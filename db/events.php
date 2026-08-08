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

/**
 * Event observer registrations for the Learning Journey plugin.
 *
 * Both observers are registered as non-internal so that they run after the
 * surrounding database transaction has committed and the gradebook is final.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\mod_quiz\event\attempt_submitted',
        'callback'  => '\local_learningjourney\event\observer\attempt_submitted::handle',
        'internal'  => false,
        'priority'  => 0,
    ],
    [
        'eventname' => '\core\event\course_deleted',
        'callback'  => '\local_learningjourney\event\observer\course_deleted::handle',
        'internal'  => false,
        'priority'  => 0,
    ],
];
