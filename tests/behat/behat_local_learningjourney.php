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
 * Behat page resolvers for the Learning Journey plugin.
 *
 * @package    local_learningjourney
 * @category   test
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file is always included from Behat.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Resolves Learning Journey URLs for the generic Behat navigation steps.
 *
 * @package    local_learningjourney
 * @category   test
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_learningjourney extends behat_base {
    /**
     * Resolve a page that belongs to a particular instance.
     *
     * Recognised page types:
     * | Result          | The result page for a quiz attempt | attempt identifier |
     * | Course settings | The course override form           | course shortname   |
     *
     * @param string $type Identifies which page is wanted.
     * @param string $identifier Identifies the instance the page belongs to.
     * @return moodle_url The resolved URL.
     * @throws Exception When the page type is not recognised.
     */
    protected function resolve_page_instance_url(string $type, string $identifier): moodle_url {
        global $DB;

        switch (strtolower($type)) {
            case 'result':
                return new moodle_url('/local/learningjourney/result.php', [
                    'attempt' => (int) $identifier,
                ]);

            case 'course settings':
                $courseid = $DB->get_field('course', 'id', ['shortname' => $identifier], MUST_EXIST);

                return new moodle_url('/local/learningjourney/coursesettings.php', ['id' => $courseid]);

            default:
                throw new Exception(
                    'Unrecognised Learning Journey page type "' . $type . '".'
                );
        }
    }

    /**
     * Resolve a page that does not belong to a particular instance.
     *
     * Recognised pages:
     * | Report | The report of quizzes that have no pass mark configured |
     *
     * @param string $page Name of the page.
     * @return moodle_url The resolved URL.
     * @throws Exception When the page is not recognised.
     */
    protected function resolve_page(string $page): moodle_url {
        switch (strtolower($page)) {
            case 'report':
                return new moodle_url('/local/learningjourney/report.php');

            default:
                throw new Exception('Unrecognised Learning Journey page "' . $page . '".');
        }
    }
}
