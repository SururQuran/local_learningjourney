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
 * Immutable description of a single quiz attempt.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class attempt_info {
    /**
     * Create an attempt description.
     *
     * @param int $attemptid Quiz attempt identifier.
     * @param int $attemptnumber Ordinal number of this attempt.
     * @param int $attemptsused Count of finished attempts by this learner.
     * @param int $attemptsallowed Maximum attempts permitted, or 0 for unlimited.
     * @param int|null $attemptsremaining Attempts still available, or null when unlimited.
     * @param int $timetaken Seconds spent on the attempt.
     * @param int $timefinish Unix timestamp at which the attempt finished.
     * @param bool $canretry Whether a further attempt may currently be started.
     * @param string|null $retryblockedreason Explanation given when a retry is not permitted.
     * @param moodle_url|null $reviewurl Core review page, when review is permitted.
     * @param moodle_url|null $retryurl Start attempt page, when a retry is permitted.
     */
    public function __construct(
        /** @var int Quiz attempt identifier. */
        public readonly int $attemptid = 0,
        /** @var int Ordinal number of this attempt. */
        public readonly int $attemptnumber = 0,
        /** @var int Count of finished attempts by this learner. */
        public readonly int $attemptsused = 0,
        /** @var int Maximum attempts permitted, or 0 for unlimited. */
        public readonly int $attemptsallowed = 0,
        /** @var int|null Attempts still available, or null when unlimited. */
        public readonly ?int $attemptsremaining = null,
        /** @var int Seconds spent on the attempt. */
        public readonly int $timetaken = 0,
        /** @var int Unix timestamp at which the attempt finished. */
        public readonly int $timefinish = 0,
        /** @var bool Whether a further attempt may currently be started. */
        public readonly bool $canretry = false,
        /** @var string|null Explanation given when a retry is not permitted. */
        public readonly ?string $retryblockedreason = null,
        /** @var moodle_url|null Core review page, when review is permitted. */
        public readonly ?moodle_url $reviewurl = null,
        /** @var moodle_url|null Start attempt page, when a retry is permitted. */
        public readonly ?moodle_url $retryurl = null,
    ) {
    }
}
