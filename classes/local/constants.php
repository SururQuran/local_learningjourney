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

/**
 * Shared, immutable values used across the Learning Journey plugin.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class constants {
    /** @var string Frankenstyle component name. */
    public const PLUGIN = 'local_learningjourney';

    /** @var int Lifetime, in seconds, of a submission handoff token. */
    public const HANDOFF_TTL = 120;

    /** @var string Name of the session cache holding the handoff token. */
    public const CACHE_HANDOFF = 'handoff';

    /** @var string Name of the application cache holding merged course settings. */
    public const CACHE_COURSESETTINGS = 'coursesettings';

    /** @var string Single key used within the handoff session cache. */
    public const HANDOFF_KEY = 'token';

    /** @var string Lesson unit mode: course sections. */
    public const UNIT_SECTION = 'section';

    /** @var string Lesson unit mode: completion tracked activities. */
    public const UNIT_ACTIVITY = 'activity';

    /** @var string Lesson unit mode: mod_lesson instances. */
    public const UNIT_LESSON = 'lesson';

    /** @var string Verdict: the learner reached the pass mark. */
    public const RESULT_PASS = 'pass';

    /** @var string Verdict: the learner did not reach the pass mark. */
    public const RESULT_FAIL = 'fail';

    /** @var string Verdict: manual grading is still outstanding. */
    public const RESULT_PENDING = 'pending';

    /** @var string Verdict: no pass mark is available, so no verdict is given. */
    public const RESULT_NOMARK = 'nomark';

    /** @var int Approved site wide fallback pass mark, as a percentage. */
    public const DEFAULT_GRADEPASS_PERCENT = 60;

    /** @var string File area holding the celebration background image. */
    public const FILEAREA_BACKGROUND = 'background';

    /** @var string File area holding the administrator supplied applause sound. */
    public const FILEAREA_SOUND = 'sound';

    /** @var string User preference storing the learner's sound mute choice. */
    public const PREF_MUTE = 'local_learningjourney_mute';

    /** @var int Defensive upper bound on course module iteration. */
    public const MAX_SCAN = 500;

    /** @var string Next step type: a further activity is available. */
    public const NEXT_ACTIVITY = 'activity';

    /** @var string Next step type: the course has no further viewable activity. */
    public const NEXT_COURSE_COMPLETE = 'course_complete';

    /** @var string Next step type: a further activity exists but is not available. */
    public const NEXT_BLOCKED = 'blocked';

    /** @var string Minimum permitted automatic redirect delay, in seconds. */
    public const MIN_REDIRECT_DELAY = 10;

    /**
     * Prevent instantiation of this constant holder.
     */
    private function __construct() {
    }
}
