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
use local_learningjourney\local\settings_resolver;

/**
 * Integer setting constrained to the range declared in the settings register.
 *
 * The pass mark this guards remains fully configurable: only the declared
 * bounds are enforced, never a particular value.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class setting_percentage extends admin_setting_configtext {
    /** @var int Lowest value the setting accepts. */
    protected int $minimum;

    /** @var int Highest value the setting accepts. */
    protected int $maximum;

    /**
     * Create a bounded integer setting.
     *
     * @param string $name Full setting name including the plugin prefix.
     * @param string|\lang_string $visiblename Setting label.
     * @param string|\lang_string $description Setting description.
     * @param string $defaultsetting Default value.
     * @param int $minimum Lowest accepted value.
     * @param int $maximum Highest accepted value.
     */
    public function __construct(
        $name,
        $visiblename,
        $description,
        $defaultsetting,
        int $minimum = 0,
        int $maximum = 100
    ) {
        $this->minimum = $minimum;
        $this->maximum = $maximum;

        parent::__construct($name, $visiblename, $description, $defaultsetting, PARAM_INT, 6);
    }

    /**
     * Validate a submitted value against the declared bounds.
     *
     * @param string $data Submitted value.
     * @return bool|string True when valid, or a translated error message.
     */
    public function validate($data) {
        $parent = parent::validate($data);
        if ($parent !== true) {
            return $parent;
        }

        return settings_resolver::validate_range(
            (string) $data,
            $this->minimum,
            $this->maximum
        ) ?? true;
    }
}
