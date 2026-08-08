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
use context_course;
use context_system;
use local_learningjourney\local\model\appearance;
use local_learningjourney\local\model\messages;
use local_learningjourney\local\model\next_step;
use local_learningjourney\local\model\progress_info;
use local_learningjourney\local\model\result_context;
use moodle_url;
use stdClass;

/**
 * Orchestrates the domain services and assembles the result context.
 *
 * This is the only class that knows the composition order, which is fixed:
 * settings, grade, progress, next activity, badges, stars, presentation.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class result_builder {
    /** @var string[] Names of the display toggles exposed to the output layer. */
    protected const DISPLAY_KEYS = [
        'showscore', 'showpercentage', 'showgradepass', 'showstatus', 'showtimetaken',
        'showattempt', 'showstars', 'showprogress', 'showcoursecompletion', 'showbadges',
        'showreviewlink',
    ];

    /** @var string[] Names of the celebration effects exposed to the output layer. */
    protected const EFFECT_KEYS = [
        'confetti', 'stars', 'trophy', 'fireworks', 'badge', 'sound',
    ];

    /** @var quiz_adapter Adapter for the attempt being reported on. */
    protected quiz_adapter $quiz;

    /** @var int Learner whose result is built. */
    protected int $userid;

    /**
     * Create a builder for one attempt.
     *
     * @param quiz_adapter $quiz Adapter for the attempt being reported on.
     * @param int $userid Learner whose result is built.
     */
    public function __construct(quiz_adapter $quiz, int $userid) {
        $this->quiz = $quiz;
        $this->userid = $userid;
    }

    /**
     * Assemble the complete result context for the page.
     *
     * @return result_context The immutable aggregate consumed by the output layer.
     */
    public function build(): result_context {
        $course = $this->quiz->get_course();
        $cm = $this->quiz->get_cm();
        $settings = new settings_resolver((int) $course->id);

        $grade = (new grade_resolver($course, $cm, $this->userid, $settings))->resolve($this->quiz);
        $verdict = $grade->verdict;
        $ispass = $verdict === constants::RESULT_PASS;

        $progress = $settings->get_bool('showprogress')
            ? (new progress_calculator($course, $this->userid, $settings))->calculate($cm)
            : new progress_info(available: false, unitmode: $settings->get('unitmode'));

        $finder = new next_activity_finder($course, $this->userid);
        $nextstep = $finder->find_next($cm);
        $previous = $ispass ? null : $finder->find_previous_viewable($cm);

        $badges = [];
        if ($ispass && $settings->get_bool('showbadges')) {
            $locator = new badge_locator($course, $this->userid, $settings);
            $awarded = $locator->award_mapped_badge();
            $badges = $locator->find_recent((int) $this->quiz->get_attempt_record()->timefinish);

            if ($awarded !== null && !$this->contains_badge($badges, $awarded->id)) {
                array_unshift($badges, $awarded);
            }
        }

        $stars = 0;
        if ($ispass && $settings->get_bool('showstars')) {
            $stars = (new star_rating($settings))->stars_for($grade->percent);
        }

        return new result_context(
            verdict: $verdict,
            course: $course,
            cm: $cm,
            attempt: $this->quiz->get_attempt_info(),
            grade: $grade,
            progress: $progress,
            nextstep: $nextstep,
            badges: $badges,
            stars: $stars,
            appearance: $this->build_appearance($settings, $course),
            messages: $this->build_messages($settings, $verdict, $progress, $previous),
            actions: $this->build_actions($settings, $verdict, $nextstep, $previous),
        );
    }

    /**
     * Build the validated presentation settings for the page.
     *
     * @param settings_resolver $settings Effective settings for the course.
     * @param stdClass $course Course the quiz belongs to.
     * @return appearance The validated presentation settings.
     */
    protected function build_appearance(settings_resolver $settings, stdClass $course): appearance {
        $effects = [];
        foreach (self::EFFECT_KEYS as $effect) {
            $effects[$effect] = $settings->get_bool('effect' . $effect);
        }

        $display = [];
        foreach (self::DISPLAY_KEYS as $key) {
            $display[$key] = $settings->get_bool($key);
        }

        $sound = $effects['sound'] ? $this->file_url($course, constants::FILEAREA_SOUND) : null;

        return new appearance(
            themecolour: $settings->get_colour('themecolour'),
            buttoncolour: $settings->get_colour('buttoncolour'),
            buttontextcolour: $settings->get_colour('buttontextcolour'),
            progressbarcolour: $settings->get_colour('progressbarcolour'),
            progressbgcolour: $settings->get_colour('progressbgcolour'),
            backgroundcolour: $settings->get_colour('backgroundcolour'),
            backgroundimageurl: $this->file_url($course, constants::FILEAREA_BACKGROUND),
            effects: $effects,
            layout: $settings->get('layout'),
            soundurl: $sound,
            display: $display,
            autoredirect: $settings->get_bool('autoredirect'),
            redirectdelay: max(constants::MIN_REDIRECT_DELAY, $settings->get_int('redirectdelay')),
            iconset: $settings->get('iconset'),
        );
    }

    /**
     * Resolve the page messages for a verdict.
     *
     * Each message resolves in the order course override, site setting,
     * translated language string.
     *
     * @param settings_resolver $settings Effective settings for the course.
     * @param string $verdict One of the constants::RESULT_* values.
     * @param progress_info $progress The learner's progress description.
     * @param cm_info|null $previous The closest preceding activity, when relevant.
     * @return messages The resolved message set.
     */
    protected function build_messages(
        settings_resolver $settings,
        string $verdict,
        progress_info $progress,
        ?cm_info $previous
    ): messages {
        $ispass = $verdict === constants::RESULT_PASS;
        $pending = $verdict === constants::RESULT_PENDING;

        if ($pending) {
            $title = get_string('default_pendingtitle', constants::PLUGIN);
            $body = get_string('default_pendingmessage', constants::PLUGIN);
            $islamic = '';
        } else {
            $title = $this->resolve_message(
                $settings,
                $ispass ? 'successtitle' : 'failtitle',
                $ispass ? 'default_successtitle' : 'default_failtitle'
            );
            $body = $this->resolve_message(
                $settings,
                $ispass ? 'successmessage' : 'failmessage',
                $ispass ? 'default_successmessage' : 'default_failmessage'
            );
            $islamic = $this->resolve_message(
                $settings,
                $ispass ? 'islamicsuccess' : 'islamicencouragement',
                $ispass ? 'default_islamicsuccess' : 'default_islamicencouragement'
            );
        }

        return new messages(
            title: $title,
            body: $body,
            islamicline: $islamic,
            progressline: $this->build_progress_line($progress),
            adviceline: $ispass || $pending ? '' : $this->build_advice_line($settings, $previous),
            coursecompletetext: $this->resolve_message(
                $settings,
                'coursecompletemessage',
                'default_coursecomplete'
            ),
        );
    }

    /**
     * Build the ordered list of page actions.
     *
     * @param settings_resolver $settings Effective settings for the course.
     * @param string $verdict One of the constants::RESULT_* values.
     * @param next_step $nextstep Description of the learner's next step.
     * @param cm_info|null $previous The closest preceding activity, when relevant.
     * @return array<int, array<string, mixed>> Ordered page actions.
     */
    protected function build_actions(
        settings_resolver $settings,
        string $verdict,
        next_step $nextstep,
        ?cm_info $previous
    ): array {
        $attempt = $this->quiz->get_attempt_info();
        $courseurl = new moodle_url('/course/view.php', ['id' => (int) $this->quiz->get_course()->id]);
        $actions = [];

        if ($verdict === constants::RESULT_PASS && $nextstep->is_available()) {
            $actions[] = [
                'url'     => $nextstep->url->out(false),
                'label'   => $this->continue_label($settings, $nextstep),
                'primary' => true,
                'id'      => 'continue',
            ];
        }

        if ($verdict === constants::RESULT_FAIL && $attempt->canretry && $attempt->retryurl !== null) {
            $label = trim($settings->get('retrylabel'));
            if ($label === '') {
                $label = get_string('label_tryagain', constants::PLUGIN);
            }

            $actions[] = [
                'url'     => $attempt->retryurl->out(false),
                'label'   => $label,
                'primary' => true,
                'id'      => 'retry',
            ];
        }

        if ($verdict === constants::RESULT_FAIL && $nextstep->is_available()) {
            $actions[] = [
                'url'     => $nextstep->url->out(false),
                'label'   => get_string('label_continuestudying', constants::PLUGIN),
                'primary' => false,
                'id'      => 'continuestudying',
            ];
        }

        if ($verdict === constants::RESULT_FAIL && $previous !== null && $previous->url !== null) {
            $actions[] = [
                'url'     => $previous->url->out(false),
                'label'   => get_string('label_reviewlesson', constants::PLUGIN),
                'primary' => false,
                'id'      => 'reviewlesson',
            ];
        }

        if ($settings->get_bool('showreviewlink') && $attempt->reviewurl !== null) {
            $actions[] = [
                'url'     => $attempt->reviewurl->out(false),
                'label'   => get_string('label_reviewquiz', constants::PLUGIN),
                'primary' => false,
                'id'      => 'reviewquiz',
            ];
        }

        $actions[] = [
            'url'     => $courseurl->out(false),
            'label'   => get_string('label_returntocourse', constants::PLUGIN),
            'primary' => empty($actions),
            'id'      => 'course',
        ];

        return $actions;
    }

    /**
     * Resolve the label of the primary continue action.
     *
     * @param settings_resolver $settings Effective settings for the course.
     * @param next_step $nextstep Description of the learner's next step.
     * @return string The button label.
     */
    protected function continue_label(settings_resolver $settings, next_step $nextstep): string {
        $label = trim($settings->get('continuelabel'));
        if ($label !== '') {
            return $label;
        }

        if ($nextstep->name === '') {
            return get_string('default_continuelabel', constants::PLUGIN);
        }

        return get_string('label_continueto', constants::PLUGIN, $nextstep->name);
    }

    /**
     * Resolve one message through the override, setting, language string chain.
     *
     * @param settings_resolver $settings Effective settings for the course.
     * @param string $name Setting key holding the customised text.
     * @param string $defaultkey Language string used when the setting is empty.
     * @return string The resolved message, before formatting.
     */
    protected function resolve_message(settings_resolver $settings, string $name, string $defaultkey): string {
        $value = trim($settings->get($name));

        return $value !== '' ? $value : get_string($defaultkey, constants::PLUGIN);
    }

    /**
     * Build the sentence describing how much of the course remains.
     *
     * @param progress_info $progress The learner's progress description.
     * @return string The progress sentence, or an empty string when unavailable.
     */
    protected function build_progress_line(progress_info $progress): string {
        if (!$progress->available || $progress->unittotal <= 0) {
            return '';
        }

        $remaining = max(0, $progress->unittotal - $progress->unitscompleted);

        if ($remaining === 0) {
            return get_string('progress_allcomplete', constants::PLUGIN);
        }

        return get_string('progress_line', constants::PLUGIN, (object) [
            'completed' => $progress->unitscompleted,
            'total'     => $progress->unittotal,
            'remaining' => $remaining,
        ]);
    }

    /**
     * Build the optional study advice shown after an unsuccessful attempt.
     *
     * @param settings_resolver $settings Effective settings for the course.
     * @param cm_info|null $previous The closest preceding activity.
     * @return string The advice, or an empty string when none applies.
     */
    protected function build_advice_line(settings_resolver $settings, ?cm_info $previous): string {
        $custom = trim($settings->get('studyadvice'));
        if ($custom !== '') {
            return $custom;
        }

        if ($previous === null) {
            return get_string('default_studyadvice', constants::PLUGIN);
        }

        return get_string(
            'advice_reviewnamed',
            constants::PLUGIN,
            format_string($previous->name, true, ['context' => $previous->context])
        );
    }

    /**
     * Build the URL of a configured file, preferring the course level copy.
     *
     * @param stdClass $course Course the quiz belongs to.
     * @param string $filearea File area holding the file.
     * @return string|null The file URL, or null when no file is configured.
     */
    protected function file_url(stdClass $course, string $filearea): ?string {
        $fs = get_file_storage();
        $contexts = [context_course::instance((int) $course->id), context_system::instance()];

        foreach ($contexts as $context) {
            $files = $fs->get_area_files($context->id, constants::PLUGIN, $filearea, 0, 'itemid', false);
            if (empty($files)) {
                continue;
            }

            $file = reset($files);

            return moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                null,
                $file->get_filepath(),
                $file->get_filename()
            )->out(false);
        }

        return null;
    }

    /**
     * Determine whether a badge is already present in a list.
     *
     * @param \local_learningjourney\local\model\badge_info[] $badges Badges already collected.
     * @param int $badgeid Badge identifier to look for.
     * @return bool True when the badge is already present.
     */
    protected function contains_badge(array $badges, int $badgeid): bool {
        foreach ($badges as $badge) {
            if ($badge->id === $badgeid) {
                return true;
            }
        }

        return false;
    }
}
