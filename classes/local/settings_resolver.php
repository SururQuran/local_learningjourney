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

use cache;
use cache_application;
use coding_exception;

/**
 * Single source of truth for every Learning Journey configuration value.
 *
 * The settings register declared by {@see self::all_definitions()} drives the
 * administration pages, the course override form, the backup handler and the
 * mobile payload. No other file may declare a setting.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class settings_resolver {
    /** @var string Administration page holding general behaviour settings. */
    public const PAGE_GENERAL = 'general';

    /** @var string Administration page holding message settings. */
    public const PAGE_MESSAGES = 'messages';

    /** @var string Administration page holding appearance settings. */
    public const PAGE_APPEARANCE = 'appearance';

    /** @var string Administration page holding celebration effect settings. */
    public const PAGE_EFFECTS = 'effects';

    /** @var string Administration page holding display and scoring settings. */
    public const PAGE_DISPLAY = 'display';

    /** @var string Database table holding sparse per course overrides. */
    public const TABLE = 'local_learningjourney_setting';

    /** @var int Identifier of the course whose settings are resolved. */
    protected int $courseid;

    /** @var array<string, string>|null Lazily resolved merged settings map. */
    protected ?array $merged = null;

    /**
     * Create a resolver bound to a single course.
     *
     * @param int $courseid Course whose overrides apply, or 0 for site values only.
     */
    public function __construct(int $courseid = 0) {
        $this->courseid = $courseid;
    }

    /**
     * Return the effective value of a setting for this course.
     *
     * @param string $name Setting key.
     * @return string The effective value, as stored.
     * @throws coding_exception When the setting is not declared in the register.
     */
    public function get(string $name): string {
        $definition = self::definition($name);
        if ($definition === null) {
            throw new coding_exception('Unknown Learning Journey setting: ' . $name);
        }

        $merged = $this->load_merged();

        return (string) ($merged[$name] ?? $definition['default']);
    }

    /**
     * Return the effective value of a boolean setting.
     *
     * @param string $name Setting key.
     * @return bool The effective value.
     */
    public function get_bool(string $name): bool {
        return (bool) (int) $this->get($name);
    }

    /**
     * Return the effective value of an integer setting.
     *
     * @param string $name Setting key.
     * @return int The effective value.
     */
    public function get_int(string $name): int {
        return (int) $this->get($name);
    }

    /**
     * Return the effective value of a colour setting, guaranteed to be safe.
     *
     * @param string $name Setting key.
     * @return string A validated #rrggbb colour value.
     */
    public function get_colour(string $name): string {
        $value = $this->get($name);
        $definition = self::definition($name);

        return colour::normalise($value, (string) ($definition['default'] ?? '#000000'));
    }

    /**
     * Return the full merged settings map for this course.
     *
     * @return array<string, string> Setting keys mapped to effective values.
     */
    public function get_all(): array {
        $values = [];
        foreach (self::all_definitions() as $name => $definition) {
            $values[$name] = $this->get($name);
        }

        return $values;
    }

    /**
     * Return the course this resolver is bound to.
     *
     * @return int Course identifier, or 0 for site values only.
     */
    public function get_courseid(): int {
        return $this->courseid;
    }

    /**
     * Determine whether the plugin is enabled for a given course.
     *
     * @param int $courseid Course identifier.
     * @return bool True when both the site and the course permit the plugin.
     */
    public static function is_enabled_for_course(int $courseid): bool {
        $site = get_config(constants::PLUGIN, 'enabled');
        if ($site !== false && !(int) $site) {
            return false;
        }

        $resolver = new self($courseid);

        return $resolver->get_bool('enabled');
    }

    /**
     * Load the raw override rows recorded against a course.
     *
     * @param int $courseid Course identifier.
     * @return array<string, string> Override keys mapped to stored values.
     */
    public static function get_overrides(int $courseid): array {
        global $DB;

        if ($courseid <= 0) {
            return [];
        }

        $rows = $DB->get_records_menu(self::TABLE, ['courseid' => $courseid], '', 'name, value');

        return array_map(static fn($value): string => (string) $value, $rows);
    }

    /**
     * Persist the overrides submitted for a course.
     *
     * Only the settings the course genuinely changes are stored; every other
     * row is removed so the table stays sparse.
     *
     * @param int $courseid Course identifier.
     * @param array $values Submitted values keyed by setting name.
     * @param string[] $overridden Names of the settings the course overrides.
     * @return void
     */
    public static function save_overrides(int $courseid, array $values, array $overridden): void {
        global $DB;

        if ($courseid <= 0) {
            return;
        }

        $existing = $DB->get_records_menu(self::TABLE, ['courseid' => $courseid], '', 'name, id');
        $now = time();

        foreach (self::overridable_definitions() as $name => $definition) {
            $isoverridden = in_array($name, $overridden, true);
            $currentid = isset($existing[$name]) ? (int) $existing[$name] : null;

            if (!$isoverridden) {
                if ($currentid !== null) {
                    $DB->delete_records(self::TABLE, ['id' => $currentid]);
                }
                continue;
            }

            $value = (string) ($values[$name] ?? $definition['default']);

            if ($currentid !== null) {
                $DB->update_record(self::TABLE, (object) [
                    'id'           => $currentid,
                    'value'        => $value,
                    'timemodified' => $now,
                ]);
                continue;
            }

            $DB->insert_record(self::TABLE, (object) [
                'courseid'     => $courseid,
                'name'         => $name,
                'value'        => $value,
                'timemodified' => $now,
            ]);
        }

        self::purge($courseid);
    }

    /**
     * Purge the cached merged settings for a single course.
     *
     * @param int $courseid Course identifier.
     * @return void
     */
    public static function purge(int $courseid): void {
        self::cache()->delete($courseid);
    }

    /**
     * Remove every override recorded against a deleted course.
     *
     * @param int $courseid Course identifier.
     * @return void
     */
    public static function delete_course(int $courseid): void {
        global $DB;

        $DB->delete_records(self::TABLE, ['courseid' => $courseid]);
        self::purge($courseid);
    }

    /**
     * Return the declared metadata for a single setting.
     *
     * @param string $name Setting key.
     * @return array<string, mixed>|null The definition, or null when undeclared.
     */
    public static function definition(string $name): ?array {
        $definitions = self::all_definitions();

        return $definitions[$name] ?? null;
    }

    /**
     * Return every declared setting belonging to one administration page.
     *
     * @param string $page One of the PAGE_* constants.
     * @return array<string, array<string, mixed>> Definitions keyed by setting name.
     */
    public static function definitions_for_page(string $page): array {
        return array_filter(
            self::all_definitions(),
            static fn(array $definition): bool => $definition['page'] === $page
        );
    }

    /**
     * Return every setting that a course may override.
     *
     * @return array<string, array<string, mixed>> Definitions keyed by setting name.
     */
    public static function overridable_definitions(): array {
        return array_filter(
            self::all_definitions(),
            static fn(array $definition): bool => $definition['overridable'] === true
        );
    }

    /**
     * Validate an integer value against a declared range.
     *
     * Shared by the administration page and the course override form so the
     * two can never disagree about what is acceptable.
     *
     * @param string $value Submitted value.
     * @param int $minimum Lowest accepted value.
     * @param int $maximum Highest accepted value.
     * @return string|null A translated error message, or null when valid.
     */
    public static function validate_range(string $value, int $minimum, int $maximum): ?string {
        $trimmed = trim($value);

        if ($trimmed === '' || !preg_match('/^-?\\d+$/', $trimmed)) {
            return get_string('error_notinteger', constants::PLUGIN);
        }

        $number = (int) $trimmed;

        if ($number < $minimum || $number > $maximum) {
            return get_string('error_outofrange', constants::PLUGIN, (object) [
                'min' => $minimum,
                'max' => $maximum,
            ]);
        }

        return null;
    }

    /**
     * Return the complete Learning Journey settings register.
     *
     * @return array<string, array<string, mixed>> Definitions keyed by setting name.
     */
    public static function all_definitions(): array {
        static $definitions = null;

        if ($definitions !== null) {
            return $definitions;
        }

        $definitions = [
            'enabled' => self::def('bool', '1', self::PAGE_GENERAL),
            'layout' => self::def('select', 'standard', self::PAGE_GENERAL, [
                'options' => ['standard' => 'layout_standard', 'focused' => 'layout_focused'],
            ]),
            'autoredirect' => self::def('bool', '0', self::PAGE_GENERAL),
            'redirectdelay' => self::def('duration', '15', self::PAGE_GENERAL),
            'unitmode' => self::def('select', constants::UNIT_SECTION, self::PAGE_GENERAL, [
                'options' => [
                    constants::UNIT_SECTION  => 'unitmode_section',
                    constants::UNIT_ACTIVITY => 'unitmode_activity',
                    constants::UNIT_LESSON   => 'unitmode_lesson',
                ],
            ]),
            'usefallbackgradepass' => self::def('bool', '1', self::PAGE_GENERAL),
            'fallbackgradepass' => self::def(
                'int',
                (string) constants::DEFAULT_GRADEPASS_PERCENT,
                self::PAGE_GENERAL,
                ['min' => 0, 'max' => 100]
            ),

            'successtitle' => self::def('text', '', self::PAGE_MESSAGES),
            'successmessage' => self::def('html', '', self::PAGE_MESSAGES),
            'failtitle' => self::def('text', '', self::PAGE_MESSAGES),
            'failmessage' => self::def('html', '', self::PAGE_MESSAGES),
            'islamicsuccess' => self::def('html', '', self::PAGE_MESSAGES),
            'islamicencouragement' => self::def('html', '', self::PAGE_MESSAGES),
            'coursecompletemessage' => self::def('html', '', self::PAGE_MESSAGES),
            'studyadvice' => self::def('html', '', self::PAGE_MESSAGES),
            'continuelabel' => self::def('text', '', self::PAGE_MESSAGES),
            'retrylabel' => self::def('text', '', self::PAGE_MESSAGES),

            'themecolour' => self::def('colour', '#1d6f42', self::PAGE_APPEARANCE),
            'buttoncolour' => self::def('colour', '#1d6f42', self::PAGE_APPEARANCE),
            'buttontextcolour' => self::def('colour', '#ffffff', self::PAGE_APPEARANCE),
            'progressbarcolour' => self::def('colour', '#1d6f42', self::PAGE_APPEARANCE),
            'progressbgcolour' => self::def('colour', '#e9ecef', self::PAGE_APPEARANCE),
            'backgroundcolour' => self::def('colour', '#ffffff', self::PAGE_APPEARANCE),
            'backgroundimage' => self::def('file', '', self::PAGE_APPEARANCE, [
                'filearea' => constants::FILEAREA_BACKGROUND,
                'accepted' => ['.jpg', '.jpeg', '.png', '.webp', '.svg'],
                'maxbytes' => 2097152,
            ]),
            'iconset' => self::def('select', 'fontawesome', self::PAGE_APPEARANCE, [
                'options' => [
                    'emoji'       => 'iconset_emoji',
                    'fontawesome' => 'iconset_fontawesome',
                    'none'        => 'iconset_none',
                ],
            ]),

            'effectconfetti' => self::def('bool', '1', self::PAGE_EFFECTS),
            'effectstars' => self::def('bool', '1', self::PAGE_EFFECTS),
            'effecttrophy' => self::def('bool', '0', self::PAGE_EFFECTS),
            'effectfireworks' => self::def('bool', '0', self::PAGE_EFFECTS),
            'effectbadge' => self::def('bool', '1', self::PAGE_EFFECTS),
            'effectsound' => self::def('bool', '0', self::PAGE_EFFECTS),
            'soundfile' => self::def('file', '', self::PAGE_EFFECTS, [
                'filearea' => constants::FILEAREA_SOUND,
                'accepted' => ['.mp3', '.ogg'],
                'maxbytes' => 1048576,
            ]),

            'showscore' => self::def('bool', '1', self::PAGE_DISPLAY),
            'showpercentage' => self::def('bool', '1', self::PAGE_DISPLAY),
            'showgradepass' => self::def('bool', '1', self::PAGE_DISPLAY),
            'showstatus' => self::def('bool', '1', self::PAGE_DISPLAY),
            'showtimetaken' => self::def('bool', '1', self::PAGE_DISPLAY),
            'showattempt' => self::def('bool', '1', self::PAGE_DISPLAY),
            'showstars' => self::def('bool', '1', self::PAGE_DISPLAY),
            'showprogress' => self::def('bool', '1', self::PAGE_DISPLAY),
            'showcoursecompletion' => self::def('bool', '1', self::PAGE_DISPLAY),
            'showbadges' => self::def('bool', '1', self::PAGE_DISPLAY),
            'showreviewlink' => self::def('bool', '1', self::PAGE_DISPLAY),
            'starthresholds' => self::def('stars', '60,70,80,90,95', self::PAGE_DISPLAY),
            'manualbadgeid' => self::def('int', '0', self::PAGE_DISPLAY, ['min' => 0]),
        ];

        return $definitions;
    }

    /**
     * Build one entry of the settings register.
     *
     * @param string $type Storage and widget type.
     * @param string $default Site default value.
     * @param string $page Administration page the setting belongs to.
     * @param array $extra Additional type specific metadata.
     * @return array<string, mixed> A complete definition.
     */
    protected static function def(string $type, string $default, string $page, array $extra = []): array {
        return $extra + [
            'type'        => $type,
            'default'     => $default,
            'page'        => $page,
            'overridable' => true,
        ];
    }

    /**
     * Return the site level values of every declared setting.
     *
     * @return array<string, string> Setting keys mapped to site values.
     */
    protected static function site_defaults(): array {
        $config = (array) get_config(constants::PLUGIN);
        $values = [];
        foreach (self::all_definitions() as $name => $definition) {
            $values[$name] = isset($config[$name]) && $config[$name] !== null
                ? (string) $config[$name]
                : (string) $definition['default'];
        }

        return $values;
    }

    /**
     * Resolve and cache the merged site and course settings map.
     *
     * @return array<string, string> Setting keys mapped to effective values.
     */
    protected function load_merged(): array {
        if ($this->merged !== null) {
            return $this->merged;
        }

        if ($this->courseid <= 0) {
            $this->merged = self::site_defaults();

            return $this->merged;
        }

        $cache = self::cache();
        $cached = $cache->get($this->courseid);
        if (is_array($cached)) {
            $this->merged = $cached;

            return $this->merged;
        }

        $merged = array_merge(self::site_defaults(), self::get_overrides($this->courseid));
        $cache->set($this->courseid, $merged);
        $this->merged = $merged;

        return $this->merged;
    }

    /**
     * Obtain the application cache holding merged course settings.
     *
     * @return cache_application The plugin course settings cache.
     */
    protected static function cache(): cache_application {
        return cache::make(constants::PLUGIN, constants::CACHE_COURSESETTINGS);
    }
}
