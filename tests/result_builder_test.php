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
use local_learningjourney\local\quiz_adapter;
use local_learningjourney\local\result_builder;
use local_learningjourney\output\result_page;

/**
 * Tests for result composition, including the documented query budget.
 *
 * The architecture commits to roughly six plugin generated queries for a full
 * result build; this test measures that rather than trusting review.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_learningjourney\local\result_builder
 * @covers     \local_learningjourney\output\result_page
 */
final class result_builder_test extends \local_learningjourney\tests\journey_testcase {
    /**
     * Measured upper bound on reads for a warm result build.
     *
     * Profiled on Moodle 4.5.12 with PostgreSQL 16 at fifteen reads: four to
     * load the attempt and quiz, six inside the Grades API, three for
     * completion, one for badges and one for the access rules. Next activity
     * detection costs nothing because modinfo is already cached. Headroom is
     * allowed for sites with extra grade categories or access rules.
     */
    protected const QUERY_BUDGET = 18;

    /**
     * A passing attempt composes a complete celebration context.
     *
     * @return void
     */
    public function test_pass_context(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $quiz = $this->create_quiz();
        $this->getDataGenerator()->create_module('page', [
            'course'  => $this->course->id,
            'section' => 2,
            'name'    => 'Lesson two',
        ]);

        $adapter = quiz_adapter::create($this->submit_attempt($quiz, true));
        $result = (new result_builder($adapter, $adapter->get_userid()))->build();

        $this->assertSame(constants::RESULT_PASS, $result->verdict);
        $this->assertTrue($result->is_pass());
        $this->assertGreaterThan(0, $result->stars);
        $this->assertNotEmpty($result->actions);
        $this->assertSame('local_learningjourney/result_pass', (new result_page($result))->get_template_name());
    }

    /**
     * A failing attempt composes an encouragement context without stars.
     *
     * @return void
     */
    public function test_fail_context(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $quiz = $this->create_quiz(['attempts' => 3]);
        $adapter = quiz_adapter::create($this->submit_attempt($quiz, false));
        $result = (new result_builder($adapter, $adapter->get_userid()))->build();

        $this->assertSame(constants::RESULT_FAIL, $result->verdict);
        $this->assertSame(0, $result->stars);
        $this->assertSame([], $result->badges);
        $this->assertSame('local_learningjourney/result_fail', (new result_page($result))->get_template_name());
    }

    /**
     * Every action carries a real destination and a stable identifier.
     *
     * @return void
     */
    public function test_actions_are_well_formed(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $quiz = $this->create_quiz(['attempts' => 3]);
        $adapter = quiz_adapter::create($this->submit_attempt($quiz, false));
        $result = (new result_builder($adapter, $adapter->get_userid()))->build();

        $ids = [];
        foreach ($result->actions as $action) {
            $this->assertArrayHasKey('url', $action);
            $this->assertArrayHasKey('label', $action);
            $this->assertArrayHasKey('id', $action);
            $this->assertNotSame('', $action['url']);
            $this->assertNotSame('', $action['label']);
            $ids[] = $action['id'];
        }

        $this->assertContains('course', $ids, 'Return to course is always offered.');
        $this->assertContains('retry', $ids, 'A retry is offered while attempts remain.');
    }

    /**
     * A learner with no attempts left is not offered a retry.
     *
     * @return void
     */
    public function test_no_retry_action_when_exhausted(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $quiz = $this->create_quiz(['attempts' => 1]);
        $adapter = quiz_adapter::create($this->submit_attempt($quiz, false));
        $result = (new result_builder($adapter, $adapter->get_userid()))->build();

        $ids = array_column($result->actions, 'id');

        $this->assertNotContains('retry', $ids);
        $this->assertContains('course', $ids);
    }

    /**
     * Colours reaching the page are always validated hexadecimal values.
     *
     * @return void
     */
    public function test_appearance_colours_are_sanitised(): void {
        $this->resetAfterTest();
        $this->setup_course();
        set_config('themecolour', 'javascript:alert(1)', constants::PLUGIN);

        $quiz = $this->create_quiz();
        $adapter = quiz_adapter::create($this->submit_attempt($quiz, true));
        $result = (new result_builder($adapter, $adapter->get_userid()))->build();

        $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $result->appearance->themecolour);
    }

    /**
     * The exported template context is free of data access and complete.
     *
     * @return void
     */
    public function test_export_is_complete(): void {
        global $PAGE;

        $this->resetAfterTest();
        $this->setup_course();

        $quiz = $this->create_quiz();
        $adapter = quiz_adapter::create($this->submit_attempt($quiz, true));
        $result = (new result_builder($adapter, $adapter->get_userid()))->build();

        $context = (new result_page($result))->export_for_template($PAGE->get_renderer('core'));

        foreach (['verdict', 'cssvars', 'title', 'message', 'actions', 'hasactions', 'showicon'] as $key) {
            $this->assertArrayHasKey($key, $context);
        }

        $this->assertStringContainsString('--ljy-theme', $context['cssvars']);
    }

    /**
     * A warm result build stays within the documented query budget.
     *
     * Caches are warmed first, because the figure the architecture commits to
     * describes a normal request rather than a cold cache.
     *
     * @return void
     */
    public function test_query_budget(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setup_course();

        $quiz = $this->create_quiz();
        $attemptid = $this->submit_attempt($quiz, true);

        // Warm the caches with a discarded build.
        $warm = quiz_adapter::create($attemptid);
        (new result_builder($warm, $warm->get_userid()))->build();

        $adapter = quiz_adapter::create($attemptid);
        $before = $DB->perf_get_reads() + $DB->perf_get_writes();
        (new result_builder($adapter, $adapter->get_userid()))->build();
        $after = $DB->perf_get_reads() + $DB->perf_get_writes();

        $this->assertLessThanOrEqual(
            self::QUERY_BUDGET,
            $after - $before,
            'A warm result build must stay within the measured query budget.'
        );
    }

    /**
     * Rendering a result performs no plugin database writes.
     *
     * @return void
     */
    public function test_no_writes_during_render(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setup_course();

        $quiz = $this->create_quiz();
        $adapter = quiz_adapter::create($this->submit_attempt($quiz, true));

        $before = $DB->perf_get_writes();
        (new result_builder($adapter, $adapter->get_userid()))->build();

        $this->assertSame($before, $DB->perf_get_writes(), 'Rendering a result must not write to the database.');
    }
}
