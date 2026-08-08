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

namespace local_learningjourney\local;

/**
 * Colour validation and WCAG contrast helpers.
 *
 * Every colour reaching a CSS custom property passes through this class, so a
 * malformed or hostile setting value can never be injected into the page.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class colour {
    /** @var string Strict three or six digit hexadecimal colour pattern. */
    private const PATTERN = '/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/';

    /** @var float Minimum WCAG AA contrast ratio for normal sized text. */
    public const AA_NORMAL = 4.5;

    /** @var float Minimum WCAG AA contrast ratio for large text. */
    public const AA_LARGE = 3.0;

    /**
     * Prevent instantiation of this stateless helper.
     */
    private function __construct() {
    }

    /**
     * Determine whether a value is a syntactically valid hexadecimal colour.
     *
     * @param string $value Candidate colour value.
     * @return bool True when the value is a #rgb or #rrggbb string.
     */
    public static function is_valid(string $value): bool {
        return (bool) preg_match(self::PATTERN, trim($value));
    }

    /**
     * Return a safe, expanded six digit colour value.
     *
     * @param string $value Candidate colour value.
     * @param string $fallback Colour used when the candidate is invalid.
     * @return string A #rrggbb colour value in lower case.
     */
    public static function normalise(string $value, string $fallback): string {
        $value = trim($value);
        if (!self::is_valid($value)) {
            $value = self::is_valid($fallback) ? $fallback : '#000000';
        }
        $value = strtolower($value);
        if (strlen($value) === 4) {
            $value = '#' . $value[1] . $value[1] . $value[2] . $value[2] . $value[3] . $value[3];
        }

        return $value;
    }

    /**
     * Calculate the WCAG contrast ratio between two colours.
     *
     * @param string $foreground Foreground colour.
     * @param string $background Background colour.
     * @return float Contrast ratio between 1.0 and 21.0.
     */
    public static function contrast_ratio(string $foreground, string $background): float {
        $first = self::relative_luminance(self::normalise($foreground, '#000000'));
        $second = self::relative_luminance(self::normalise($background, '#ffffff'));
        $lighter = max($first, $second);
        $darker = min($first, $second);

        return round(($lighter + 0.05) / ($darker + 0.05), 2);
    }

    /**
     * Determine whether a colour pair satisfies WCAG AA contrast.
     *
     * @param string $foreground Foreground colour.
     * @param string $background Background colour.
     * @param bool $large Whether the text is large scale.
     * @return bool True when the pair meets the applicable threshold.
     */
    public static function meets_aa(string $foreground, string $background, bool $large = false): bool {
        $required = $large ? self::AA_LARGE : self::AA_NORMAL;

        return self::contrast_ratio($foreground, $background) >= $required;
    }

    /**
     * Calculate the relative luminance of a normalised colour.
     *
     * @param string $hex A #rrggbb colour value.
     * @return float Relative luminance between 0.0 and 1.0.
     */
    private static function relative_luminance(string $hex): float {
        [$red, $green, $blue] = self::to_rgb($hex);
        $channels = [];
        foreach ([$red, $green, $blue] as $channel) {
            $value = $channel / 255;
            $channels[] = $value <= 0.03928 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        }

        return (0.2126 * $channels[0]) + (0.7152 * $channels[1]) + (0.0722 * $channels[2]);
    }

    /**
     * Split a normalised colour into its red, green and blue components.
     *
     * @param string $hex A #rrggbb colour value.
     * @return int[] Three integers between 0 and 255.
     */
    private static function to_rgb(string $hex): array {
        return [
            (int) hexdec(substr($hex, 1, 2)),
            (int) hexdec(substr($hex, 3, 2)),
            (int) hexdec(substr($hex, 5, 2)),
        ];
    }
}
