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

namespace local_learningjourney\admin;

use admin_setting_configcolourpicker;
use core\notification;
use local_learningjourney\local\colour;

/**
 * Colour picker setting that validates its value and warns about poor contrast.
 *
 * The parent contract requires validate() to return the sanitised value, or
 * false when the value is unacceptable; the returned value is what gets stored.
 *
 * The contrast warning is deliberately advisory rather than blocking:
 * administrators retain control, but are told when a choice falls below WCAG AA.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class setting_colour extends admin_setting_configcolourpicker {
    /** @var string|null Name of the setting this colour is displayed against. */
    protected ?string $contrastagainst = null;

    /**
     * Record the setting whose colour this one is displayed against.
     *
     * @param string $name Unqualified name of the paired setting.
     * @return void
     */
    public function set_contrast_pair(string $name): void {
        $this->contrastagainst = $name;
    }

    /**
     * Validate and sanitise a submitted colour value.
     *
     * @param string $data Submitted value.
     * @return string|false The value to store, or false when it is not a colour.
     */
    protected function validate($data) {
        $value = trim((string) $data);

        return colour::is_valid($value) ? $value : false;
    }

    /**
     * Store the setting and raise an advisory contrast warning where relevant.
     *
     * @param string $data Submitted value.
     * @return string Empty string on success, or an error message.
     */
    public function write_setting($data) {
        $result = parent::write_setting($data);

        if ($result === '' && $this->contrastagainst !== null) {
            $against = (string) get_config('local_learningjourney', $this->contrastagainst);
            if ($against !== '' && !colour::meets_aa((string) $data, $against)) {
                notification::warning(get_string('warning_lowcontrast', 'local_learningjourney', [
                    'ratio' => colour::contrast_ratio((string) $data, $against),
                    'name'  => (string) $this->visiblename,
                ]));
            }
        }

        return $result;
    }
}
