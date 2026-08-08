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
use local_learningjourney\local\progress_calculator;
use local_learningjourney\local\settings_resolver;

/**
 * Tests for course and lesson progress calculation.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_learningjourney\local\progress_calculator
 */
final class progress_calculator_test extends \local_learningjourney\tests\journey_testcase {
    /**
     * Create a page with manual completion tracking enabled.
     *
     * @param int $section Section number.
     * @param string $name Activity name.
     * @return \cm_info The created course module.
     */
    protected function create_tracked_page(int $section, string $name) {
        $page = $this->getDataGenerator()->create_module('page', [
            'course'     => $this->course->id,
            'section'    => $section,
            'name'       => $name,
            'completion' => COMPLETION_TRACKING_MANUAL,
        ]);

        return get_fast_modinfo($this->course, $this->learner->id)->get_cm($page->cmid);
    }

    /**
     * Mark an activity complete for the learner.
     *
     * @param \cm_info $cm The activity to complete.
     * @return void
     */
    protected function complete(\cm_info $cm): void {
        $completion = new \completion_info($this->course);
        $completion->update_state($cm, COMPLETION_COMPLETE, $this->learner->id);
    }

    /**
     * Build a calculator bound to the fixture learner.
     *
     * @return progress_calculator The calculator under test.
     */
    protected function calculator(): progress_calculator {
        return new progress_calculator(
            $this->course,
            $this->learner->id,
            new settings_resolver((int) $this->course->id)
        );
    }

    /**
     * A learner who has completed nothing shows zero progress.
     *
     * @return void
     */
    public function test_zero_completion(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $first = $this->create_tracked_page(1, 'Lesson one');
        $this->create_tracked_page(2, 'Lesson two');

        $progress = $this->calculator()->calculate($first);

        $this->assertTrue($progress->available);
        $this->assertSame(0, $progress->activitiescompleted);
        $this->assertSame(2, $progress->activitiestotal);
        $this->assertSame(2, $progress->activitiesremaining);
        $this->assertSame(0, $progress->unitscompleted);
    }

    /**
     * Partial completion is counted, and the current unit is identified.
     *
     * @return void
     */
    public function test_partial_completion(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $first = $this->create_tracked_page(1, 'Lesson one');
        $this->create_tracked_page(2, 'Lesson two');
        $this->complete($first);

        $progress = $this->calculator()->calculate($first);

        $this->assertSame(1, $progress->activitiescompleted);
        $this->assertSame(1, $progress->activitiesremaining);
        $this->assertSame(1, $progress->unitindex, 'The learner is in the first section.');
        $this->assertSame(2, $progress->unittotal);
        $this->assertSame(1, $progress->unitscompleted);
    }

    /**
     * Full completion reports every unit complete and no remainder.
     *
     * @return void
     */
    public function test_full_completion(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $first = $this->create_tracked_page(1, 'Lesson one');
        $second = $this->create_tracked_page(2, 'Lesson two');
        $this->complete($first);
        $this->complete($second);

        $progress = $this->calculator()->calculate($second);

        $this->assertSame(2, $progress->activitiescompleted);
        $this->assertSame(0, $progress->activitiesremaining);
        $this->assertSame(2, $progress->unitscompleted);
        $this->assertSame(2, $progress->unitindex, 'The learner is in the second section.');
    }

    /**
     * Counting activities rather than sections changes the unit totals.
     *
     * @return void
     */
    public function test_activity_unit_mode(): void {
        $this->resetAfterTest();
        $this->setup_course();
        set_config('unitmode', constants::UNIT_ACTIVITY, constants::PLUGIN);

        $first = $this->create_tracked_page(1, 'Lesson one');
        $this->create_tracked_page(1, 'Lesson two');
        $this->create_tracked_page(2, 'Lesson three');

        $progress = $this->calculator()->calculate($first);

        $this->assertSame(constants::UNIT_ACTIVITY, $progress->unitmode);
        $this->assertSame(3, $progress->unittotal);
        $this->assertSame(1, $progress->unitindex);
    }

    /**
     * With completion switched off the progress block is withheld entirely.
     *
     * @return void
     */
    public function test_completion_disabled(): void {
        $this->resetAfterTest();
        $this->setup_course(['enablecompletion' => 0]);

        $page = $this->getDataGenerator()->create_module('page', [
            'course'  => $this->course->id,
            'section' => 1,
        ]);
        $cm = get_fast_modinfo($this->course, $this->learner->id)->get_cm($page->cmid);

        $progress = $this->calculator()->calculate($cm);

        $this->assertFalse($progress->available);
    }
}
