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
use local_learningjourney\local\star_rating;

/**
 * Unit tests for the star rating calculator.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_learningjourney\local\star_rating
 */
final class star_rating_test extends \advanced_testcase {
    /**
     * Data provider walking every default band and its boundaries.
     *
     * @return array<string, array{0: float|null, 1: int}> Percentage and expected stars.
     */
    public static function percentage_provider(): array {
        return [
            'unknown'          => [null, 0],
            'below any band'   => [59.9, 0],
            'band one lower'   => [60.0, 1],
            'band one upper'   => [69.9, 1],
            'band two lower'   => [70.0, 2],
            'band two upper'   => [79.9, 2],
            'band three lower' => [80.0, 3],
            'band three upper' => [89.9, 3],
            'band four lower'  => [90.0, 4],
            'band four upper'  => [94.9, 4],
            'band five lower'  => [95.0, 5],
            'band five upper'  => [100.0, 5],
        ];
    }

    /**
     * The default bands award the documented number of stars.
     *
     * @dataProvider percentage_provider
     * @param float|null $percent Percentage achieved.
     * @param int $expected Expected star count.
     * @return void
     */
    public function test_stars_for(?float $percent, int $expected): void {
        $this->resetAfterTest();

        $rating = new star_rating(new settings_resolver(0));

        $this->assertSame($expected, $rating->stars_for($percent));
    }

    /**
     * Administrator customised bands are honoured.
     *
     * @return void
     */
    public function test_custom_thresholds(): void {
        $this->resetAfterTest();
        set_config('starthresholds', '50,60,70,80,90', 'local_learningjourney');

        $rating = new star_rating(new settings_resolver(0));

        $this->assertSame(0, $rating->stars_for(49.0));
        $this->assertSame(1, $rating->stars_for(50.0));
        $this->assertSame(3, $rating->stars_for(70.0));
        $this->assertSame(5, $rating->stars_for(95.0));
    }

    /**
     * A course may set its own bands.
     *
     * @return void
     */
    public function test_course_override_thresholds(): void {
        $this->resetAfterTest();
        set_config('starthresholds', '60,70,80,90,95', 'local_learningjourney');

        $course = $this->getDataGenerator()->create_course();
        settings_resolver::save_overrides(
            (int) $course->id,
            ['starthresholds' => '40,55,70,85,95'],
            ['starthresholds']
        );

        $rating = new star_rating(new settings_resolver((int) $course->id));

        $this->assertSame(1, $rating->stars_for(40.0));
        $this->assertSame(2, $rating->stars_for(55.0));
    }

    /**
     * A malformed threshold list falls back to the documented defaults.
     *
     * @return void
     */
    public function test_malformed_thresholds_are_repaired(): void {
        $this->resetAfterTest();
        set_config('starthresholds', '90,10,not-a-number', 'local_learningjourney');

        $rating = new star_rating(new settings_resolver(0));

        $this->assertSame(1, $rating->stars_for(60.0));
        $this->assertSame(5, $rating->stars_for(99.0));
    }

    /**
     * Descending thresholds are rejected in favour of the defaults.
     *
     * @return void
     */
    public function test_descending_thresholds_are_repaired(): void {
        $this->resetAfterTest();
        set_config('starthresholds', '90,80,70,60,50', 'local_learningjourney');

        $rating = new star_rating(new settings_resolver(0));

        $this->assertSame(1, $rating->stars_for(60.0));
    }
}
