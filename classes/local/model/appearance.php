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

namespace local_learningjourney\local\model;

/**
 * Immutable description of the validated presentation settings for a page.
 *
 * Every colour held here has already passed validation, so the values are safe
 * to emit as CSS custom properties.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class appearance {
    /**
     * Create an appearance description.
     *
     * @param string $themecolour Primary theme colour.
     * @param string $buttoncolour Background colour of the primary button.
     * @param string $buttontextcolour Text colour of the primary button.
     * @param string $progressbarcolour Fill colour of the progress bar.
     * @param string $progressbgcolour Track colour of the progress bar.
     * @param string $backgroundcolour Page background colour.
     * @param string|null $backgroundimageurl Optional decorative background image.
     * @param array $effects Enabled celebration effects keyed by name.
     * @param string $layout Page layout mode.
     * @param string|null $soundurl Administrator supplied applause sound.
     * @param array $display Display toggles keyed by setting name.
     * @param bool $autoredirect Whether the countdown is enabled.
     * @param int $redirectdelay Countdown length in seconds.
     * @param string $iconset Icon style used on the page.
     */
    public function __construct(
        /** @var string Primary theme colour. */
        public readonly string $themecolour = '#1d6f42',
        /** @var string Background colour of the primary button. */
        public readonly string $buttoncolour = '#1d6f42',
        /** @var string Text colour of the primary button. */
        public readonly string $buttontextcolour = '#ffffff',
        /** @var string Fill colour of the progress bar. */
        public readonly string $progressbarcolour = '#1d6f42',
        /** @var string Track colour of the progress bar. */
        public readonly string $progressbgcolour = '#e9ecef',
        /** @var string Page background colour. */
        public readonly string $backgroundcolour = '#ffffff',
        /** @var string|null Optional decorative background image. */
        public readonly ?string $backgroundimageurl = null,
        /** @var array<string, bool> Enabled celebration effects keyed by name. */
        public readonly array $effects = [],
        /** @var string Page layout mode. */
        public readonly string $layout = 'standard',
        /** @var string|null Administrator supplied applause sound. */
        public readonly ?string $soundurl = null,
        /** @var array<string, bool> Display toggles keyed by setting name. */
        public readonly array $display = [],
        /** @var bool Whether the countdown is enabled. */
        public readonly bool $autoredirect = false,
        /** @var int Countdown length in seconds. */
        public readonly int $redirectdelay = 15,
        /** @var string Icon style used on the page. */
        public readonly string $iconset = 'fontawesome',
    ) {
    }

    /**
     * Determine whether a display toggle is switched on.
     *
     * @param string $name Unqualified name of the display setting.
     * @return bool True when the element should be shown.
     */
    public function shows(string $name): bool {
        return !empty($this->display[$name]);
    }

    /**
     * Determine whether a celebration effect is switched on.
     *
     * @param string $name Unqualified name of the effect.
     * @return bool True when the effect should run.
     */
    public function has_effect(string $name): bool {
        return !empty($this->effects[$name]);
    }
}
