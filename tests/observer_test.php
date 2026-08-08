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

use local_learningjourney\local\constants;
use local_learningjourney\local\handoff;
use local_learningjourney\local\settings_resolver;

/**
 * Tests for the quiz submission observer and the handoff token.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_learningjourney\event\observer\attempt_submitted
 * @covers     \local_learningjourney\local\handoff
 */
final class observer_test extends \local_learningjourney\tests\journey_testcase {
    /**
     * The handoff token round trips and is consumed exactly once.
     *
     * @return void
     */
    public function test_token_is_consumed_once(): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user());

        handoff::store(11, 22, 33);

        $peeked = handoff::peek();
        $this->assertNotNull($peeked);
        $this->assertSame(11, (int) $peeked->attemptid);
        $this->assertSame(22, (int) $peeked->cmid);
        $this->assertSame(33, (int) $peeked->courseid);
        $this->assertTrue(handoff::is_fresh($peeked));

        $this->assertNotNull(handoff::consume());
        $this->assertNull(handoff::peek(), 'A consumed token must not be reusable.');
    }

    /**
     * A token older than its lifetime is not treated as fresh.
     *
     * @return void
     */
    public function test_stale_token_is_rejected(): void {
        $this->resetAfterTest();

        $token = (object) ['attemptid' => 1, 'cmid' => 2, 'courseid' => 3];
        $token->timecreated = time() - constants::HANDOFF_TTL - 5;

        $this->assertFalse(handoff::is_fresh($token));
    }

    /**
     * Submitting an attempt performs no plugin database writes.
     *
     * @return void
     */
    public function test_observer_performs_no_writes(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setup_course();

        $quiz = $this->create_quiz();

        $before = $DB->count_records(settings_resolver::TABLE);
        $this->submit_attempt($quiz, true);

        $this->assertSame(
            $before,
            $DB->count_records(settings_resolver::TABLE),
            'The observer must never write to the plugin table.'
        );
    }

    /**
     * The course deletion observer clears that course's overrides.
     *
     * Moodle holds back non internal observers while a database transaction is
     * open, and every PHPUnit test runs inside one, so the handler is invoked
     * directly here rather than through delete_course().
     *
     * @return void
     */
    public function test_course_deletion_observer(): void {
        global $DB;

        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $other = $this->getDataGenerator()->create_course();

        settings_resolver::save_overrides((int) $course->id, ['successtitle' => 'Bye'], ['successtitle']);
        settings_resolver::save_overrides((int) $other->id, ['successtitle' => 'Stay'], ['successtitle']);

        $event = \core\event\course_deleted::create([
            'objectid' => (int) $course->id,
            'context'  => \context_course::instance((int) $course->id),
            'other'    => [
                'shortname' => $course->shortname,
                'fullname'  => $course->fullname,
                'idnumber'  => $course->idnumber,
            ],
        ]);
        $event->add_record_snapshot('course', $course);

        \local_learningjourney\event\observer\course_deleted::handle($event);

        $this->assertSame(0, $DB->count_records(settings_resolver::TABLE, ['courseid' => $course->id]));
        $this->assertSame(
            1,
            $DB->count_records(settings_resolver::TABLE, ['courseid' => $other->id]),
            'Another course must keep its overrides.'
        );
    }
}
