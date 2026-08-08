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
 * Backup handler for Learning Journey course overrides.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Adds the plugin's per course overrides and files to a course backup.
 *
 * No user data is backed up because the plugin stores none.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_local_learningjourney_plugin extends backup_local_plugin {
    /**
     * Define the course level structure contributed by this plugin.
     *
     * @return backup_plugin_element The populated plugin element.
     */
    protected function define_course_plugin_structure() {
        $plugin = $this->get_plugin_element();

        $wrapper = new backup_nested_element($this->get_recommended_name());
        $plugin->add_child($wrapper);

        $settings = new backup_nested_element('settings');
        $wrapper->add_child($settings);

        $setting = new backup_nested_element('setting', ['id'], ['name', 'value', 'timemodified']);
        $settings->add_child($setting);

        $setting->set_source_table('local_learningjourney_setting', ['courseid' => backup::VAR_COURSEID]);

        $wrapper->annotate_files('local_learningjourney', 'background', null);
        $wrapper->annotate_files('local_learningjourney', 'sound', null);

        return $plugin;
    }
}
