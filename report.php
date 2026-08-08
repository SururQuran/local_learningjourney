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
 * Report listing quizzes that have no pass mark configured.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_learningjourney\local\constants;
use local_learningjourney\local\report\gradepass_report;

admin_externalpage_setup('local_learningjourney_report');

$courseid = optional_param('courseid', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$perpage = 50;

$filter = $courseid > 0 ? $courseid : null;
$total = gradepass_report::count_quizzes_without_gradepass($filter);
$rows = gradepass_report::get_quizzes_without_gradepass($filter, $page, $perpage);

$baseurl = new moodle_url('/local/learningjourney/report.php', ['courseid' => $courseid]);
$paging = new paging_bar($total, $page, $perpage, $baseurl);

$renderer = $PAGE->get_renderer(constants::PLUGIN);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('report_title', constants::PLUGIN));
echo $renderer->render_gradepass_report($rows, $paging);
echo $OUTPUT->footer();
