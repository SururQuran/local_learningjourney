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
use local_learningjourney\local\next_activity_finder;

/**
 * Tests for next activity detection.
 *
 * Moodle's own modinfo and availability engine remain authoritative; these
 * tests confirm the plugin consumes them rather than second guessing them.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_learningjourney\local\next_activity_finder
 */
final class next_activity_finder_test extends \local_learningjourney\tests\journey_testcase {
    /**
     * Create a page activity in the course.
     *
     * @param array $options Extra module options.
     * @return \cm_info The created course module.
     */
    protected function create_page(array $options = []) {
        $page = $this->getDataGenerator()->create_module(
            'page',
            $options + ['course' => $this->course->id]
        );

        return get_fast_modinfo($this->course, $this->learner->id)->get_cm($page->cmid);
    }

    /**
     * The immediately following activity in the same section is offered.
     *
     * @return void
     */
    public function test_next_activity_in_same_section(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $first = $this->create_page(['section' => 1, 'name' => 'Lesson one']);
        $this->create_page(['section' => 1, 'name' => 'Lesson two']);

        $step = (new next_activity_finder($this->course, $this->learner->id))->find_next($first);

        $this->assertSame(constants::NEXT_ACTIVITY, $step->type);
        $this->assertSame('Lesson two', $step->name);
        $this->assertNotNull($step->url);
    }

    /**
     * Detection continues into the following section.
     *
     * @return void
     */
    public function test_next_activity_crosses_section_boundary(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $first = $this->create_page(['section' => 1, 'name' => 'Lesson one']);
        $this->create_page(['section' => 2, 'name' => 'Lesson two']);

        $step = (new next_activity_finder($this->course, $this->learner->id))->find_next($first);

        $this->assertSame(constants::NEXT_ACTIVITY, $step->type);
        $this->assertSame('Lesson two', $step->name);
    }

    /**
     * A label has no page of its own and is skipped.
     *
     * @return void
     */
    public function test_labels_are_skipped(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $first = $this->create_page(['section' => 1, 'name' => 'Lesson one']);
        $this->getDataGenerator()->create_module('label', ['course' => $this->course->id, 'section' => 1]);
        $this->create_page(['section' => 1, 'name' => 'Lesson two']);

        $step = (new next_activity_finder($this->course, $this->learner->id))->find_next($first);

        $this->assertSame('Lesson two', $step->name);
    }

    /**
     * A hidden activity is never offered to the learner.
     *
     * @return void
     */
    public function test_hidden_activity_is_skipped(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $first = $this->create_page(['section' => 1, 'name' => 'Lesson one']);
        $this->create_page(['section' => 1, 'name' => 'Hidden lesson', 'visible' => 0]);
        $this->create_page(['section' => 1, 'name' => 'Lesson three']);

        $step = (new next_activity_finder($this->course, $this->learner->id))->find_next($first);

        $this->assertSame('Lesson three', $step->name);
    }

    /**
     * A restricted activity is reported as blocked and is never named.
     *
     * @return void
     */
    public function test_restricted_activity_is_blocked_and_not_named(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $first = $this->create_page(['section' => 1, 'name' => 'Lesson one']);
        $this->create_page([
            'section'        => 1,
            'name'           => 'Locked lesson',
            'availability'   => json_encode((object) [
                'op' => '&',
                'c'  => [(object) ['type' => 'date', 'd' => '>=', 't' => time() + WEEKSECS]],
                'showc' => [false],
            ]),
        ]);

        $step = (new next_activity_finder($this->course, $this->learner->id))->find_next($first);

        $this->assertSame(constants::NEXT_BLOCKED, $step->type);
        $this->assertSame('', $step->name, 'A restricted activity must never be named.');
        $this->assertNull($step->url);
    }

    /**
     * The final activity in a course reports that nothing remains.
     *
     * @return void
     */
    public function test_final_activity_reports_course_complete(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $only = $this->create_page(['section' => 1, 'name' => 'Only lesson']);

        $step = (new next_activity_finder($this->course, $this->learner->id))->find_next($only);

        $this->assertSame(constants::NEXT_COURSE_COMPLETE, $step->type);
        $this->assertFalse($step->is_available());
    }

    /**
     * The preceding viewable activity is found for the review lesson action.
     *
     * @return void
     */
    public function test_previous_viewable_activity(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $this->create_page(['section' => 1, 'name' => 'Lesson one']);
        $second = $this->create_page(['section' => 1, 'name' => 'Lesson two']);

        $finder = new next_activity_finder($this->course, $this->learner->id);
        $previous = $finder->find_previous_viewable($second);

        $this->assertNotNull($previous);
        $this->assertSame('Lesson one', $previous->name);
    }

    /**
     * The first activity in a course has no preceding activity.
     *
     * @return void
     */
    public function test_no_previous_activity(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $first = $this->create_page(['section' => 1, 'name' => 'Lesson one']);

        $finder = new next_activity_finder($this->course, $this->learner->id);

        $this->assertNull($finder->find_previous_viewable($first));
    }
}
