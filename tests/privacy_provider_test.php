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

namespace local_learningjourney;

use core_privacy\local\metadata\collection;
use local_learningjourney\privacy\provider;

/**
 * Unit tests for the privacy provider.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_learningjourney\privacy\provider
 */
final class privacy_provider_test extends \advanced_testcase {
    /**
     * The provider declares the sound preference and nothing else.
     *
     * @return void
     */
    public function test_metadata_declares_only_the_preference(): void {
        $collection = provider::get_metadata(new collection('local_learningjourney'));

        $items = $collection->get_collection();
        $this->assertCount(1, $items);
        $this->assertInstanceOf(\core_privacy\local\metadata\types\user_preference::class, reset($items));
    }

    /**
     * The settings table holds no user identifying column.
     *
     * @return void
     */
    public function test_settings_table_holds_no_user_data(): void {
        global $DB;

        $this->resetAfterTest();

        $columns = array_keys($DB->get_columns('local_learningjourney_setting'));

        $this->assertNotContains('userid', $columns);
        $this->assertSame(['id', 'courseid', 'name', 'value', 'timemodified'], $columns);
    }
}
