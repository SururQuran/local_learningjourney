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
 * Learning Journey post quiz result page.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use local_learningjourney\local\constants;
use local_learningjourney\local\permission;
use local_learningjourney\local\quiz_adapter;
use local_learningjourney\local\result_builder;
use local_learningjourney\local\settings_resolver;
use local_learningjourney\output\result_page;

$attemptid = required_param('attempt', PARAM_INT);

$quiz = quiz_adapter::create($attemptid);
$course = $quiz->get_course();
$cm = $quiz->get_cm();

require_login($course, false, $cm);

$context = $quiz->get_context();
permission::require_can_view_result($context, $quiz->get_userid());

$url = new moodle_url('/local/learningjourney/result.php', ['attempt' => $attemptid]);
$PAGE->set_url($url);
$PAGE->set_cm($cm, $course);
$PAGE->set_pagelayout('incourse');
$PAGE->set_title(format_string($course->shortname) . ': ' . get_string('resulttitle', constants::PLUGIN));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->add_body_class('ljy-body');

$settings = new settings_resolver((int) $course->id);
if ($settings->get('layout') === 'focused') {
    $PAGE->set_secondary_navigation(false);
}

$result = (new result_builder($quiz, $quiz->get_userid()))->build();
$page = new result_page($result);

foreach ($page->get_required_modules() as $module => $arguments) {
    $PAGE->requires->js_call_amd($module, 'init', $arguments);
}

$renderer = $PAGE->get_renderer(constants::PLUGIN);

echo $OUTPUT->header();
echo $renderer->render($page);
echo $OUTPUT->footer();
