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
use local_learningjourney\local\grade_resolver;
use local_learningjourney\local\model\grade_info;
use local_learningjourney\local\quiz_adapter;
use local_learningjourney\local\settings_resolver;

/**
 * Tests for the pass mark chain and the pass or fail verdict.
 *
 * The pass mark must remain configurable end to end: administrator setting,
 * stored configuration, resolver, verdict, displayed value.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_learningjourney\local\grade_resolver
 */
final class grade_resolver_test extends \local_learningjourney\tests\journey_testcase {
    /**
     * Resolve the grade description for a finished attempt.
     *
     * @param int $attemptid Identifier of the finished attempt.
     * @return grade_info The resolved grade description.
     */
    protected function resolve(int $attemptid): grade_info {
        $quiz = quiz_adapter::create($attemptid);
        $settings = new settings_resolver((int) $this->course->id);

        $resolver = new grade_resolver(
            $quiz->get_course(),
            $quiz->get_cm(),
            $quiz->get_userid(),
            $settings
        );

        return $resolver->resolve($quiz);
    }

    /**
     * The shipped default pass mark is 60 percent and it is applied.
     *
     * @return void
     */
    public function test_default_pass_mark_is_sixty(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $this->assertSame(60, constants::DEFAULT_GRADEPASS_PERCENT);

        $quiz = $this->create_quiz();
        $result = $this->resolve($this->submit_attempt($quiz, true));

        $this->assertEqualsWithDelta(60.0, $result->gradepasspercent, 0.01);
        $this->assertSame(grade_info::SOURCE_FALLBACK, $result->gradepasssource);
        $this->assertSame(constants::RESULT_PASS, $result->verdict);
    }

    /**
     * Data provider covering the pass marks an administrator is likely to set.
     *
     * @return array<string, array{0: int}> The configured pass mark.
     */
    public static function pass_mark_provider(): array {
        return [
            '60 percent' => [60],
            '70 percent' => [70],
            '75 percent' => [75],
            '80 percent' => [80],
        ];
    }

    /**
     * A pass mark set by an administrator reaches the verdict unchanged.
     *
     * @dataProvider pass_mark_provider
     * @param int $passmark The pass mark configured for the site.
     * @return void
     */
    public function test_site_pass_mark_is_applied(int $passmark): void {
        $this->resetAfterTest();
        $this->setup_course();
        set_config('fallbackgradepass', $passmark, constants::PLUGIN);

        $quiz = $this->create_quiz();
        $result = $this->resolve($this->submit_attempt($quiz, true));

        $this->assertEqualsWithDelta((float) $passmark, $result->gradepasspercent, 0.01);
        $this->assertSame(constants::RESULT_PASS, $result->verdict, 'A full score passes at every threshold.');
    }

    /**
     * A course override takes precedence over the site pass mark.
     *
     * @return void
     */
    public function test_course_override_beats_site_default(): void {
        $this->resetAfterTest();
        $this->setup_course();
        set_config('fallbackgradepass', 60, constants::PLUGIN);

        settings_resolver::save_overrides(
            (int) $this->course->id,
            ['fallbackgradepass' => '80'],
            ['fallbackgradepass']
        );

        $quiz = $this->create_quiz();
        $result = $this->resolve($this->submit_attempt($quiz, true));

        $this->assertEqualsWithDelta(80.0, $result->gradepasspercent, 0.01);
    }

    /**
     * A pass grade set on the quiz itself outranks every plugin setting.
     *
     * @return void
     */
    public function test_quiz_gradepass_wins(): void {
        $this->resetAfterTest();
        $this->setup_course();
        set_config('fallbackgradepass', 60, constants::PLUGIN);

        $quiz = $this->create_quiz();
        $this->set_quiz_gradepass($quiz, 90.0);

        $result = $this->resolve($this->submit_attempt($quiz, true));

        $this->assertEqualsWithDelta(90.0, $result->gradepasspercent, 0.01);
        $this->assertSame(grade_info::SOURCE_QUIZ, $result->gradepasssource);
    }

    /**
     * With the fallback disabled and no quiz pass grade, no verdict is given.
     *
     * @return void
     */
    public function test_no_pass_mark_yields_no_verdict(): void {
        $this->resetAfterTest();
        $this->setup_course();
        set_config('usefallbackgradepass', 0, constants::PLUGIN);

        $quiz = $this->create_quiz();
        $result = $this->resolve($this->submit_attempt($quiz, true));

        $this->assertNull($result->gradepasspercent);
        $this->assertSame(constants::RESULT_NOMARK, $result->verdict);
    }

    /**
     * A score exactly on the pass mark is a pass, and one below it is not.
     *
     * @return void
     */
    public function test_threshold_boundaries(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $quiz = $this->create_quiz();
        $this->set_quiz_gradepass($quiz, 100.0);

        $exact = $this->resolve($this->submit_attempt($quiz, true));
        $this->assertSame(constants::RESULT_PASS, $exact->verdict, 'Exactly on the pass mark is a pass.');

        $below = $this->resolve($this->submit_attempt($quiz, false, 2));
        $this->assertSame(constants::RESULT_FAIL, $below->verdict, 'Below the pass mark is a fail.');
    }

