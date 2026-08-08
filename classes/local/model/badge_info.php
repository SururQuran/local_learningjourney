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

use moodle_url;

/**
 * Immutable description of a badge shown on the result page.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class badge_info {
    /**
     * Create a badge description.
     *
     * @param int $id Badge identifier, or 0 for the decorative achievement badge.
     * @param string $name Name of the badge.
     * @param string $description Description of the badge.
     * @param moodle_url|null $imageurl Location of the badge image.
     * @param int $dateissued Unix timestamp at which the badge was issued.
     * @param bool $isreal Whether this is a badge issued by the Moodle badge system.
     */
    public function __construct(
        /** @var int Badge identifier, or 0 for the decorative achievement badge. */
        public readonly int $id = 0,
        /** @var string Name of the badge. */
        public readonly string $name = '',
        /** @var string Description of the badge. */
        public readonly string $description = '',
        /** @var moodle_url|null Location of the badge image. */
        public readonly ?moodle_url $imageurl = null,
        /** @var int Unix timestamp at which the badge was issued. */
        public readonly int $dateissued = 0,
        /** @var bool Whether this is a badge issued by the Moodle badge system. */
        public readonly bool $isreal = false,
    ) {
    }
}
