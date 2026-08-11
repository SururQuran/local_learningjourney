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

namespace local_learningjourney\local;

use cm_info;
use context_module;
use local_learningjourney\local\model\attempt_info;
use mod_quiz\question\display_options;
use mod_quiz\quiz_settings;
use moodle_exception;
use moodle_url;
use question_display_options;
use stdClass;

/**
 * The only class permitted to reference mod_quiz APIs.
 *
 * Isolating mod_quiz here means that an upstream class rename, such as the
 * move from quiz to quiz_settings, is a single file change for this plugin.
 * The question engine is deliberately never loaded, because instantiating a
 * question usage is by far the most expensive part of the page this plugin
 * replaces.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_adapter {
    /** @var stdClass The quiz_attempts record. */
    protected stdClass $attempt;

    /** @var quiz_settings Quiz settings wrapper supplied by mod_quiz. */
    protected quiz_settings $quizobj;

    /** @var cm_info Course module of the quiz, resolved from modinfo. */
    protected cm_info $cm;

    /** @var stdClass Course the quiz belongs to. */
    protected stdClass $course;

    /** @var stdClass[]|null Memoised list of this learner's finished attempts. */
    protected ?array $attempts = null;

    /** @var display_options|null Memoised review options for this attempt. */
    protected ?display_options $reviewoptions = null;

    /** @var attempt_info|null Memoised attempt description. */
    protected ?attempt_info $attemptinfo = null;

    /** @var string|false|null Memoised reason a further attempt is blocked. */
    protected $blockedreason = null;

    /**
     * Create an adapter for a single quiz attempt.
     *
     * @param stdClass $attempt The quiz_attempts record.
     * @param quiz_settings $quizobj Quiz settings wrapper for the attempt.
     * @param cm_info $cm Course module of the quiz.
     * @param stdClass $course Course the quiz belongs to.
     */
    public function __construct(stdClass $attempt, quiz_settings $quizobj, cm_info $cm, stdClass $course) {
        $this->attempt = $attempt;
        $this->quizobj = $quizobj;
        $this->cm = $cm;
        $this->course = $course;
    }

    /**
     * Build an adapter from an attempt identifier.
     *
     * @param int $attemptid Quiz attempt identifier.
     * @return self A fully loaded adapter.
     * @throws moodle_exception When the attempt or its course module cannot be found.
     */
    public static function create(int $attemptid): self {
        global $DB;

        $attempt = $DB->get_record('quiz_attempts', ['id' => $attemptid]);
        if (!$attempt) {
            throw new moodle_exception('error_attemptnotfound', constants::PLUGIN);
        }

        $quizobj = quiz_settings::create($attempt->quiz, $attempt->userid);
        $course = $quizobj->get_course();
        $modinfo = get_fast_modinfo($course, $attempt->userid);
        $cm = $modinfo->get_cm($quizobj->get_cmid());

        return new self($attempt, $quizobj, $cm, $course);
    }

    /**
     * Determine whether an exception means the attempt simply is not there.
     *
     * Recognised codes are this plugin's own not-found code and the codes the
     * database layer raises for a missing record. Anything else is a genuine
     * fault and must keep Moodle's normal error reporting.
     *
     * @param moodle_exception $e The exception raised while loading an attempt.
     * @return bool True when the attempt, or the data it depends on, is gone.
     */
    public static function is_missing_attempt(moodle_exception $e): bool {
        return in_array(
            $e->errorcode,
            ['error_attemptnotfound', 'invalidrecord', 'invalidrecordunknown'],
            true
        );
    }

    /**
     * Return the course the quiz belongs to.
     *
     * @return stdClass The course record.
     */
    public function get_course(): stdClass {
        return $this->course;
    }

    /**
     * Return the course module of the quiz.
     *
     * @return cm_info The course module.
     */
    public function get_cm(): cm_info {
        return $this->cm;
    }

    /**
     * Return the module context of the quiz.
     *
     * @return context_module The module context.
     */
    public function get_context(): context_module {
        return context_module::instance($this->cm->id);
    }

    /**
     * Return the identifier of the learner who owns the attempt.
     *
     * @return int The owning user identifier.
     */
    public function get_userid(): int {
        return (int) $this->attempt->userid;
    }

    /**
     * Return the raw attempt record.
     *
     * @return stdClass The quiz_attempts record.
     */
    public function get_attempt_record(): stdClass {
        return $this->attempt;
    }

    /**
     * Return the quiz settings wrapper.
     *
     * @return quiz_settings The mod_quiz settings wrapper.
     */
    public function get_quiz_settings(): quiz_settings {
        return $this->quizobj;
    }

    /**
     * Determine whether the attempt has been submitted and closed.
     *
     * @return bool True when the attempt is in the finished state.
     */
    public function is_finished(): bool {
        return $this->attempt->state === 'finished';
    }

    /**
     * Determine whether the attempt was made in preview mode.
     *
     * @return bool True when this is a teacher preview attempt.
     */
    public function is_preview(): bool {
        return !empty($this->attempt->preview);
    }

    /**
     * Return the number of seconds the learner spent on the attempt.
     *
     * @return int Elapsed seconds, never negative.
     */
    public function time_taken(): int {
        $start = (int) $this->attempt->timestart;
        $finish = (int) $this->attempt->timefinish;

        return $finish > $start ? $finish - $start : 0;
    }

    /**
     * Return the ordinal number of this attempt.
     *
     * @return int The attempt number.
     */
    public function attempt_number(): int {
        return (int) $this->attempt->attempt;
    }

    /**
     * Return the maximum number of attempts the quiz permits.
     *
     * @return int Permitted attempts, or 0 when unlimited.
     */
    public function attempts_allowed(): int {
        return (int) $this->quizobj->get_quiz()->attempts;
    }

    /**
     * Return the maximum grade configured for the quiz.
     *
     * @return float The quiz maximum grade.
     */
    public function get_quiz_max_grade(): float {
        return (float) $this->quizobj->get_quiz()->grade;
    }

    /**
     * Return the grade earned by this attempt, on the quiz grade scale.
     *
     * @return float|null The attempt grade, or null when it is not yet available.
     */
    public function get_attempt_grade(): ?float {
        global $CFG;

        if ($this->attempt->sumgrades === null) {
            return null;
        }

        require_once($CFG->dirroot . '/mod/quiz/locallib.php');

        $grade = quiz_rescale_grade($this->attempt->sumgrades, $this->quizobj->get_quiz(), false);

        return $grade === null ? null : (float) $grade;
    }

    /**
     * Return the percentage earned by this attempt.
     *
     * @return float|null The percentage, or null when it is not yet available.
     */
    public function get_attempt_percentage(): ?float {
        $max = $this->get_quiz_max_grade();
        $grade = $this->get_attempt_grade();

        if ($grade === null || $max <= 0) {
            return null;
        }

        return round(($grade / $max) * 100, 2);
    }

    /**
     * Format a grade using the decimal places configured for the quiz.
     *
     * @param float|null $grade The grade to format.
     * @return string The formatted grade, or an empty string when unavailable.
     */
    public function format_grade(?float $grade): string {
        global $CFG;

        if ($grade === null) {
            return '';
        }

        require_once($CFG->dirroot . '/mod/quiz/locallib.php');

        return (string) quiz_format_grade($this->quizobj->get_quiz(), $grade);
    }

    /**
     * Determine whether manual grading of this attempt is still outstanding.
     *
     * A finished attempt keeps a null sum of grades precisely when at least one
     * question could not be graded automatically, which is the condition core
     * itself uses before writing a grade to the gradebook.
     *
     * @return bool True when a grade is not yet final.
     */
    public function has_pending_manual_grading(): bool {
        return $this->is_finished() && $this->attempt->sumgrades === null;
    }

    /**
     * Return the number of finished, non preview attempts by this learner.
     *
     * @return int Count of finished attempts.
     */
    public function attempts_used(): int {
        return count($this->load_attempts());
    }

    /**
     * Return the number of attempts still available to this learner.
     *
     * @return int|null Remaining attempts, or null when unlimited.
     */
    public function attempts_remaining(): ?int {
        $allowed = $this->attempts_allowed();
        if ($allowed <= 0) {
            return null;
        }

        return max(0, $allowed - $this->attempts_used());
    }

    /**
     * Determine whether the learner may start a further attempt right now.
     *
     * The mod_quiz access manager is authoritative here, so time limits,
     * enforced delays, passwords and network restrictions are all respected.
     *
     * @return bool True when a further attempt is currently permitted.
     */
    public function can_start_new_attempt(): bool {
        return $this->new_attempt_blocked_reason() === null;
    }

    /**
     * Return the reason a further attempt is not currently permitted.
     *
     * @return string|null The reason, or null when a retry is permitted.
     */
    public function new_attempt_blocked_reason(): ?string {
        if ($this->blockedreason !== null) {
            return $this->blockedreason === false ? null : $this->blockedreason;
        }

        $this->blockedreason = $this->resolve_blocked_reason() ?? false;

        return $this->blockedreason === false ? null : $this->blockedreason;
    }

    /**
     * Ask the mod_quiz access manager whether a further attempt is permitted.
     *
     * @return string|null The reason, or null when a retry is permitted.
     */
    protected function resolve_blocked_reason(): ?string {
        $timenow = time();
        $accessmanager = $this->quizobj->get_access_manager($timenow);

        $reason = $this->first_message($accessmanager->prevent_access());
        if ($reason !== null) {
            return $reason;
        }

        $attempts = $this->load_attempts();
        $lastattempt = empty($attempts) ? null : end($attempts);

        return $this->first_message($accessmanager->prevent_new_attempt(count($attempts), $lastattempt));
    }

    /**
     * Determine whether the learner may review this attempt.
     *
     * @return bool True when review of the attempt itself is permitted.
     */
    public function can_review_attempt(): bool {
        if (!$this->is_finished()) {
            return false;
        }

        return $this->get_review_options()->attempt == question_display_options::VISIBLE;
    }

    /**
     * Return the core review page for this attempt.
     *
     * @return moodle_url|null The review URL, or null when review is not permitted.
     */
    public function get_review_url(): ?moodle_url {
        if (!$this->can_review_attempt()) {
            return null;
        }

        return new moodle_url('/mod/quiz/review.php', ['attempt' => (int) $this->attempt->id]);
    }

    /**
     * Return the page used to start a further attempt.
     *
     * @return moodle_url The quiz view page.
     */
    public function get_attempt_url(): moodle_url {
        return new moodle_url('/mod/quiz/view.php', ['id' => $this->cm->id]);
    }

    /**
     * Assemble the immutable attempt description.
     *
     * @return attempt_info The attempt description.
     */
    public function get_attempt_info(): attempt_info {
        if ($this->attemptinfo !== null) {
            return $this->attemptinfo;
        }

        $blocked = $this->new_attempt_blocked_reason();

        $this->attemptinfo = new attempt_info(
            attemptid: (int) $this->attempt->id,
            attemptnumber: $this->attempt_number(),
            attemptsused: $this->attempts_used(),
            attemptsallowed: $this->attempts_allowed(),
            attemptsremaining: $this->attempts_remaining(),
            timetaken: $this->time_taken(),
            timefinish: (int) $this->attempt->timefinish,
            canretry: $blocked === null,
            retryblockedreason: $blocked,
            reviewurl: $this->get_review_url(),
            retryurl: $this->get_attempt_url(),
        );

        return $this->attemptinfo;
    }

    /**
     * Load and memoise this learner's finished attempts at the quiz.
     *
     * @return stdClass[] Finished attempts in chronological order.
     */
    protected function load_attempts(): array {
        global $CFG;

        if ($this->attempts === null) {
            require_once($CFG->dirroot . '/mod/quiz/locallib.php');

            $this->attempts = quiz_get_user_attempts(
                (int) $this->quizobj->get_quizid(),
                $this->get_userid(),
                'finished',
                false
            );
        }

        return $this->attempts;
    }

    /**
     * Resolve and memoise the review options that apply to this attempt.
     *
     * @return display_options The applicable review options.
     */
    protected function get_review_options(): display_options {
        if ($this->reviewoptions === null) {
            $this->reviewoptions = display_options::make_from_quiz(
                $this->quizobj->get_quiz(),
                $this->attempt_state()
            );
        }

        return $this->reviewoptions;
    }

    /**
     * Determine which set of review options currently applies.
     *
     * This mirrors the state calculation mod_quiz performs when it loads a full
     * attempt object, which this plugin deliberately avoids doing.
     *
     * @return int One of the display_options state constants.
     */
    protected function attempt_state(): int {
        $quiz = $this->quizobj->get_quiz();

        if (!$this->is_finished()) {
            return display_options::DURING;
        }

        if (!empty($quiz->timeclose) && time() >= (int) $quiz->timeclose) {
            return display_options::AFTER_CLOSE;
        }

        if (time() < (int) $this->attempt->timefinish + 120) {
            return display_options::IMMEDIATELY_AFTER;
        }

        return display_options::LATER_WHILE_OPEN;
    }

    /**
     * Reduce an access rule response to a single readable sentence.
     *
     * The access manager returns an array of reasons, which is empty when
     * nothing prevents access.
     *
     * @param array|string|bool $messages The access manager response.
     * @return string|null The first reason, or null when access is not prevented.
     */
    protected function first_message($messages): ?string {
        if (is_array($messages)) {
            $messages = reset($messages);
        }

        if ($messages === false || $messages === null) {
            return null;
        }

        $text = trim(html_to_text((string) $messages, 0, false));

        return $text === '' ? null : $text;
    }
}
