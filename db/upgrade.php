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
 * Upgrade steps for the Learning Journey plugin.
 *
 * The sparse key/value settings table absorbs new settings without schema
 * changes, so no upgrade steps are required for the current release. The
 * course settings cache is purged on every upgrade.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Execute the Learning Journey upgrade from the given old version.
 *
 * @param int $oldversion The currently installed plugin version.
 * @return bool Always true once every applicable step has run.
 */
function xmldb_local_learningjourney_upgrade(int $oldversion): bool {
    \cache_helper::purge_by_definition('local_learningjourney', 'coursesettings');

    return true;
}