    /**
     * The reported percentage matches the score achieved.
     *
     * @return void
     */
    public function test_percentage_is_reported(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $quiz = $this->create_quiz();

        $passed = $this->resolve($this->submit_attempt($quiz, true));
        $this->assertEqualsWithDelta(100.0, $passed->percent, 0.01);

        $failed = $this->resolve($this->submit_attempt($quiz, false, 2));
        $this->assertEqualsWithDelta(0.0, $failed->percent, 0.01);
    }

    /**
     * An out of range pass mark is rejected before it can be stored.
     *
     * @return void
     */
    public function test_invalid_pass_mark_is_rejected(): void {
        $this->resetAfterTest();

        $this->assertNotNull(settings_resolver::validate_range('101', 0, 100));
        $this->assertNotNull(settings_resolver::validate_range('-1', 0, 100));
        $this->assertNotNull(settings_resolver::validate_range('eighty', 0, 100));
        $this->assertNull(settings_resolver::validate_range('80', 0, 100));
        $this->assertNull(settings_resolver::validate_range('0', 0, 100));
        $this->assertNull(settings_resolver::validate_range('100', 0, 100));
    }

    /**
     * The administrator can move the pass mark without any code change.
     *
     * A single 65 percent result must pass at 60, fail at 70, fail at 80 and
     * pass again when the setting is returned to 60. Only the stored setting
     * changes between assertions.
     *
     * @return void
     */
    public function test_changing_the_pass_mark_changes_the_verdict(): void {
        $this->resetAfterTest();
        $this->setup_course();

        $quiz = $this->create_multi_question_quiz(20);
        $attemptid = $this->submit_partial_attempt($quiz, 13, 20);

        $baseline = $this->resolve($attemptid);
        $this->assertEqualsWithDelta(65.0, $baseline->percent, 0.01, 'The fixture must score 65 percent.');

        set_config('fallbackgradepass', 60, constants::PLUGIN);
        settings_resolver::purge((int) $this->course->id);
        $result = $this->resolve($attemptid);
        $this->assertEqualsWithDelta(60.0, $result->gradepasspercent, 0.01);
        $this->assertSame(constants::RESULT_PASS, $result->verdict, '65 percent passes at a 60 percent pass mark.');

        set_config('fallbackgradepass', 70, constants::PLUGIN);
        settings_resolver::purge((int) $this->course->id);
        $result = $this->resolve($attemptid);
        $this->assertEqualsWithDelta(70.0, $result->gradepasspercent, 0.01);
        $this->assertSame(constants::RESULT_FAIL, $result->verdict, '65 percent fails at a 70 percent pass mark.');

        set_config('fallbackgradepass', 80, constants::PLUGIN);
        settings_resolver::purge((int) $this->course->id);
        $result = $this->resolve($attemptid);
        $this->assertEqualsWithDelta(80.0, $result->gradepasspercent, 0.01);
        $this->assertSame(constants::RESULT_FAIL, $result->verdict, '65 percent fails at an 80 percent pass mark.');

        set_config('fallbackgradepass', 60, constants::PLUGIN);
        settings_resolver::purge((int) $this->course->id);
        $result = $this->resolve($attemptid);
        $this->assertSame(constants::RESULT_PASS, $result->verdict, 'Returning the setting to 60 restores the pass.');
    }

    /**
     * A course override moves the verdict for that course alone.
     *
     * @return void
     */
    public function test_course_override_changes_the_verdict(): void {
        $this->resetAfterTest();
        $this->setup_course();
        set_config('fallbackgradepass', 60, constants::PLUGIN);

        $quiz = $this->create_multi_question_quiz(20);
        $attemptid = $this->submit_partial_attempt($quiz, 13, 20);

        $this->assertSame(constants::RESULT_PASS, $this->resolve($attemptid)->verdict);

        settings_resolver::save_overrides(
            (int) $this->course->id,
            ['fallbackgradepass' => '70'],
            ['fallbackgradepass']
        );

        $this->assertSame(constants::RESULT_FAIL, $this->resolve($attemptid)->verdict);
    }

    /**
     * A score exactly on a raised pass mark still passes.
     *
     * @return void
     */
    public function test_exact_match_on_a_raised_pass_mark(): void {
        $this->resetAfterTest();
        $this->setup_course();
        set_config('fallbackgradepass', 75, constants::PLUGIN);

        $quiz = $this->create_multi_question_quiz(20);
        $attemptid = $this->submit_partial_attempt($quiz, 15, 20);

        $result = $this->resolve($attemptid);

        $this->assertEqualsWithDelta(75.0, $result->percent, 0.01);
        $this->assertSame(constants::RESULT_PASS, $result->verdict);
    }
}
