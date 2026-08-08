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
 * Course level Learning Journey override form.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

use local_learningjourney\form\course_settings_form;
use local_learningjourney\local\constants;
use local_learningjourney\local\permission;
use local_learningjourney\local\settings_resolver;

$courseid = required_param('id', PARAM_INT);

$course = get_course($courseid);
require_login($course);

$context = context_course::instance($course->id);
permission::require_can_manage_course($context);

$url = new moodle_url('/local/learningjourney/coursesettings.php', ['id' => $courseid]);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('incourse');
$PAGE->set_title(format_string($course->shortname) . ': ' . get_string('coursesettings', constants::PLUGIN));
$PAGE->set_heading(format_string($course->fullname));

$resolver = new settings_resolver($courseid);
$overrides = settings_resolver::get_overrides($courseid);
$filedefinitions = [];
$formdata = ['id' => $courseid];

foreach (settings_resolver::overridable_definitions() as $name => $definition) {
    $isoverridden = array_key_exists($name, $overrides);
    $formdata['override_' . $name] = $isoverridden ? 0 : 1;
    $value = $isoverridden ? $overrides[$name] : $resolver->get($name);

    if ($definition['type'] === 'file') {
        $filedefinitions[$name] = $definition;
        $draftid = file_get_submitted_draft_itemid($name);
        file_prepare_draft_area(
            $draftid,
            $context->id,
            constants::PLUGIN,
            $definition['filearea'],
            0,
            local_learningjourney_file_options($definition)
        );
        $formdata[$name] = $draftid;
        continue;
    }

    if ($definition['type'] === 'html') {
        $formdata[$name] = ['text' => $value, 'format' => FORMAT_HTML];
        continue;
    }

    $formdata[$name] = $value;
}

$form = new course_settings_form($url, ['courseid' => $courseid]);
$form->set_data($formdata);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
}

$data = $form->get_data();
if ($data) {
    $submitted = $form->get_submitted_overrides();

    foreach ($filedefinitions as $name => $definition) {
        if (!in_array($name, $submitted['overridden'], true)) {
            continue;
        }

        file_save_draft_area_files(
            (int) $data->{$name},
            $context->id,
            constants::PLUGIN,
            $definition['filearea'],
            0,
            local_learningjourney_file_options($definition)
        );

        $files = get_file_storage()->get_area_files(
            $context->id,
            constants::PLUGIN,
            $definition['filearea'],
            0,
            'itemid',
            false
        );

        $submitted['values'][$name] = empty($files) ? '' : reset($files)->get_filename();
    }

    settings_resolver::save_overrides($courseid, $submitted['values'], $submitted['overridden']);

    redirect($url, get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
}

$renderer = $PAGE->get_renderer(constants::PLUGIN);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('coursesettings', constants::PLUGIN));
echo $renderer->render_course_settings_header($course);
$form->display();
echo $OUTPUT->footer();
