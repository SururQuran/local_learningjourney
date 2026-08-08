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

namespace local_learningjourney\tests;

use mod_quiz\quiz_attempt;
use mod_quiz\quiz_settings;
use question_engine;
use stdClass;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

/**
 * Shared fixtures for the Learning Journey test suite.
 *
 * Builds real courses, quizzes and finished attempts through the core
 * generators so the tests exercise Moodle behaviour rather than mocks.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class journey_testcase extends \advanced_testcase {
    /** @var stdClass The course used by the fixture. */
    protected stdClass $course;

    /** @var stdClass The learner used by the fixture. */
    protected stdClass $learner;

    /**
     * Create a course and an enrolled learner.
     *
     * @param array $courseoptions Extra course generator options.
     * @return void
     */
    protected function setup_course(array $courseoptions = []): void {
        $this->course = $this->getDataGenerator()->create_course(
            $courseoptions + ['numsections' => 3, 'enablecompletion' => 1]
        );
        $this->learner = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($this->learner->id, $this->course->id, 'student');
    }

    /**
     * Create a quiz containing a single true or false question.
     *
     * @param array $options Quiz generator options.
     * @return stdClass The quiz record.
     */
    protected function create_quiz(array $options = []): stdClass {
        $quiz = $this->getDataGenerator()->get_plugin_generator('mod_quiz')->create_instance(
            $options + [
                'course'      => $this->course->id,
                'grade'       => 100.0,
                'sumgrades'   => 1,
                'questionsperpage' => 0,
            ]
        );

        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $category = $questiongenerator->create_question_category();
        $question = $questiongenerator->create_question('truefalse', null, ['category' => $category->id]);
        quiz_add_quiz_question($question->id, $quiz);

        return $quiz;
    }

    /**
     * Create a quiz containing several true or false questions.
     *
     * @param int $count Number of questions to add.
     * @param array $options Quiz generator options.
     * @return stdClass The quiz record.
     */
    protected function create_multi_question_quiz(int $count, array $options = []): stdClass {
        global $DB;

        $quiz = $this->getDataGenerator()->get_plugin_generator('mod_quiz')->create_instance(
            $options + [
                'course'           => $this->course->id,
                'grade'            => 100.0,
                'questionsperpage' => 0,
            ]
        );

        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $category = $questiongenerator->create_question_category();

        for ($index = 0; $index < $count; $index++) {
            $question = $questiongenerator->create_question('truefalse', null, ['category' => $category->id]);
            quiz_add_quiz_question($question->id, $quiz);
        }

        quiz_settings::create($quiz->id)->get_grade_calculator()->recompute_quiz_sumgrades();

        return $DB->get_record('quiz', ['id' => $quiz->id], '*', MUST_EXIST);
    }

    /**
     * Submit an attempt answering a given number of questions correctly.
     *
     * @param stdClass $quiz The quiz record.
     * @param int $correct How many questions to answer correctly.
     * @param int $total How many questions the quiz contains.
     * @param int $attemptnumber Ordinal number of the attempt.
     * @return int The identifier of the finished attempt.
     */
    protected function submit_partial_attempt(
        stdClass $quiz,
        int $correct,
        int $total,
        int $attemptnumber = 1
    ): int {
        $responses = [];
        for ($slot = 1; $slot <= $total; $slot++) {
            $responses[$slot] = ['answer' => $slot <= $correct ? 1 : 0];
        }

        return $this->submit_responses($quiz, $responses, $attemptnumber);
    }

    /**
     * Set the pass grade recorded in the gradebook for a quiz.
     *
     * @param stdClass $quiz The quiz record.
     * @param float $gradepass The pass grade on the quiz grade scale.
     * @return void
     */
    protected function set_quiz_gradepass(stdClass $quiz, float $gradepass): void {
        $item = \grade_item::fetch([
            'courseid'     => (int) $this->course->id,
            'itemtype'     => 'mod',
            'itemmodule'   => 'quiz',
            'iteminstance' => (int) $quiz->id,
            'itemnumber'   => 0,
        ]);
        $item->gradepass = $gradepass;
        $item->update();
    }

    /**
     * Submit and finish an attempt at a quiz.
     *
     * @param stdClass $quiz The quiz record.
     * @param bool $correct Whether the learner answers correctly.
     * @param int $attemptnumber Ordinal number of the attempt.
     * @return int The identifier of the finished attempt.
     */
    protected function submit_attempt(stdClass $quiz, bool $correct, int $attemptnumber = 1): int {
        return $this->submit_responses($quiz, [1 => ['answer' => $correct ? 1 : 0]], $attemptnumber);
    }

    /**
     * Submit and finish an attempt using explicit responses.
     *
     * @param stdClass $quiz The quiz record.
     * @param array $responses Responses keyed by slot.
     * @param int $attemptnumber Ordinal number of the attempt.
     * @return int The identifier of the finished attempt.
     */
    protected function submit_responses(stdClass $quiz, array $responses, int $attemptnumber = 1): int {
        $quizobj = quiz_settings::create($quiz->id, $this->learner->id);
        $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);

        $timenow = time() - 60;
        $attempt = quiz_create_attempt($quizobj, $attemptnumber, false, $timenow, false, $this->learner->id);
        quiz_start_new_attempt($quizobj, $quba, $attempt, $attemptnumber, $timenow);
        quiz_attempt_save_started($quizobj, $quba, $attempt);

        $attemptobj = quiz_attempt::create($attempt->id);
        $attemptobj->process_submitted_actions($timenow, false, $responses);
        $attemptobj->process_finish(time(), false);

        return (int) $attempt->id;
    }
}
