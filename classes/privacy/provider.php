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

namespace local_learningjourney\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\writer;
use local_learningjourney\local\constants;

/**
 * Privacy provider for the Learning Journey plugin.
 *
 * The plugin stores no personal data in the database. Course overrides are
 * configuration, not user data. The only user linked value is the sound mute
 * preference declared below.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\user_preference_provider {
    /**
     * Describe the personal data stored by this plugin.
     *
     * @param collection $collection The collection to add metadata to.
     * @return collection The updated collection.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_user_preference(
            constants::PREF_MUTE,
            'privacy:preference:mute'
        );

        return $collection;
    }

    /**
     * Export the user preferences stored by this plugin.
     *
     * @param int $userid Identifier of the user being exported.
     * @return void
     */
    public static function export_user_preferences(int $userid): void {
        $preference = get_user_preferences(constants::PREF_MUTE, null, $userid);
        if ($preference === null) {
            return;
        }

        $description = get_string(
            $preference ? 'privacy:preference:mute:on' : 'privacy:preference:mute:off',
            constants::PLUGIN
        );

        writer::export_user_preference(
            constants::PLUGIN,
            constants::PREF_MUTE,
            (string) $preference,
            $description
        );
    }
}
