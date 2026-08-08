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

use local_learningjourney\local\permission;
use local_learningjourney\local\quiz_adapter;

/**
 * Tests for the authorisation rules protecting a learner's result.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_learningjourney\local\permission
 */
final class permission_test extends \local_learningjourney\tests\journey_testcase {
    /**
     * A learner may open their own result.
     *
     * @return void
     */
    public function test_owner_may_view_own_result(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $quiz = $this->create_quiz();
        $adapter = quiz_adapter::create($this->submit_attempt($quiz, true));

        $this->setUser($this->learner);

        $this->assertTrue(permission::can_view_result($adapter->get_context(), $adapter->get_userid()));
    }

    /**
     * Another learner may not open somebody else's result.
     *
     * @return void
     */
    public function test_other_learner_is_denied(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $quiz = $this->create_quiz();
        $adapter = quiz_adapter::create($this->submit_attempt($quiz, true));

        $other = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($other->id, $this->course->id, 'student');
        $this->setUser($other);

        $this->assertFalse(permission::can_view_result($adapter->get_context(), $adapter->get_userid()));

        $this->expectException(\required_capability_exception::class);
        permission::require_can_view_result($adapter->get_context(), $adapter->get_userid());
    }

    /**
     * A teacher holding the capability may open a learner's result.
     *
     * @return void
     */
    public function test_teacher_with_capability_is_allowed(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $quiz = $this->create_quiz();
        $adapter = quiz_adapter::create($this->submit_attempt($quiz, true));

        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $this->course->id, 'editingteacher');
        $this->setUser($teacher);

        $this->assertTrue(permission::can_view_result($adapter->get_context(), $adapter->get_userid()));
    }

    /**
     * An unknown attempt identifier produces a clean, localised failure.
     *
     * @return void
     */
    public function test_missing_attempt_is_rejected(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $this->expectException(\moodle_exception::class);
        quiz_adapter::create(-1);
    }

    /**
     * Course override editing requires the manage capability.
     *
     * @return void
     */
    public function test_course_management_requires_capability(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $context = \context_course::instance($this->course->id);

        $this->setUser($this->learner);
        $this->assertFalse(permission::can_manage_course($context));

        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $this->course->id, 'editingteacher');
        $this->setUser($teacher);
        $this->assertTrue(permission::can_manage_course($context));
    }
}
