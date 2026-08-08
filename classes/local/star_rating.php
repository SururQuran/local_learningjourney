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
 * Maps a percentage to a star count using administrator defined bands.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class star_rating {
    /** @var int Highest number of stars that can be awarded. */
    public const MAX_STARS = 5;

    /** @var int[] Thresholds used when the configured value cannot be parsed. */
    public const DEFAULT_THRESHOLDS = [60, 70, 80, 90, 95];

    /** @var settings_resolver Effective settings for the course. */
    protected settings_resolver $settings;

    /**
     * Create a star rating calculator.
     *
     * @param settings_resolver $settings Effective settings for the course.
     */
    public function __construct(settings_resolver $settings) {
        $this->settings = $settings;
    }

    /**
     * Return the number of stars earned by a percentage.
     *
     * @param float|null $percent Percentage achieved, or null when unknown.
     * @return int Star count between 0 and the maximum.
     */
    public function stars_for(?float $percent): int {
        if ($percent === null) {
            return 0;
        }

        $stars = 0;
        foreach ($this->thresholds() as $index => $threshold) {
            if ($percent >= $threshold) {
                $stars = $index + 1;
            }
        }

        return $stars;
    }

    /**
     * Return the maximum number of stars available.
     *
     * @return int The maximum star count.
     */
    public function max_stars(): int {
        return self::MAX_STARS;
    }

    /**
     * Return the configured, validated star thresholds.
     *
     * @return int[] Five ascending thresholds.
     */
    protected function thresholds(): array {
        $raw = array_map('trim', explode(',', $this->settings->get('starthresholds')));

        return self::normalise($raw);
    }

    /**
     * Repair a threshold list that is malformed or out of order.
     *
     * @param array $raw Raw threshold values.
     * @return int[] Five ascending thresholds between 0 and 100.
     */
    protected static function normalise(array $raw): array {
        if (count($raw) !== self::MAX_STARS) {
            return self::DEFAULT_THRESHOLDS;
        }

        $values = [];
        $previous = -1;
        foreach ($raw as $value) {
            if (!is_numeric($value)) {
                return self::DEFAULT_THRESHOLDS;
            }
            $number = (int) $value;
            if ($number < 0 || $number > 100 || $number <= $previous) {
                return self::DEFAULT_THRESHOLDS;
            }
            $values[] = $number;
            $previous = $number;
        }

        return $values;
    }
}
