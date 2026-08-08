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

use local_learningjourney\local\handoff;
use local_learningjourney\local\settings_resolver;
use mod_quiz\event\attempt_submitted as attempt_submitted_event;
use Throwable;

/**
 * Writes the one shot handoff token when a learner submits their own attempt.
 *
 * The observer never writes to the database, never produces output and never
 * redirects, so it is safe in every execution context.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class attempt_submitted {
    /**
     * Handle a submitted quiz attempt.
     *
     * @param attempt_submitted_event $event The triggered event.
     * @return void
     */
    public static function handle(attempt_submitted_event $event): void {
        try {
            if (!self::should_intercept($event)) {
                return;
            }

            handoff::store(
                (int) $event->objectid,
                (int) $event->contextinstanceid,
                (int) $event->courseid
            );
        } catch (Throwable $e) {
            debugging('Learning Journey observer failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }

    /**
     * Decide whether this submission should divert the learner.
     *
     * The guards run cheapest first, so the common rejection cases cost nothing
     * more than a constant lookup.
     *
     * @param attempt_submitted_event $event The triggered event.
     * @return bool True when the learner should be diverted.
     */
    protected static function should_intercept(attempt_submitted_event $event): bool {
        if (CLI_SCRIPT || (defined('WS_SERVER') && WS_SERVER) || (defined('AJAX_SCRIPT') && AJAX_SCRIPT)) {
            return false;
        }

        if (during_initial_install() || isguestuser()) {
            return false;
        }

        if ((int) $event->userid !== (int) $event->relateduserid) {
            return false;
        }

        if (!settings_resolver::is_enabled_for_course((int) $event->courseid)) {
            return false;
        }

        if (has_capability('mod/quiz:preview', $event->get_context(), (int) $event->userid, false)) {
            return false;
        }

        return true;
    }
}
