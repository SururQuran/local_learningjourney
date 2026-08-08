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

namespace local_learningjourney\local\model;

use local_learningjourney\local\constants;

/**
 * Immutable description of a learner's grade and pass or fail verdict.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class grade_info {
    /** @var string Pass mark taken from the quiz grade item. */
    public const SOURCE_QUIZ = 'quiz';

    /** @var string Pass mark taken from the site wide fallback. */
    public const SOURCE_FALLBACK = 'fallback';

    /** @var string No pass mark could be determined. */
    public const SOURCE_NONE = 'none';

    /**
     * Create a grade description.
     *
     * @param string $verdict One of the constants::RESULT_* values.
     * @param float|null $rawgrade Grade achieved on this attempt.
     * @param float|null $maxgrade Maximum grade available.
     * @param float|null $percent Percentage achieved on this attempt.
     * @param float|null $overallpercent Percentage recorded in the gradebook.
     * @param float|null $gradepasspercent Pass mark applied, as a percentage.
     * @param string $gradepasssource Origin of the applied pass mark.
     * @param string $formattedgrade Localised representation of the grade.
     * @param string $formattedmax Localised representation of the maximum grade.
     * @param bool $hasoverallvariance Whether the gradebook figure differs from this attempt.
     */
    public function __construct(
        /** @var string One of the constants::RESULT_* values. */
        public readonly string $verdict = constants::RESULT_NOMARK,
        /** @var float|null Grade achieved on this attempt. */
        public readonly ?float $rawgrade = null,
        /** @var float|null Maximum grade available. */
        public readonly ?float $maxgrade = null,
        /** @var float|null Percentage achieved on this attempt. */
        public readonly ?float $percent = null,
        /** @var float|null Percentage recorded in the gradebook. */
        public readonly ?float $overallpercent = null,
        /** @var float|null Pass mark applied, as a percentage. */
        public readonly ?float $gradepasspercent = null,
        /** @var string Origin of the applied pass mark. */
        public readonly string $gradepasssource = self::SOURCE_NONE,
        /** @var string Localised representation of the grade. */
        public readonly string $formattedgrade = '',
        /** @var string Localised representation of the maximum grade. */
        public readonly string $formattedmax = '',
        /** @var bool Whether the gradebook figure differs from this attempt. */
        public readonly bool $hasoverallvariance = false,
    ) {
    }

    /**
     * Determine whether the learner reached the pass mark.
     *
     * @return bool True when the verdict is a pass.
     */
    public function is_pass(): bool {
        return $this->verdict === constants::RESULT_PASS;
    }
}
