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
 * Restore handler for Learning Journey course overrides.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Restores the plugin's per course overrides and files.
 *
 * Setting names that are unknown to the installed plugin version are skipped,
 * which keeps restores between plugin versions safe in both directions.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_local_learningjourney_plugin extends restore_local_plugin {
    /**
     * Define the course level paths handled by this plugin.
     *
     * @return restore_path_element[] The paths to process.
     */
    protected function define_course_plugin_structure() {
        $paths = [];

        $elename = 'local_learningjourney_setting';
        $elepath = $this->get_pathfor('/settings/setting');
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths;
    }

    /**
     * Restore a single Learning Journey override row.
     *
     * @param array|stdClass $data Serialised row from the backup file.
     * @return void
     */
    public function process_local_learningjourney_setting($data) {
        global $DB;

        $data = (object) $data;
        $courseid = $this->task->get_courseid();

        if (\local_learningjourney\local\settings_resolver::definition($data->name) === null) {
            debugging('Skipping unknown Learning Journey setting: ' . $data->name, DEBUG_DEVELOPER);

            return;
        }

        $existing = $DB->get_record('local_learningjourney_setting', [
            'courseid' => $courseid,
            'name'     => $data->name,
        ]);

        $record = (object) [
            'courseid'     => $courseid,
            'name'         => $data->name,
            'value'        => $data->value,
            'timemodified' => time(),
        ];

        if ($existing) {
            $record->id = $existing->id;
            $DB->update_record('local_learningjourney_setting', $record);
        } else {
            $DB->insert_record('local_learningjourney_setting', $record);
        }
    }

    /**
     * Restore the plugin's course level files once the course is in place.
     *
     * @return void
     */
    protected function after_execute_course() {
        $this->add_related_files('local_learningjourney', 'background', null);
        $this->add_related_files('local_learningjourney', 'sound', null);

        \local_learningjourney\local\settings_resolver::purge($this->task->get_courseid());
    }
}
