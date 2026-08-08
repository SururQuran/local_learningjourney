<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_learningjourney\local;

use cm_info;
use completion_info;
use core_completion\progress;
use local_learningjourney\local\model\progress_info;
use section_info;
use stdClass;
use Throwable;

/**
 * Calculates course and unit progress using core completion aggregates only.
 *
 * Completion data is preloaded for the whole course in a single query, and the
 * course percentage comes from the same core helper the course overview uses,
 * so the figures shown here can never drift from Moodle's own reporting.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class progress_calculator {
    /** @var stdClass Course whose progress is calculated. */
    protected stdClass $course;

    /** @var int Learner whose progress is calculated. */
    protected int $userid;

    /** @var settings_resolver Effective settings for the course. */
    protected settings_resolver $settings;

    /** @var completion_info|null Memoised completion helper. */
    protected ?completion_info $completion = null;

    /** @var \course_modinfo|null Memoised course modinfo for this learner. */
    protected $modinfo = null;

    /** @var array<int, bool>|null Memoised completion state keyed by course module. */
    protected ?array $completionstate = null;

    /**
     * Create a progress calculator for one learner and one course.
     *
     * @param stdClass $course Course whose progress is calculated.
     * @param int $userid Learner whose progress is calculated.
     * @param settings_resolver $settings Effective settings for the course.
     */
    public function __construct(stdClass $course, int $userid, settings_resolver $settings) {
        $this->course = $course;
        $this->userid = $userid;
        $this->settings = $settings;
    }

    /**
     * Calculate the learner's progress relative to the current activity.
     *
     * @param cm_info|null $currentcm Course module the learner has just completed.
     * @return progress_info The progress description.
     */
    public function calculate(?cm_info $currentcm = null): progress_info {
        $unitmode = $this->settings->get('unitmode');

        try {
            if (!$this->get_completion()->is_enabled()) {
                return new progress_info(available: false, unitmode: $unitmode);
            }

            $this->preload_completion();

            $counts = $this->activity_counts();
            $units = match ($unitmode) {
                constants::UNIT_ACTIVITY => $this->units_by_activity($currentcm),
                constants::UNIT_LESSON   => $this->units_by_lesson($currentcm),
                default                  => $this->units_by_section($currentcm),
            };

            return new progress_info(
                available: true,
                unitmode: $unitmode,
                unitindex: $units['index'],
                unittotal: $units['total'],
                unitscompleted: $units['completed'],
                coursepercent: $this->course_percentage(),
                activitiescompleted: $counts['completed'],
                activitiestotal: $counts['total'],
                activitiesremaining: max(0, $counts['total'] - $counts['completed']),
            );
        } catch (Throwable $e) {
            debugging('Learning Journey progress calculation failed: ' . $e->getMessage(), DEBUG_DEVELOPER);

            return new progress_info(available: false, unitmode: $unitmode);
        }
    }

    /**
     * Return the completion helper for the course.
     *
     * @return completion_info The completion helper.
     */
    protected function get_completion(): completion_info {
        if ($this->completion === null) {
            $this->completion = new completion_info($this->course);
        }

        return $this->completion;
    }

    /**
     * Return the course modinfo for this learner.
     *
     * @return \course_modinfo The course modinfo.
     */
    protected function get_modinfo() {
        if ($this->modinfo === null) {
            $this->modinfo = get_fast_modinfo($this->course, $this->userid);
        }

        return $this->modinfo;
    }

    /**
     * Preload completion state for every tracked activity in one query.
     *
     * @return array<int, bool> Completion state keyed by course module identifier.
     */
    protected function preload_completion(): array {
        global $USER;

        if ($this->completionstate !== null) {
            return $this->completionstate;
        }

        $completion = $this->get_completion();
        $modinfo = $this->get_modinfo();

        $tracked = [];
        foreach ($completion->get_activities() as $activity) {
            $cm = $modinfo->get_cm($activity->id);
            if ($cm->uservisible) {
                $tracked[] = $cm;
            }
        }

        // Core only preloads the whole course for the user who is logged in.
        // For anybody else completion_info::get_data() falls back to one query
        // per activity, so staff viewing a learner's result would otherwise
        // trigger an N+1. One bulk read covers that case instead.
        $this->completionstate = ((int) $this->userid === (int) $USER->id)
            ? $this->completion_for_current_user($completion, $tracked)
            : $this->completion_for_other_user($tracked);

        return $this->completionstate;
    }

    /**
     * Read completion for the logged in user through the core preload.
     *
     * @param completion_info $completion The completion helper.
     * @param cm_info[] $tracked Completion tracked, viewable activities.
     * @return array<int, bool> Completion state keyed by course module identifier.
     */
    protected function completion_for_current_user(completion_info $completion, array $tracked): array {
        $state = [];
        $wholecourse = true;

        foreach ($tracked as $cm) {
            $data = $completion->get_data($cm, $wholecourse, $this->userid);
            $wholecourse = false;
            $state[(int) $cm->id] = $this->is_complete((int) $data->completionstate);
        }

        return $state;
    }

    /**
     * Read completion for another user in a single query.
     *
     * @param cm_info[] $tracked Completion tracked, viewable activities.
     * @return array<int, bool> Completion state keyed by course module identifier.
     */
    protected function completion_for_other_user(array $tracked): array {
        global $DB;

        $state = [];
        foreach ($tracked as $cm) {
            $state[(int) $cm->id] = false;
        }

        if (empty($state)) {
            return $state;
        }

        [$insql, $params] = $DB->get_in_or_equal(array_keys($state), SQL_PARAMS_NAMED, 'cm');
        $params['userid'] = $this->userid;

        $records = $DB->get_records_select(
            'course_modules_completion',
            "userid = :userid AND coursemoduleid $insql",
            $params,
            '',
            'coursemoduleid, completionstate'
        );

        foreach ($records as $cmid => $record) {
            $state[(int) $cmid] = $this->is_complete((int) $record->completionstate);
        }

        return $state;
    }

    /**
     * Determine whether a completion state counts as complete.
     *
     * @param int $completionstate One of the COMPLETION_* state constants.
     * @return bool True when the activity is complete.
     */
    protected function is_complete(int $completionstate): bool {
        return in_array($completionstate, [COMPLETION_COMPLETE, COMPLETION_COMPLETE_PASS], true);
    }

    /**
     * Return the number of tracked activities and how many are complete.
     *
     * @return array{completed: int, total: int} Activity counts.
     */
    protected function activity_counts(): array {
        $state = $this->preload_completion();

        return [
            'completed' => count(array_filter($state)),
            'total'     => count($state),
        ];
    }

    /**
     * Return the overall course completion percentage.
     *
     * @return float|null The percentage, or null when it cannot be calculated.
     */
    protected function course_percentage(): ?float {
        global $USER;

        // The core progress helper uses completion_info::get_data() internally,
        // which only preloads for the logged in user. For anybody else it costs
        // one query per activity, so the identical ratio is derived from the
        // state already loaded here instead.
        if ((int) $this->userid !== (int) $USER->id) {
            $counts = $this->activity_counts();

            if ($counts['total'] === 0) {
                return null;
            }

            return round(($counts['completed'] / $counts['total']) * 100, 2);
        }

        $percent = progress::get_course_progress_percentage($this->course, $this->userid);

        return $percent === null ? null : round((float) $percent, 2);
    }

    /**
     * Count units as course sections that contain at least one viewable activity.
     *
     * @param cm_info|null $current Course module the learner has just completed.
     * @return array{index: int, total: int, completed: int} Unit counts.
     */
    protected function units_by_section(?cm_info $current): array {
        $modinfo = $this->get_modinfo();
        $sections = $modinfo->get_sections();
        $currentsection = $current === null ? null : (int) $current->sectionnum;

        $index = 0;
        $total = 0;
        $completed = 0;
        $position = 0;

        foreach ($modinfo->get_section_info_all() as $sectioninfo) {
            if (!$sectioninfo->uservisible) {
                continue;
            }

            if (!$this->section_has_viewable_activity($sectioninfo, $sections, $modinfo)) {
                continue;
            }

            $total++;
            $position++;

            if ($currentsection !== null && (int) $sectioninfo->section === $currentsection) {
                $index = $position;
            }

            if ($this->is_section_complete($sectioninfo, $sections)) {
                $completed++;
            }
        }

        return ['index' => $index, 'total' => $total, 'completed' => $completed];
    }

    /**
     * Count units as completion tracked activities.
     *
     * @param cm_info|null $current Course module the learner has just completed.
     * @return array{index: int, total: int, completed: int} Unit counts.
     */
    protected function units_by_activity(?cm_info $current): array {
        $state = $this->preload_completion();
        $modinfo = $this->get_modinfo();
        $currentid = $current === null ? null : (int) $current->id;

        $index = 0;
        $position = 0;

        foreach ($modinfo->get_cms() as $cm) {
            if (!array_key_exists((int) $cm->id, $state)) {
                continue;
            }

            $position++;

            if ($currentid !== null && (int) $cm->id === $currentid) {
                $index = $position;
            }
        }

        return [
            'index'     => $index,
            'total'     => count($state),
            'completed' => count(array_filter($state)),
        ];
    }

    /**
     * Count units as mod_lesson instances.
     *
     * @param cm_info|null $current Course module the learner has just completed.
     * @return array{index: int, total: int, completed: int} Unit counts.
     */
    protected function units_by_lesson(?cm_info $current): array {
        $modinfo = $this->get_modinfo();
        $state = $this->preload_completion();
        $currentid = $current === null ? null : (int) $current->id;

        $index = 0;
        $total = 0;
        $completed = 0;

        foreach ($modinfo->get_instances_of('lesson') as $cm) {
            if (!$cm->uservisible) {
                continue;
            }

            $total++;

            if ($currentid !== null && (int) $cm->id === $currentid) {
                $index = $total;
            }

            if (!empty($state[(int) $cm->id])) {
                $completed++;
            }
        }

        return ['index' => $index, 'total' => $total, 'completed' => $completed];
    }

    /**
     * Determine whether a section contains at least one viewable activity.
     *
     * @param section_info $sectioninfo The section to inspect.
     * @param array $sections Course module identifiers keyed by section number.
     * @param \course_modinfo $modinfo Course modinfo for this learner.
     * @return bool True when the section has something the learner can open.
     */
    protected function section_has_viewable_activity(section_info $sectioninfo, array $sections, $modinfo): bool {
        foreach ($sections[$sectioninfo->section] ?? [] as $cmid) {
            $cm = $modinfo->get_cm($cmid);
            if ($cm->has_view() && $cm->uservisible && empty($cm->deletioninprogress)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether every tracked activity in a section is complete.
     *
     * @param section_info $sectioninfo The section to inspect.
     * @param array $sections Course module identifiers keyed by section number.
     * @return bool True when the section has tracked activities and all are complete.
     */
    protected function is_section_complete(section_info $sectioninfo, array $sections): bool {
        $state = $this->preload_completion();
        $tracked = 0;

        foreach ($sections[$sectioninfo->section] ?? [] as $cmid) {
            if (!array_key_exists((int) $cmid, $state)) {
                continue;
            }

            $tracked++;

            if (!$state[(int) $cmid]) {
                return false;
            }
        }

        return $tracked > 0;
    }
}
