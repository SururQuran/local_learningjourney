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

namespace local_learningjourney\output;

use context_course;
use local_learningjourney\local\constants;
use local_learningjourney\local\progress_calculator;
use local_learningjourney\local\settings_resolver;

/**
 * Moodle Mobile App views for the Learning Journey plugin.
 *
 * The app submits quiz attempts over web services and renders its own
 * interface, so the journey is offered as an on demand course option rather
 * than as an interception of the submission flow.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mobile {
    /**
     * Return the course level Learning Journey view for the app.
     *
     * @param array $args Arguments supplied by the app.
     * @return array<string, mixed> App template descriptor.
     */
    public static function course_view(array $args): array {
        global $OUTPUT, $USER;

        $courseid = (int) ($args['courseid'] ?? 0);
        $userid = (int) ($args['userid'] ?? $USER->id);

        $course = get_course($courseid);
        require_login($course, false);
        require_capability('moodle/course:view', context_course::instance($courseid));

        $settings = new settings_resolver($courseid);
        $progress = (new progress_calculator($course, $userid, $settings))->calculate();

        $percent = 0;
        if ($progress->coursepercent !== null) {
            $percent = (int) round($progress->coursepercent);
        } else if ($progress->unittotal > 0) {
            $percent = (int) round(($progress->unitscompleted / $progress->unittotal) * 100);
        }
        $percent = max(0, min(100, $percent));

        $unitlabel = '';
        if ($progress->unittotal > 0) {
            $unitlabel = get_string('unitlabel_completed', constants::PLUGIN, (object) [
                'completed' => $progress->unitscompleted,
                'total'     => $progress->unittotal,
            ]);
        }

        $progressline = '';
        if ($progress->available) {
            $remaining = max(0, $progress->unittotal - $progress->unitscompleted);
            $progressline = $remaining === 0
                ? get_string('progress_allcomplete', constants::PLUGIN)
                : get_string('progress_line', constants::PLUGIN, (object) [
                    'completed' => $progress->unitscompleted,
                    'total'     => $progress->unittotal,
                    'remaining' => $remaining,
                ]);
        }

        return [
            'templates' => [
                [
                    'id'   => 'main',
                    'html' => $OUTPUT->render_from_template('local_learningjourney/mobile_result', [
                        'hasdata'      => $progress->available,
                        'nodata'       => get_string('mobile_nodata', constants::PLUGIN),
                        'title'        => get_string('pluginname', constants::PLUGIN),
                        'unitlabel'    => $unitlabel,
                        'hasunitlabel' => $unitlabel !== '',
                        'progressline' => $progressline,
                        'percent'      => $percent,
                        'percentlabel' => get_string('label_percentcomplete', constants::PLUGIN, $percent),
                    ]),
                ],
            ],
            'javascript' => '',
            'otherdata'  => [],
        ];
    }
}
