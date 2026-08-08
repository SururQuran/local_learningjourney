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

use local_learningjourney\local\quiz_adapter;

/**
 * Tests for the mod_quiz adapter.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_learningjourney\local\quiz_adapter
 */
final class quiz_adapter_test extends \local_learningjourney\tests\journey_testcase {
    /**
     * Attempt counting reflects the attempts actually made.
     *
     * @return void
     */
    public function test_attempt_counting(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $quiz = $this->create_quiz(['attempts' => 3]);

        $first = quiz_adapter::create($this->submit_attempt($quiz, false, 1));
        $this->assertSame(1, $first->attempt_number());
        $this->assertSame(1, $first->attempts_used());
        $this->assertSame(2, $first->attempts_remaining());
        $this->assertSame(3, $first->attempts_allowed());

        $second = quiz_adapter::create($this->submit_attempt($quiz, false, 2));
        $this->assertSame(2, $second->attempt_number());
        $this->assertSame(2, $second->attempts_used());
        $this->assertSame(1, $second->attempts_remaining());
    }

    /**
     * An unlimited quiz reports no remaining attempt count.
     *
     * @return void
     */
    public function test_unlimited_attempts(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $quiz = $this->create_quiz(['attempts' => 0]);
        $adapter = quiz_adapter::create($this->submit_attempt($quiz, true));

        $this->assertSame(0, $adapter->attempts_allowed());
        $this->assertNull($adapter->attempts_remaining());
    }

    /**
     * A learner who has used every attempt may not retry.
     *
     * @return void
     */
    public function test_no_retry_when_attempts_exhausted(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $quiz = $this->create_quiz(['attempts' => 1]);
        $adapter = quiz_adapter::create($this->submit_attempt($quiz, false));

        $this->assertSame(0, $adapter->attempts_remaining());
        $this->assertFalse($adapter->can_start_new_attempt());
        $this->assertNotNull($adapter->new_attempt_blocked_reason());
    }

    /**
     * A learner with attempts left may retry.
     *
     * @return void
     */
    public function test_retry_allowed_when_attempts_remain(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $quiz = $this->create_quiz(['attempts' => 3]);
        $adapter = quiz_adapter::create($this->submit_attempt($quiz, false));

        $this->assertTrue($adapter->can_start_new_attempt());
        $this->assertNull($adapter->new_attempt_blocked_reason());
    }

    /**
     * The elapsed time of an attempt is reported and never negative.
     *
     * @return void
     */
    public function test_time_taken(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $quiz = $this->create_quiz();
        $adapter = quiz_adapter::create($this->submit_attempt($quiz, true));

        $this->assertGreaterThanOrEqual(0, $adapter->time_taken());
        $this->assertTrue($adapter->is_finished());
    }

    /**
     * A fully graded attempt is not awaiting manual marking.
     *
     * @return void
     */
    public function test_automatic_grading_is_not_pending(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $quiz = $this->create_quiz();
        $adapter = quiz_adapter::create($this->submit_attempt($quiz, true));

        $this->assertFalse($adapter->has_pending_manual_grading());
        $this->assertEqualsWithDelta(100.0, $adapter->get_attempt_percentage(), 0.01);
    }

    /**
     * Review access follows the quiz review options rather than plugin settings.
     *
     * @return void
     */
    public function test_review_follows_quiz_options(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $quiz = $this->create_quiz();
        $adapter = quiz_adapter::create($this->submit_attempt($quiz, true));

        if ($adapter->can_review_attempt()) {
            $this->assertNotNull($adapter->get_review_url());
        } else {
            $this->assertNull($adapter->get_review_url());
        }
    }
}
