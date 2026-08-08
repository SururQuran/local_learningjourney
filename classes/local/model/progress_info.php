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
 * Immutable description of a learner's progress through a course.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class progress_info {
    /**
     * Create a progress description.
     *
     * @param bool $available Whether completion data could be calculated.
     * @param string $unitmode One of the constants::UNIT_* values.
     * @param int $unitindex Ordinal position of the current unit.
     * @param int $unittotal Total number of units in the course.
     * @param int $unitscompleted Number of units already completed.
     * @param float|null $coursepercent Overall course completion percentage.
     * @param int $activitiescompleted Count of completed tracked activities.
     * @param int $activitiestotal Count of tracked activities in the course.
     * @param int $activitiesremaining Count of tracked activities still outstanding.
     */
    public function __construct(
        /** @var bool Whether completion data could be calculated. */
        public readonly bool $available = false,
        /** @var string One of the constants::UNIT_* values. */
        public readonly string $unitmode = constants::UNIT_SECTION,
        /** @var int Ordinal position of the current unit. */
        public readonly int $unitindex = 0,
        /** @var int Total number of units in the course. */
        public readonly int $unittotal = 0,
        /** @var int Number of units already completed. */
        public readonly int $unitscompleted = 0,
        /** @var float|null Overall course completion percentage. */
        public readonly ?float $coursepercent = null,
        /** @var int Count of completed tracked activities. */
        public readonly int $activitiescompleted = 0,
        /** @var int Count of tracked activities in the course. */
        public readonly int $activitiestotal = 0,
        /** @var int Count of tracked activities still outstanding. */
        public readonly int $activitiesremaining = 0,
    ) {
    }
}
