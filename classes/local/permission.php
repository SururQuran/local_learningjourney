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

use context_course;
use context_module;
use required_capability_exception;

/**
 * Central authorisation rules for the Learning Journey plugin.
 *
 * The web page, the external service and the mobile view all call these
 * methods, so the access rule exists in exactly one testable place.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class permission {
    /**
     * Prevent instantiation of this stateless helper.
     */
    private function __construct() {
    }

    /**
     * Determine whether the current user may view a learner's result page.
     *
     * @param context_module $context Context of the quiz course module.
     * @param int $ownerid Identifier of the learner who owns the attempt.
     * @return bool True when the current user is the owner or a permitted viewer.
     */
    public static function can_view_result(context_module $context, int $ownerid): bool {
        global $USER;

        if ((int) $USER->id === $ownerid) {
            return true;
        }

        return has_capability('local/learningjourney:viewothers', $context);
    }

    /**
     * Require that the current user may view a learner's result page.
     *
     * @param context_module $context Context of the quiz course module.
     * @param int $ownerid Identifier of the learner who owns the attempt.
     * @return void
     * @throws required_capability_exception When the current user is not permitted.
     */
    public static function require_can_view_result(context_module $context, int $ownerid): void {
        global $USER;

        if ((int) $USER->id === $ownerid) {
            return;
        }

        require_capability('local/learningjourney:viewothers', $context);
    }

    /**
     * Determine whether the current user may edit course level overrides.
     *
     * @param context_course $context Context of the course.
     * @return bool True when the current user may manage the course settings.
     */
    public static function can_manage_course(context_course $context): bool {
        return has_capability('local/learningjourney:manage', $context);
    }

    /**
     * Require that the current user may edit course level overrides.
     *
     * @param context_course $context Context of the course.
     * @return void
     * @throws required_capability_exception When the current user is not permitted.
     */
    public static function require_can_manage_course(context_course $context): void {
        require_capability('local/learningjourney:manage', $context);
    }
}
