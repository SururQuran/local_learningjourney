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
use moodle_url;

/**
 * Immutable description of the learner's next step in the course.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class next_step {
    /**
     * Create a next step description.
     *
     * @param string $type One of the constants::NEXT_* values.
     * @param int|null $cmid Course module identifier of the next activity.
     * @param string $name Name of the next activity.
     * @param moodle_url|null $url Destination of the continue action.
     * @param string $modname Module type of the next activity.
     * @param string $icon Identifier of the icon representing the next activity.
     */
    public function __construct(
        /** @var string One of the constants::NEXT_* values. */
        public readonly string $type = constants::NEXT_COURSE_COMPLETE,
        /** @var int|null Course module identifier of the next activity. */
        public readonly ?int $cmid = null,
        /** @var string Name of the next activity. */
        public readonly string $name = '',
        /** @var moodle_url|null Destination of the continue action. */
        public readonly ?moodle_url $url = null,
        /** @var string Module type of the next activity. */
        public readonly string $modname = '',
        /** @var string Identifier of the icon representing the next activity. */
        public readonly string $icon = '',
    ) {
    }

    /**
     * Determine whether a further activity is available to the learner.
     *
     * @return bool True when the learner may continue to another activity.
     */
    public function is_available(): bool {
        return $this->type === constants::NEXT_ACTIVITY && $this->url !== null;
    }
}
