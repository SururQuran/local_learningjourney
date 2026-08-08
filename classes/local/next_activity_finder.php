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
use local_learningjourney\local\model\next_step;
use stdClass;

/**
 * Locates the learner's next activity using course modinfo.
 *
 * Modinfo already applies visibility, availability, stealth and group rules for
 * the given user, so this class never evaluates access restrictions itself and
 * never discloses why an activity is unavailable.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class next_activity_finder {
    /** @var stdClass Course being traversed. */
    protected stdClass $course;

    /** @var int Learner whose view of the course applies. */
    protected int $userid;

    /** @var cm_info[]|null Memoised, ordered course modules. */
    protected ?array $ordered = null;

    /**
     * Create a finder for one learner and one course.
     *
     * @param stdClass $course Course being traversed.
     * @param int $userid Learner whose view of the course applies.
     */
    public function __construct(stdClass $course, int $userid) {
        $this->course = $course;
        $this->userid = $userid;
    }

    /**
     * Find the activity that follows the given course module.
     *
     * @param cm_info $current Course module the learner has just completed.
     * @return next_step Description of the learner's next step.
     */
    public function find_next(cm_info $current): next_step {
        $modules = $this->ordered_cms();
        $position = $this->position_of($modules, (int) $current->id);

        if ($position === null) {
            return new next_step(type: constants::NEXT_COURSE_COMPLETE);
        }

        $blocked = false;
        $scanned = 0;
        $total = count($modules);

        for ($index = $position + 1; $index < $total; $index++) {
            if (++$scanned > constants::MAX_SCAN) {
                break;
            }

            $cm = $modules[$index];
            if (!$this->is_candidate($cm)) {
                continue;
            }

            if (!$cm->uservisible) {
                $blocked = true;
                continue;
            }

            return new next_step(
                type: constants::NEXT_ACTIVITY,
                cmid: (int) $cm->id,
                name: format_string($cm->name, true, ['context' => $cm->context]),
                url: $cm->url,
                modname: (string) $cm->modname,
                icon: (string) $cm->modname,
            );
        }

        return new next_step(type: $blocked ? constants::NEXT_BLOCKED : constants::NEXT_COURSE_COMPLETE);
    }

    /**
     * Find the closest preceding activity the learner may revisit.
     *
     * @param cm_info $current Course module the learner has just completed.
     * @return cm_info|null The preceding activity, or null when none exists.
     */
    public function find_previous_viewable(cm_info $current): ?cm_info {
        $modules = $this->ordered_cms();
        $position = $this->position_of($modules, (int) $current->id);

        if ($position === null) {
            return null;
        }

        $scanned = 0;

        for ($index = $position - 1; $index >= 0; $index--) {
            if (++$scanned > constants::MAX_SCAN) {
                break;
            }

            $cm = $modules[$index];
            if ($this->is_candidate($cm) && $cm->uservisible) {
                return $cm;
            }
        }

        return null;
    }

    /**
     * Determine whether a course module may be offered to the learner.
     *
     * @param cm_info $cm Candidate course module.
     * @return bool True when the module has its own page and is not being deleted.
     */
    protected function is_candidate(cm_info $cm): bool {
        return $cm->has_view() && empty($cm->deletioninprogress);
    }

    /**
     * Return every course module in course order, excluding hidden sections.
     *
     * @return cm_info[] Course modules ordered by section and sequence.
     */
    protected function ordered_cms(): array {
        if ($this->ordered !== null) {
            return $this->ordered;
        }

        $modinfo = get_fast_modinfo($this->course, $this->userid);
        $sections = $modinfo->get_sections();
        $ordered = [];

        foreach ($modinfo->get_section_info_all() as $sectioninfo) {
            if (!$sectioninfo->uservisible) {
                continue;
            }

            foreach ($sections[$sectioninfo->section] ?? [] as $cmid) {
                $ordered[] = $modinfo->get_cm($cmid);
            }
        }

        $this->ordered = $ordered;

        return $this->ordered;
    }

    /**
     * Return the position of a course module within an ordered list.
     *
     * @param cm_info[] $modules Ordered course modules.
     * @param int $cmid Course module identifier to locate.
     * @return int|null The zero based position, or null when not present.
     */
    protected function position_of(array $modules, int $cmid): ?int {
        foreach ($modules as $index => $cm) {
            if ((int) $cm->id === $cmid) {
                return $index;
            }
        }

        return null;
    }
}
