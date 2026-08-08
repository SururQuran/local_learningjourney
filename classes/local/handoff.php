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

use cache;
use cache_session;
use stdClass;

/**
 * Owns the one shot submission handoff token and its session cache.
 *
 * The token is written by the quiz attempt observer and consumed by the output
 * hook on the following request. No other class may read or write the cache
 * directly, so the interception mechanism can be changed in one place.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class handoff {
    /**
     * Prevent instantiation of this stateless helper.
     */
    private function __construct() {
    }

    /**
     * Store a handoff token for the current session.
     *
     * @param int $attemptid Quiz attempt identifier.
     * @param int $cmid Course module identifier of the quiz.
     * @param int $courseid Course identifier.
     * @return void
     */
    public static function store(int $attemptid, int $cmid, int $courseid): void {
        $token = new stdClass();
        $token->attemptid = $attemptid;
        $token->cmid = $cmid;
        $token->courseid = $courseid;
        $token->timecreated = time();

        self::cache()->set(constants::HANDOFF_KEY, $token);
    }

    /**
     * Read the current handoff token without consuming it.
     *
     * @return stdClass|null The token, or null when none is stored.
     */
    public static function peek(): ?stdClass {
        $token = self::cache()->get(constants::HANDOFF_KEY);
        if (!($token instanceof stdClass)) {
            return null;
        }

        return $token;
    }

    /**
     * Read and immediately delete the current handoff token.
     *
     * @return stdClass|null The token, or null when none is stored.
     */
    public static function consume(): ?stdClass {
        $token = self::peek();
        self::clear();

        return $token;
    }

    /**
     * Delete any stored handoff token.
     *
     * @return void
     */
    public static function clear(): void {
        self::cache()->delete(constants::HANDOFF_KEY);
    }

    /**
     * Determine whether a token is still within its permitted lifetime.
     *
     * @param stdClass $token Token previously returned by peek or consume.
     * @return bool True when the token has not expired.
     */
    public static function is_fresh(stdClass $token): bool {
        $age = time() - (int) ($token->timecreated ?? 0);

        return $age >= 0 && $age <= constants::HANDOFF_TTL;
    }

    /**
     * Obtain the session cache holding the handoff token.
     *
     * @return cache_session The plugin handoff cache.
     */
    private static function cache(): cache_session {
        return cache::make(constants::PLUGIN, constants::CACHE_HANDOFF);
    }
}
