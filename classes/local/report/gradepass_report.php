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

namespace local_learningjourney\local\report;

/**
 * Lists quizzes that have no pass mark configured.
 *
 * Administrators use this report to find the quizzes for which the site wide
 * fallback pass mark is being applied.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gradepass_report {
    /**
     * Return the base query used by both the listing and the count.
     *
     * @return array{0: string,1: string, 2: array<string, mixed>} Select, from and parameters.
     */
    protected static function base_query(): array {
        $select = 'gi.id, gi.iteminstance, gi.grademax, c.id AS courseid, c.fullname AS coursename, gi.itemname';
        $from = '{grade_items} gi JOIN {course} c ON c.id = gi.courseid';
        $params = ['itemmodule' => 'quiz', 'itemtype' => 'mod'];

        return [$select, $from, $params];
    }

    /**
     * Return the quizzes on the site that have no pass mark configured.
     *
     * @param int|null $courseid Restrict to a single course, or null for all courses.
     * @param int $page Zero based page number.
     * @param int $perpage Number of rows per page.
     * @return array<int, \stdClass> Matching rows.
     */
    public static function get_quizzes_without_gradepass(?int $courseid, int $page = 0, int $perpage = 50): array {
        global $DB;

        [$select, $from, $params] = self::base_query();
        $where = 'gi.itemtype = :itemtype AND gi.itemmodule = :itemmodule AND (gi.gradepass IS NULL OR gi.gradepass <= 0)';
        if ($courseid !== null) {
            $where .= ' AND gi.courseid = :courseid';
            $params['courseid'] = $courseid;
        }

        $sql = "SELECT $select FROM $from WHERE $where ORDER BY c.fullname ASC, gi.itemname ASC";

        return $DB->get_records_sql($sql, $params, $page * $perpage, $perpage);
    }

    /**
     * Count the quizzes on the site that have no pass mark configured.
     *
     * @param int|null $courseid Restrict to a single course, or null for all courses.
     * @return int Number of matching quizzes.
     */
    public static function count_quizzes_without_gradepass(?int $courseid): int {
        global $DB;

        [, $from, $params] = self::base_query();
        $where = 'gi.itemtype = :itemtype AND gi.itemmodule = :itemmodule AND (gi.gradepass IS NULL OR gi.gradepass <= 0)';
        if ($courseid !== null) {
            $where .= ' AND gi.courseid = :courseid';
            $params['courseid'] = $courseid;
        }

        return (int) $DB->count_records_sql("SELECT COUNT(1) FROM $from WHERE $where", $params);
    }
}
