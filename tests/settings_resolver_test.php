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

use local_learningjourney\local\settings_resolver;

/**
 * Unit tests for the settings register and resolver.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_learningjourney\local\settings_resolver
 */
final class settings_resolver_test extends \advanced_testcase {
    /**
     * Every declared setting carries the metadata the register promises.
     *
     * @return void
     */
    public function test_register_is_well_formed(): void {
        $definitions = settings_resolver::all_definitions();

        $this->assertNotEmpty($definitions);
        foreach ($definitions as $name => $definition) {
            $this->assertIsString($name);
            $this->assertArrayHasKey('type', $definition);
            $this->assertArrayHasKey('default', $definition);
            $this->assertArrayHasKey('page', $definition);
            $this->assertArrayHasKey('overridable', $definition);
        }
    }

    /**
     * Every declared setting belongs to exactly one administration page.
     *
     * @return void
     */
    public function test_every_setting_belongs_to_a_page(): void {
        $pages = [
            settings_resolver::PAGE_GENERAL,
            settings_resolver::PAGE_MESSAGES,
            settings_resolver::PAGE_APPEARANCE,
            settings_resolver::PAGE_EFFECTS,
            settings_resolver::PAGE_DISPLAY,
        ];

        $counted = 0;
        foreach ($pages as $page) {
            $counted += count(settings_resolver::definitions_for_page($page));
        }

        $this->assertSame(count(settings_resolver::all_definitions()), $counted);
    }

    /**
     * Site values are returned when a course records no override.
     *
     * @return void
     */
    public function test_site_value_is_used_without_an_override(): void {
        $this->resetAfterTest();
        set_config('fallbackgradepass', '75', 'local_learningjourney');

        $resolver = new settings_resolver(0);

        $this->assertSame(75, $resolver->get_int('fallbackgradepass'));
    }

    /**
     * Requesting an undeclared setting is a developer error.
     *
     * @return void
     */
    public function test_unknown_setting_throws(): void {
        $this->resetAfterTest();
        $this->expectException(\coding_exception::class);

        (new settings_resolver(0))->get('no_such_setting');
    }

    /**
     * A course override replaces the site value for that course only.
     *
     * @return void
     */
    public function test_course_override_applies_to_one_course_only(): void {
        $this->resetAfterTest();
        set_config('fallbackgradepass', '60', 'local_learningjourney');

        $overridden = $this->getDataGenerator()->create_course();
        $untouched = $this->getDataGenerator()->create_course();

        settings_resolver::save_overrides(
            (int) $overridden->id,
            ['fallbackgradepass' => '75'],
            ['fallbackgradepass']
        );

        $this->assertSame(75, (new settings_resolver((int) $overridden->id))->get_int('fallbackgradepass'));
        $this->assertSame(60, (new settings_resolver((int) $untouched->id))->get_int('fallbackgradepass'));
        $this->assertSame(60, (new settings_resolver(0))->get_int('fallbackgradepass'));
    }

    /**
     * Removing an override restores the inherited site value.
     *
     * @return void
     */
    public function test_removing_an_override_restores_inheritance(): void {
        $this->resetAfterTest();
        set_config('fallbackgradepass', '60', 'local_learningjourney');

        $course = $this->getDataGenerator()->create_course();

        settings_resolver::save_overrides((int) $course->id, ['fallbackgradepass' => '80'], ['fallbackgradepass']);
        $this->assertSame(80, (new settings_resolver((int) $course->id))->get_int('fallbackgradepass'));

        settings_resolver::save_overrides((int) $course->id, [], []);
        $this->assertSame(60, (new settings_resolver((int) $course->id))->get_int('fallbackgradepass'));
        $this->assertSame([], settings_resolver::get_overrides((int) $course->id));
    }

    /**
     * Only overridden settings occupy a row, keeping the table sparse.
     *
     * @return void
     */
    public function test_storage_is_sparse(): void {
        global $DB;

        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();

        settings_resolver::save_overrides((int) $course->id, ['successtitle' => 'Well done'], ['successtitle']);

        $this->assertSame(1, $DB->count_records(settings_resolver::TABLE, ['courseid' => $course->id]));
    }

    /**
     * A saved override is visible immediately, so the cache is invalidated.
     *
     * @return void
     */
    public function test_cache_is_invalidated_on_save(): void {
        $this->resetAfterTest();
        set_config('successtitle', 'Site title', 'local_learningjourney');

        $course = $this->getDataGenerator()->create_course();

        $this->assertSame('Site title', (new settings_resolver((int) $course->id))->get('successtitle'));

        settings_resolver::save_overrides((int) $course->id, ['successtitle' => 'Course title'], ['successtitle']);

        $this->assertSame('Course title', (new settings_resolver((int) $course->id))->get('successtitle'));
    }

    /**
     * Deleting a course removes its overrides.
     *
     * @return void
     */
    public function test_course_deletion_removes_overrides(): void {
        global $DB;

        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();

        settings_resolver::save_overrides((int) $course->id, ['successtitle' => 'Gone'], ['successtitle']);
        settings_resolver::delete_course((int) $course->id);

        $this->assertSame(0, $DB->count_records(settings_resolver::TABLE, ['courseid' => $course->id]));
    }

    /**
     * The site level switch overrides an enabling course override.
     *
     * @return void
     */
    public function test_site_disable_is_absolute(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();

        settings_resolver::save_overrides((int) $course->id, ['enabled' => '1'], ['enabled']);
        set_config('enabled', '0', 'local_learningjourney');

        $this->assertFalse(settings_resolver::is_enabled_for_course((int) $course->id));
    }

    /**
     * A course may switch the plugin off while the site leaves it on.
     *
     * @return void
     */
    public function test_course_may_opt_out(): void {
        $this->resetAfterTest();
        set_config('enabled', '1', 'local_learningjourney');

        $course = $this->getDataGenerator()->create_course();
        settings_resolver::save_overrides((int) $course->id, ['enabled' => '0'], ['enabled']);

        $this->assertFalse(settings_resolver::is_enabled_for_course((int) $course->id));
    }
}
