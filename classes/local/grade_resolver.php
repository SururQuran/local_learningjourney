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
use grade_grade;
use grade_item;
use local_learningjourney\local\model\grade_info;
use stdClass;

/**
 * Determines the learner's grade and the pass or fail verdict.
 *
 * The verdict describes the attempt the learner has just submitted, because
 * that is what the page reports on: a learner who scores zero on a retake must
 * not be congratulated because an earlier attempt still holds the gradebook
 * figure. The gradebook value is still resolved and shown alongside whenever it
 * differs, so the learner can see both.
 *
 * The pass mark is resolved in a fixed order of precedence:
 * the quiz grade item pass grade, then the course level Learning Journey
 * override, then the site wide Learning Journey default.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grade_resolver {
    /** @var float Tolerance below which two percentages are treated as equal. */
    protected const EPSILON = 0.005;

    /** @var stdClass Course the quiz belongs to. */
    protected stdClass $course;

    /** @var cm_info Course module of the quiz. */
    protected cm_info $cm;

    /** @var int Learner whose grade is resolved. */
    protected int $userid;

    /** @var settings_resolver Effective settings for the course. */
    protected settings_resolver $settings;

    /** @var grade_item|false|null Memoised quiz grade item. */
    protected $gradeitem = null;

    /** @var grade_grade|false|null Memoised learner grade. */
    protected $usergrade = null;

    /**
     * Create a grade resolver for one learner and one quiz.
     *
     * @param stdClass $course Course the quiz belongs to.
     * @param cm_info $cm Course module of the quiz.
     * @param int $userid Learner whose grade is resolved.
     * @param settings_resolver $settings Effective settings for the course.
     */
    public function __construct(stdClass $course, cm_info $cm, int $userid, settings_resolver $settings) {
        $this->course = $course;
        $this->cm = $cm;
        $this->userid = $userid;
        $this->settings = $settings;
    }

    /**
     * Resolve the grade and verdict for an attempt.
     *
     * @param quiz_adapter $quiz Adapter for the attempt being reported on.
     * @return grade_info The resolved grade description.
     */
    public function resolve(quiz_adapter $quiz): grade_info {
        $pending = $quiz->has_pending_manual_grading();
        $item = $this->get_grade_item();

        [$passpercent, $passsource] = $this->resolve_gradepass($item);

        $attemptpercent = $quiz->get_attempt_percentage();
        $overallpercent = $this->gradebook_percentage($item);

        $verdictpercent = $attemptpercent ?? $overallpercent;
        $verdict = $this->determine_verdict($verdictpercent, $passpercent, $pending);

        $maxgrade = $quiz->get_quiz_max_grade();
        $attemptgrade = $quiz->get_attempt_grade();

        $variance = $overallpercent !== null
            && $attemptpercent !== null
            && abs($overallpercent - $attemptpercent) > self::EPSILON;

        return new grade_info(
            verdict: $verdict,
            rawgrade: $attemptgrade,
            maxgrade: $maxgrade > 0 ? $maxgrade : null,
            percent: $attemptpercent,
            overallpercent: $overallpercent,
            gradepasspercent: $passpercent,
            gradepasssource: $passsource,
            formattedgrade: $quiz->format_grade($attemptgrade),
            formattedmax: $quiz->format_grade($maxgrade > 0 ? $maxgrade : null),
            hasoverallvariance: $variance,
        );
    }

    /**
     * Return the grade item belonging to the quiz.
     *
     * @return grade_item|null The grade item, or null when none exists.
     */
    protected function get_grade_item(): ?grade_item {
        if ($this->gradeitem === null) {
            $this->gradeitem = grade_item::fetch([
                'courseid'     => (int) $this->course->id,
                'itemtype'     => 'mod',
                'itemmodule'   => 'quiz',
                'iteminstance' => (int) $this->cm->instance,
                'itemnumber'   => 0,
            ]);
        }

        return $this->gradeitem instanceof grade_item ? $this->gradeitem : null;
    }

    /**
     * Return the learner's grade for the quiz.
     *
     * @return grade_grade|null The grade, or null when none is recorded.
     */
    protected function get_user_grade(): ?grade_grade {
        if ($this->usergrade === null) {
            $item = $this->get_grade_item();
            $this->usergrade = $item === null
                ? false
                : grade_grade::fetch(['itemid' => $item->id, 'userid' => $this->userid]);
        }

        return $this->usergrade instanceof grade_grade ? $this->usergrade : null;
    }

    /**
     * Return the percentage recorded for this learner in the gradebook.
     *
     * @param grade_item|null $item The quiz grade item.
     * @return float|null The percentage, or null when no grade is recorded.
     */
    protected function gradebook_percentage(?grade_item $item): ?float {
        if ($item === null) {
            return null;
        }

        $grade = $this->get_user_grade();
        if ($grade === null || $grade->finalgrade === null) {
            return null;
        }

        $min = (float) $item->grademin;
        $max = (float) $item->grademax;
        $range = $max - $min;

        if ($range <= 0) {
            return null;
        }

        $percent = ((float) $grade->finalgrade - $min) / $range * 100;

        return round(max(0.0, min(100.0, $percent)), 2);
    }

    /**
     * Determine the pass mark that applies, and where it came from.
     *
     * @param grade_item|null $item The quiz grade item.
     * @return array{0: float|null, 1: string} Pass percentage and its source.
     */
    protected function resolve_gradepass(?grade_item $item): array {
        if ($item !== null && (float) $item->gradepass > 0) {
            $min = (float) $item->grademin;
            $max = (float) $item->grademax;
            $range = $max - $min;

            if ($range > 0) {
                $percent = ((float) $item->gradepass - $min) / $range * 100;

                return [round(max(0.0, min(100.0, $percent)), 2), grade_info::SOURCE_QUIZ];
            }
        }

        if ($this->settings->get_bool('usefallbackgradepass')) {
            $fallback = (float) $this->settings->get_int('fallbackgradepass');

            return [max(0.0, min(100.0, $fallback)), grade_info::SOURCE_FALLBACK];
        }

        return [null, grade_info::SOURCE_NONE];
    }

    /**
     * Apply the fixed verdict precedence to the resolved figures.
     *
     * @param float|null $percent Percentage achieved.
     * @param float|null $passpercent Pass mark applied.
     * @param bool $pending Whether manual grading is outstanding.
     * @return string One of the constants::RESULT_* values.
     */
    protected function determine_verdict(?float $percent, ?float $passpercent, bool $pending): string {
        if ($pending) {
            return constants::RESULT_PENDING;
        }

        if ($percent === null || $passpercent === null) {
            return constants::RESULT_NOMARK;
        }

        return ($percent + self::EPSILON) >= $passpercent
            ? constants::RESULT_PASS
            : constants::RESULT_FAIL;
    }
}
