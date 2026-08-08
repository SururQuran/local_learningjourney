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
 * Administration settings for the Learning Journey plugin.
 *
 * Every page is generated from the settings register so that a setting is
 * declared in exactly one place.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_learningjourney\admin\setting_colour;
use local_learningjourney\admin\setting_percentage;
use local_learningjourney\admin\setting_redirect_delay;
use local_learningjourney\admin\setting_star_thresholds;
use local_learningjourney\local\constants;
use local_learningjourney\local\settings_resolver;

if ($hassiteconfig) {
    $component = constants::PLUGIN;

    $ADMIN->add('localplugins', new admin_category($component, new lang_string('pluginname', $component)));

    $pages = [
        settings_resolver::PAGE_GENERAL,
        settings_resolver::PAGE_MESSAGES,
        settings_resolver::PAGE_APPEARANCE,
        settings_resolver::PAGE_EFFECTS,
        settings_resolver::PAGE_DISPLAY,
    ];

    foreach ($pages as $pagename) {
        $settingpage = new admin_settingpage(
            $component . '_' . $pagename,
            new lang_string('settingspage_' . $pagename, $component)
        );

        $settingpage->add(new admin_setting_heading(
            $component . '/heading_' . $pagename,
            '',
            new lang_string('settingspage_' . $pagename . '_intro', $component)
        ));

        foreach (settings_resolver::definitions_for_page($pagename) as $name => $definition) {
            $key = $component . '/' . $name;
            $title = new lang_string('setting_' . $name, $component);
            $description = new lang_string('setting_' . $name . '_desc', $component);
            $default = $definition['default'];

            switch ($definition['type']) {
                case 'bool':
                    $setting = new admin_setting_configcheckbox($key, $title, $description, $default);
                    break;
                case 'int':
                    $setting = new setting_percentage(
                        $key,
                        $title,
                        $description,
                        $default,
                        (int) ($definition['min'] ?? 0),
                        (int) ($definition['max'] ?? PHP_INT_MAX)
                    );
                    break;
                case 'duration':
                    $setting = new setting_redirect_delay($key, $title, $description, (int) $default);
                    break;
                case 'colour':
                    $setting = new setting_colour($key, $title, $description, $default);
                    break;
                case 'select':
                    $choices = [];
                    foreach ((array) $definition['options'] as $value => $stringkey) {
                        $choices[$value] = new lang_string($stringkey, $component);
                    }
                    $setting = new admin_setting_configselect($key, $title, $description, $default, $choices);
                    break;
                case 'html':
                    $setting = new admin_setting_confightmleditor($key, $title, $description, $default, PARAM_RAW);
                    break;
                case 'file':
                    $setting = new admin_setting_configstoredfile(
                        $key,
                        $title,
                        $description,
                        (string) $definition['filearea'],
                        0,
                        [
                            'maxfiles'       => 1,
                            'maxbytes'       => (int) $definition['maxbytes'],
                            'accepted_types' => (array) $definition['accepted'],
                        ]
                    );
                    break;
                case 'stars':
                    $setting = new setting_star_thresholds($key, $title, $description, $default, PARAM_RAW, 24);
                    break;
                case 'text':
                default:
                    $setting = new admin_setting_configtext($key, $title, $description, $default, PARAM_TEXT);
                    break;
            }

            if ($setting instanceof setting_colour && $name === 'buttoncolour') {
                $setting->set_contrast_pair('buttontextcolour');
            }

            $settingpage->add($setting);
        }

        $ADMIN->add($component, $settingpage);
    }

    $ADMIN->add($component, new admin_externalpage(
        $component . '_report',
        new lang_string('report_title', $component),
        new moodle_url('/local/learningjourney/report.php')
    ));
}
