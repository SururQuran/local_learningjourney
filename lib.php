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
 * Public callbacks for the Learning Journey plugin.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_learningjourney\local\constants;
use local_learningjourney\local\permission;

/**
 * Add the Learning Journey item to the course secondary navigation.
 *
 * @param navigation_node $navigation The course navigation node.
 * @param stdClass $course The course being viewed.
 * @param context_course $context The context of the course.
 * @return void
 */
function local_learningjourney_extend_navigation_course(
    navigation_node $navigation,
    stdClass $course,
    context_course $context
): void {
    if (!permission::can_manage_course($context)) {
        return;
    }

    $navigation->add(
        get_string('coursesettings', constants::PLUGIN),
        new moodle_url('/local/learningjourney/coursesettings.php', ['id' => (int) $course->id]),
        navigation_node::TYPE_SETTING,
        null,
        'local_learningjourney',
        new pix_icon('icon', '', constants::PLUGIN)
    );
}

/**
 * Serve the background image and applause sound files.
 *
 * @param stdClass $course The course the file belongs to, if any.
 * @param stdClass|cm_info|null $cm The course module the file belongs to, if any.
 * @param context $context The context the file was uploaded in.
 * @param string $filearea The file area being requested.
 * @param array $args The remaining path arguments.
 * @param bool $forcedownload Whether the file should be sent as a download.
 * @param array $options Additional options affecting file serving.
 * @return bool False when the file cannot be served.
 */
function local_learningjourney_pluginfile(
    $course,
    $cm,
    context $context,
    string $filearea,
    array $args,
    bool $forcedownload,
    array $options = []
): bool {
    $allowed = [constants::FILEAREA_BACKGROUND, constants::FILEAREA_SOUND];
    if (!in_array($filearea, $allowed, true)) {
        return false;
    }

    if (!in_array($context->contextlevel, [CONTEXT_SYSTEM, CONTEXT_COURSE], true)) {
        return false;
    }

    if ($context->contextlevel === CONTEXT_COURSE) {
        require_login($course, false, $cm);
    } else {
        require_login(null, false);
    }

    $filename = array_pop($args);
    $filepath = empty($args) ? '/' : '/' . implode('/', $args) . '/';

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, constants::PLUGIN, $filearea, 0, $filepath, $filename);
    if (!$file || $file->is_directory()) {
        return false;
    }

    send_stored_file($file, DAYSECS, 0, $forcedownload, $options);

    return true;
}

/**
 * Return the file manager options declared for a Learning Journey file setting.
 *
 * @param array $definition The setting definition from the settings register.
 * @return array The file manager and file area options.
 */
function local_learningjourney_file_options(array $definition): array {
    return [
        'subdirs'        => 0,
        'maxfiles'       => 1,
        'maxbytes'       => (int) ($definition['maxbytes'] ?? 0),
        'accepted_types' => (array) ($definition['accepted'] ?? '*'),
    ];
}
