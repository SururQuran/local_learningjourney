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

/**
 * Moodle Mobile App addon declaration for the Learning Journey plugin.
 *
 * The official app submits quiz attempts through web services and renders its
 * own interface, so the web diversion cannot apply there. This addon exposes
 * the learner's journey as an on-demand course option instead.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$addons = [
    'local_learningjourney' => [
        'handlers' => [
            'learningjourney' => [
                'delegate'    => 'CoreCourseOptionsDelegate',
                'method'      => 'course_view',
                'displaydata' => [
                    'title' => 'mobile_viewprogress',
                    'class' => '',
                    'icon'  => 'trophy',
                ],
            ],
        ],
        'lang' => [
            ['pluginname', 'local_learningjourney'],
            ['mobile_viewprogress', 'local_learningjourney'],
            ['mobile_nodata', 'local_learningjourney'],
        ],
    ],
];
