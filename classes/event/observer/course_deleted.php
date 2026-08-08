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

namespace local_learningjourney\event\observer;

use core\event\course_deleted as course_deleted_event;
use local_learningjourney\local\settings_resolver;
use Throwable;

/**
 * Removes Learning Journey overrides belonging to a deleted course.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_deleted {
    /**
     * Handle a deleted course.
     *
     * @param course_deleted_event $event The triggered event.
     * @return void
     */
    public static function handle(course_deleted_event $event): void {
        try {
            settings_resolver::delete_course((int) $event->objectid);
        } catch (Throwable $e) {
            debugging('Learning Journey cleanup failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }
}
