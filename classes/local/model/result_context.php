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

use cm_info;
use local_learningjourney\local\constants;
use stdClass;

/**
 * Immutable aggregate holding everything the result page needs to render.
 *
 * Assembled once by the result builder and consumed by the output layer, which
 * may not perform any further data access.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class result_context {
    /**
     * Create a result context.
     *
     * @param string $verdict One of the constants::RESULT_* values.
     * @param stdClass $course Course the quiz belongs to.
     * @param cm_info $cm Course module of the quiz.
     * @param attempt_info $attempt Attempt description.
     * @param grade_info $grade Grade description.
     * @param progress_info $progress Progress description.
     * @param next_step $nextstep Next step description.
     * @param badge_info[] $badges Badges to display.
     * @param int $stars Star rating between 0 and 5.
     * @param appearance $appearance Validated presentation settings.
     * @param messages $messages Resolved page messages.
     * @param array $actions Ordered page actions.
     */
    public function __construct(
        /** @var string One of the constants::RESULT_* values. */
        public readonly string $verdict,
        /** @var stdClass Course the quiz belongs to. */
        public readonly stdClass $course,
        /** @var cm_info Course module of the quiz. */
        public readonly cm_info $cm,
        /** @var attempt_info Attempt description. */
        public readonly attempt_info $attempt,
        /** @var grade_info Grade description. */
        public readonly grade_info $grade,
        /** @var progress_info Progress description. */
        public readonly progress_info $progress,
        /** @var next_step Next step description. */
        public readonly next_step $nextstep,
        /** @var badge_info[] Badges to display. */
        public readonly array $badges,
        /** @var int Star rating between 0 and 5. */
        public readonly int $stars,
        /** @var appearance Validated presentation settings. */
        public readonly appearance $appearance,
        /** @var messages Resolved page messages. */
        public readonly messages $messages,
        /** @var array<int, array<string, mixed>> Ordered page actions. */
        public readonly array $actions,
    ) {
    }

    /**
     * Determine whether the page should render the celebration variant.
     *
     * @return bool True when the verdict is a pass.
     */
    public function is_pass(): bool {
        return $this->verdict === constants::RESULT_PASS;
    }
}
