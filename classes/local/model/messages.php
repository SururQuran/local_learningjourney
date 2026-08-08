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

/**
 * Immutable set of resolved, ready to display page messages.
 *
 * Values are resolved in the order course override, site setting, language
 * string, which is what gives untouched installations correct translations.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class messages {
    /**
     * Create a resolved message set.
     *
     * @param string $title Page heading.
     * @param string $body Main body message.
     * @param string $islamicline Optional supplementary message.
     * @param string $progressline Sentence describing course progress.
     * @param string $adviceline Optional study advice shown after a failed attempt.
     * @param string $coursecompletetext Message shown when no activity remains.
     */
    public function __construct(
        /** @var string Page heading. */
        public readonly string $title = '',
        /** @var string Main body message. */
        public readonly string $body = '',
        /** @var string Optional supplementary message. */
        public readonly string $islamicline = '',
        /** @var string Sentence describing course progress. */
        public readonly string $progressline = '',
        /** @var string Optional study advice shown after a failed attempt. */
        public readonly string $adviceline = '',
        /** @var string Message shown when no activity remains. */
        public readonly string $coursecompletetext = '',
    ) {
    }
}
