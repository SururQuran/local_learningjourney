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

namespace local_learningjourney\output;

use html_table;
use html_table_row;
use html_writer;
use moodle_url;
use paging_bar;
use plugin_renderer_base;
use stdClass;

/**
 * Renderer for the Learning Journey plugin.
 *
 * All learner facing markup lives in Mustache templates so that themes can
 * override the presentation without touching the plugin.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {
    /**
     * Render a learner's result page.
     *
     * @param result_page $page The renderable result page.
     * @return string Rendered HTML.
     */
    public function render_result_page(result_page $page): string {
        return $this->render_from_template($page->get_template_name(), $page->export_for_template($this));
    }

    /**
     * Render the introduction shown above the course override form.
     *
     * @param stdClass $course The course being configured.
     * @return string Rendered HTML.
     */
    public function render_course_settings_header(stdClass $course): string {
        return $this->output->notification(
            get_string('coursesettings_intro', 'local_learningjourney', format_string($course->fullname)),
            'info',
            false
        );
    }

    /**
     * Render the report of quizzes that have no pass mark configured.
     *
     * @param stdClass[] $rows Report rows.
     * @param paging_bar $paging Paging control for the report.
     * @return string Rendered HTML.
     */
    public function render_gradepass_report(array $rows, paging_bar $paging): string {
        if (empty($rows)) {
            return $this->output->notification(
                get_string('report_noquizzes', 'local_learningjourney'),
                'info',
                false
            );
        }

        $table = new html_table();
        $table->head = [
            get_string('report_course', 'local_learningjourney'),
            get_string('report_quiz', 'local_learningjourney'),
        ];
        $table->attributes['class'] = 'generaltable ljy-report';

        foreach ($rows as $row) {
            $courseurl = new moodle_url('/course/view.php', ['id' => (int) $row->courseid]);
            $table->data[] = new html_table_row([
                html_writer::link($courseurl, format_string($row->coursename)),
                format_string((string) $row->itemname),
            ]);
        }

        return html_writer::table($table) . $this->render($paging);
    }
}
