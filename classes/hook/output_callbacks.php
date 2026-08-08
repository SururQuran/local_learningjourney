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

namespace local_learningjourney\hook;

use core\hook\output\before_http_headers;
use local_learningjourney\local\handoff;
use moodle_url;
use stdClass;
use Throwable;

/**
 * The single site wide interception point for the Learning Journey plugin.
 *
 * On the overwhelming majority of page loads this costs one session cache read
 * and no database access, which is what allows the plugin to run site wide.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class output_callbacks {
    /** @var string[] Scripts on which a pending handoff token may be consumed. */
    protected const DESTINATIONS = ['/mod/quiz/review.php', '/mod/quiz/view.php', '/mod/quiz/summary.php'];

    /**
     * Divert the learner to the result page immediately after a submission.
     *
     * @param before_http_headers $hook The dispatched hook instance.
     * @return void
     */
    public static function before_http_headers(before_http_headers $hook): void {
        unset($hook);

        try {
            $token = handoff::peek();
            if ($token === null) {
                return;
            }

            if (!handoff::is_fresh($token)) {
                handoff::clear();

                return;
            }

            if (!self::matches_destination($token)) {
                return;
            }

            handoff::consume();
            redirect(self::result_url($token));
        } catch (Throwable $e) {
            handoff::clear();
            debugging('Learning Journey diversion failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }

    /**
     * Decide whether the current request is the expected destination.
     *
     * The token is only consumed on the page mod_quiz redirects to after a
     * submission, so an unrelated request never triggers a diversion.
     *
     * @param stdClass $token The stored handoff token.
     * @return bool True when the current request should be diverted.
     */
    protected static function matches_destination(stdClass $token): bool {
        global $PAGE, $SCRIPT;

        if (!isset($PAGE) || !($PAGE instanceof \moodle_page)) {
            return false;
        }

        if ($PAGE->has_set_url() && $PAGE->url->compare(self::result_url($token), URL_MATCH_BASE)) {
            return false;
        }

        $cm = $PAGE->cm;
        if ($cm !== null && (int) $cm->id === (int) $token->cmid) {
            return true;
        }

        if (!is_string($SCRIPT) || !in_array($SCRIPT, self::DESTINATIONS, true)) {
            return false;
        }

        return (int) $PAGE->course->id === (int) $token->courseid;
    }

    /**
     * Build the result page URL for a handoff token.
     *
     * @param stdClass $token The stored handoff token.
     * @return moodle_url The Learning Journey result page.
     */
    protected static function result_url(stdClass $token): moodle_url {
        return new moodle_url('/local/learningjourney/result.php', ['attempt' => (int) $token->attemptid]);
    }
}
