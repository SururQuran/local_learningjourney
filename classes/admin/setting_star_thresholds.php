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

use admin_setting_configtext;

/**
 * Text setting holding five ascending star rating thresholds.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class setting_star_thresholds extends admin_setting_configtext {
    /** @var int Number of thresholds the setting must contain. */
    protected const REQUIRED = 5;

    /**
     * Validate a submitted threshold list.
     *
     * @param string $data Submitted value.
     * @return bool|string True when valid, or a translated error message.
     */
    public function validate($data) {
        $parts = array_map('trim', explode(',', (string) $data));
        if (count($parts) !== self::REQUIRED) {
            return get_string('error_thresholdcount', 'local_learningjourney', self::REQUIRED);
        }

        $previous = -1;
        foreach ($parts as $part) {
            if (!preg_match('/^\d{1,3}$/', $part)) {
                return get_string('error_thresholdnumeric', 'local_learningjourney');
            }
            $value = (int) $part;
            if ($value > 100 || $value <= $previous) {
                return get_string('error_thresholdorder', 'local_learningjourney');
            }
            $previous = $value;
        }

        return true;
    }
}
