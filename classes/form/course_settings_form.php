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

namespace local_learningjourney\form;

use local_learningjourney\local\colour;
use local_learningjourney\local\constants;
use local_learningjourney\local\settings_resolver;
use local_learningjourney\local\star_rating;
use moodleform;

/**
 * Course level override form for the Learning Journey plugin.
 *
 * Every overridable setting is rendered as a pair: a "use site default"
 * checkbox and the value element it controls, so a course stores only the
 * settings it genuinely changes.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_settings_form extends moodleform {
    /** @var string Prefix applied to the "use site default" checkbox names. */
    public const OVERRIDE_PREFIX = 'override_';

    /**
     * Build the form.
     *
     * @return void
     */
    protected function definition(): void {
        $mform = $this->_form;
        $courseid = (int) ($this->_customdata['courseid'] ?? 0);

        $mform->addElement('hidden', 'id', $courseid);
        $mform->setType('id', PARAM_INT);

        $mform->addElement(
            'static',
            'overrideintro',
            '',
            get_string('coursesettings_help', 'local_learningjourney')
        );

        $lastpage = '';
        foreach (settings_resolver::overridable_definitions() as $name => $definition) {
            if ($definition['page'] !== $lastpage) {
                $lastpage = $definition['page'];
                $mform->addElement(
                    'header',
                    'header_' . $lastpage,
                    get_string('settingspage_' . $lastpage, 'local_learningjourney')
                );
                $mform->setExpanded('header_' . $lastpage, $lastpage === settings_resolver::PAGE_GENERAL);
            }
            $this->add_override_pair($name, $definition);
        }

        $this->add_action_buttons();
    }

    /**
     * Add one override checkbox and its associated value element.
     *
     * @param string $name Setting key.
     * @param array $definition Declared metadata for the setting.
     * @return void
     */
    protected function add_override_pair(string $name, array $definition): void {
        $mform = $this->_form;
        $label = get_string('setting_' . $name, 'local_learningjourney');
        $overridename = self::OVERRIDE_PREFIX . $name;

        $mform->addElement(
            'advcheckbox',
            $overridename,
            $label,
            get_string('usesitedefault', 'local_learningjourney')
        );
        $mform->setDefault($overridename, 1);

        switch ($definition['type']) {
            case 'bool':
                $mform->addElement('advcheckbox', $name, '', $label);
                $mform->setType($name, PARAM_BOOL);
                break;
            case 'int':
            case 'duration':
                $mform->addElement('text', $name, '', ['size' => 8]);
                $mform->setType($name, PARAM_INT);
                break;
            case 'colour':
                $mform->addElement('text', $name, '', ['size' => 10]);
                $mform->setType($name, PARAM_TEXT);
                break;
            case 'select':
                $options = [];
                foreach ((array) ($definition['options'] ?? []) as $value => $stringkey) {
                    $options[$value] = get_string($stringkey, 'local_learningjourney');
                }
                $mform->addElement('select', $name, '', $options);
                $mform->setType($name, PARAM_ALPHANUMEXT);
                break;
            case 'html':
                $mform->addElement('editor', $name, '', ['rows' => 6]);
                $mform->setType($name, PARAM_RAW);
                break;
            case 'file':
                $mform->addElement('filemanager', $name, '', null, local_learningjourney_file_options($definition));
                break;
            case 'stars':
            case 'text':
            default:
                $mform->addElement('text', $name, '', ['size' => 48]);
                $mform->setType($name, PARAM_TEXT);
                break;
        }

        $mform->disabledIf($name, $overridename, 'checked');
    }

    /**
     * Validate the submitted overrides.
     *
     * @param array $data Submitted data.
     * @param array $files Submitted files.
     * @return array<string, string> Validation errors keyed by element name.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);

        foreach (settings_resolver::overridable_definitions() as $name => $definition) {
            if (!empty($data[self::OVERRIDE_PREFIX . $name])) {
                continue;
            }

            $value = is_array($data[$name] ?? null)
                ? (string) ($data[$name]['text'] ?? '')
                : (string) ($data[$name] ?? '');

            $error = $this->validate_value($definition, $value);
            if ($error !== null) {
                $errors[$name] = $error;
            }
        }

        return $errors;
    }

    /**
     * Validate one submitted override against its declared type.
     *
     * @param array $definition Declared metadata for the setting.
     * @param string $value Submitted value.
     * @return string|null A translated error message, or null when valid.
     */
    protected function validate_value(array $definition, string $value): ?string {
        switch ($definition['type']) {
            case 'colour':
                return colour::is_valid($value)
                    ? null
                    : get_string('error_invalidcolour', 'local_learningjourney');

            case 'int':
                return settings_resolver::validate_range(
                    $value,
                    (int) ($definition['min'] ?? 0),
                    (int) ($definition['max'] ?? PHP_INT_MAX)
                );

            case 'duration':
                return (int) $value >= constants::MIN_REDIRECT_DELAY
                    ? null
                    : get_string(
                        'error_redirectdelay',
                        'local_learningjourney',
                        constants::MIN_REDIRECT_DELAY
                    );

            case 'stars':
                return $this->validate_thresholds($value);

            default:
                return null;
        }
    }

    /**
     * Validate a submitted star threshold list.
     *
     * @param string $value Submitted comma separated thresholds.
     * @return string|null A translated error message, or null when valid.
     */
    protected function validate_thresholds(string $value): ?string {
        $parts = array_map('trim', explode(',', $value));

        if (count($parts) !== star_rating::MAX_STARS) {
            return get_string('error_thresholdcount', 'local_learningjourney', star_rating::MAX_STARS);
        }

        $previous = -1;
        foreach ($parts as $part) {
            if (!preg_match('/^\d{1,3}$/', $part)) {
                return get_string('error_thresholdnumeric', 'local_learningjourney');
            }

            $number = (int) $part;
            if ($number > 100 || $number <= $previous) {
                return get_string('error_thresholdorder', 'local_learningjourney');
            }

            $previous = $number;
        }

        return null;
    }

    /**
     * Split submitted data into values and the list of overridden settings.
     *
     * @return array{values: array<string, string>, overridden: string[]} Submitted overrides.
     */
    public function get_submitted_overrides(): array {
        $data = (array) $this->get_data();
        $values = [];
        $overridden = [];

        foreach (settings_resolver::overridable_definitions() as $name => $definition) {
            unset($definition);
            if (!empty($data[self::OVERRIDE_PREFIX . $name])) {
                continue;
            }
            $overridden[] = $name;
            $values[$name] = is_array($data[$name] ?? null)
                ? (string) ($data[$name]['text'] ?? '')
                : (string) ($data[$name] ?? '');
        }

        return ['values' => $values, 'overridden' => $overridden];
    }
}
