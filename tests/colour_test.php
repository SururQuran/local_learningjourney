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

use local_learningjourney\local\colour;

/**
 * Unit tests for the colour validation and contrast helper.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_learningjourney\local\colour
 */
final class colour_test extends \advanced_testcase {
    /**
     * Data provider covering valid and invalid colour values.
     *
     * @return array<string, array{0: string, 1: bool}> Value and expected validity.
     */
    public static function colour_provider(): array {
        return [
            'six digit'    => ['#1d6f42', true],
            'three digit'  => ['#fff', true],
            'upper case'   => ['#ABCDEF', true],
            'missing hash' => ['1d6f42', false],
            'too short'    => ['#12', false],
            'not hex'      => ['#zzzzzz', false],
            'empty'        => ['', false],
        ];
    }

    /**
     * Colour values are accepted only in the documented formats.
     *
     * @dataProvider colour_provider
     * @param string $value Candidate colour value.
     * @param bool $expected Whether the value should be accepted.
     * @return void
     */
    public function test_is_valid(string $value, bool $expected): void {
        $this->assertSame($expected, colour::is_valid($value));
    }

    /**
     * Shorthand colours expand and invalid colours fall back safely.
     *
     * @return void
     */
    public function test_normalise(): void {
        $this->assertSame('#ffffff', colour::normalise('#fff', '#000000'));
        $this->assertSame('#1d6f42', colour::normalise('#1D6F42', '#000000'));
        $this->assertSame('#123456', colour::normalise('not a colour', '#123456'));
        $this->assertSame('#000000', colour::normalise('bad', 'also bad'));
    }

    /**
     * Contrast ratios match the values defined by WCAG.
     *
     * @return void
     */
    public function test_contrast_ratio(): void {
        $this->assertEqualsWithDelta(21.0, colour::contrast_ratio('#000000', '#ffffff'), 0.01);
        $this->assertEqualsWithDelta(1.0, colour::contrast_ratio('#123456', '#123456'), 0.01);
    }

    /**
     * The AA threshold is applied at the documented boundaries.
     *
     * @return void
     */
    public function test_meets_aa(): void {
        $this->assertTrue(colour::meets_aa('#ffffff', '#1d6f42'));
        $this->assertFalse(colour::meets_aa('#cccccc', '#ffffff'));
        $this->assertTrue(colour::meets_aa('#767676', '#ffffff', true));
    }
}
