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

use admin_setting_configduration;
use local_learningjourney\local\constants;

/**
 * Duration setting enforcing the shortest accessible countdown.
 *
 * A timed redirect must give the learner enough time to read the outcome and
 * cancel it, so anything below the documented minimum is rejected on save
 * rather than silently corrected at display time.
 *
 * The parent class validates through validate_setting(), which receives the
 * duration already reduced to seconds.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class setting_redirect_delay extends admin_setting_configduration {
    /**
     * Validate a submitted duration expressed in seconds.
     *
     * @param int $data Submitted duration in seconds.
     * @return string An error message, or an empty string when the value is acceptable.
     */
    protected function validate_setting(int $data): string {
        if ($data < constants::MIN_REDIRECT_DELAY) {
            return get_string(
                'error_redirectdelay',
                constants::PLUGIN,
                constants::MIN_REDIRECT_DELAY
            );
        }

        return parent::validate_setting($data);
    }
}
