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

namespace local_learningjourney\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use local_learningjourney\local\permission;
use local_learningjourney\local\quiz_adapter;
use local_learningjourney\local\result_builder;

/**
 * Read only external function returning a Learning Journey result payload.
 *
 * The function shares the authorisation helper used by the web page, so the
 * access rule cannot drift between the two surfaces.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_result extends external_api {
    /**
     * Describe the parameters accepted by this function.
     *
     * @return external_function_parameters The parameter description.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'attemptid' => new external_value(PARAM_INT, 'Quiz attempt identifier'),
        ]);
    }

    /**
     * Return the result payload for a quiz attempt.
     *
     * @param int $attemptid Quiz attempt identifier.
     * @return array<string, mixed> The result payload.
     */
    public static function execute(int $attemptid): array {
        $params = self::validate_parameters(self::execute_parameters(), ['attemptid' => $attemptid]);

        $quiz = quiz_adapter::create($params['attemptid']);
        $context = $quiz->get_context();
        self::validate_context($context);
        permission::require_can_view_result($context, $quiz->get_userid());

        $result = (new result_builder($quiz, $quiz->get_userid()))->build();

        $badges = [];
        foreach ($result->badges as $badge) {
            $badges[] = [
                'id'       => $badge->id,
                'name'     => $badge->name,
                'imageurl' => $badge->imageurl === null ? '' : $badge->imageurl->out(false),
                'isreal'   => $badge->isreal,
            ];
        }

        return [
            'verdict'           => $result->verdict,
            'attemptid'         => $result->attempt->attemptid,
            'attemptnumber'     => $result->attempt->attemptnumber,
            'attemptsremaining' => $result->attempt->attemptsremaining,
            'timetaken'         => $result->attempt->timetaken,
            'canretry'          => $result->attempt->canretry,
            'courseid'          => (int) $result->course->id,
            'cmid'              => (int) $result->cm->id,
            'title'             => $result->messages->title,
            'message'           => $result->messages->body,
            'progressline'      => $result->messages->progressline,
            'stars'             => $result->stars,
            'percent'           => $result->grade->percent,
            'gradepass'         => $result->grade->gradepasspercent,
            'progressavailable' => $result->progress->available,
            'coursepercent'     => $result->progress->coursepercent,
            'unitindex'         => $result->progress->unitindex,
            'unittotal'         => $result->progress->unittotal,
            'nexttype'          => $result->nextstep->type,
            'nextcmid'          => $result->nextstep->cmid,
            'nextname'          => $result->nextstep->name,
            'badges'            => $badges,
        ];
    }

    /**
     * Describe the structure returned by this function.
     *
     * @return external_single_structure The return description.
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'verdict'           => new external_value(PARAM_ALPHA, 'Pass, fail, pending or nomark'),
            'attemptid'         => new external_value(PARAM_INT, 'Quiz attempt identifier'),
            'attemptnumber'     => new external_value(PARAM_INT, 'Ordinal number of the attempt'),
            'attemptsremaining' => new external_value(
                PARAM_INT,
                'Attempts still available, null when unlimited',
                VALUE_OPTIONAL,
                null,
                NULL_ALLOWED
            ),
            'timetaken'         => new external_value(PARAM_INT, 'Seconds spent on the attempt'),
            'canretry'          => new external_value(PARAM_BOOL, 'Whether a further attempt is permitted'),
            'courseid'          => new external_value(PARAM_INT, 'Course identifier'),
            'cmid'              => new external_value(PARAM_INT, 'Quiz course module identifier'),
            'title'             => new external_value(PARAM_RAW, 'Resolved page heading'),
            'message'           => new external_value(PARAM_RAW, 'Resolved page message'),
            'progressline'      => new external_value(PARAM_RAW, 'Sentence describing remaining work'),
            'stars'             => new external_value(PARAM_INT, 'Star rating between 0 and 5'),
            'percent'           => new external_value(
                PARAM_FLOAT,
                'Percentage achieved',
                VALUE_OPTIONAL,
                null,
                NULL_ALLOWED
            ),
            'gradepass'         => new external_value(
                PARAM_FLOAT,
                'Applied pass mark percentage',
                VALUE_OPTIONAL,
                null,
                NULL_ALLOWED
            ),
            'progressavailable' => new external_value(PARAM_BOOL, 'Whether progress could be calculated'),
            'coursepercent'     => new external_value(
                PARAM_FLOAT,
                'Overall course completion percentage',
                VALUE_OPTIONAL,
                null,
                NULL_ALLOWED
            ),
            'unitindex'         => new external_value(PARAM_INT, 'Position of the current unit'),
            'unittotal'         => new external_value(PARAM_INT, 'Total number of units'),
            'nexttype'          => new external_value(PARAM_ALPHAEXT, 'Type of the learner next step'),
            'nextcmid'          => new external_value(
                PARAM_INT,
                'Course module of the next activity',
                VALUE_OPTIONAL,
                null,
                NULL_ALLOWED
            ),
            'nextname'          => new external_value(PARAM_RAW, 'Name of the next activity'),
            'badges'            => new \core_external\external_multiple_structure(
                new external_single_structure([
                    'id'       => new external_value(PARAM_INT, 'Badge identifier'),
                    'name'     => new external_value(PARAM_RAW, 'Badge name'),
                    'imageurl' => new external_value(PARAM_URL, 'Badge image URL'),
                    'isreal'   => new external_value(PARAM_BOOL, 'Whether the badge was issued by Moodle'),
                ]),
                'Badges earned by the learner in this course'
            ),
        ]);
    }
}
