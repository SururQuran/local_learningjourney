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

namespace local_learningjourney\output;

use local_learningjourney\local\constants;
use local_learningjourney\local\model\grade_info;
use local_learningjourney\local\model\result_context;
use local_learningjourney\local\star_rating;
use renderable;
use renderer_base;
use templatable;

/**
 * Converts a result context into a flat, escaped template context.
 *
 * This class performs no data access. Every value it returns is either plain
 * text destined for escaped output, or has already been cleaned here before
 * being flagged for unescaped output.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class result_page implements renderable, templatable {
    /** @var result_context Immutable aggregate describing the result. */
    protected result_context $context;

    /**
     * Create a renderable result page.
     *
     * @param result_context $context Immutable aggregate describing the result.
     */
    public function __construct(result_context $context) {
        $this->context = $context;
    }

    /**
     * Return the Mustache template that renders this verdict.
     *
     * @return string Fully qualified template name.
     */
    public function get_template_name(): string {
        return match ($this->context->verdict) {
            constants::RESULT_PASS => 'local_learningjourney/result_pass',
            constants::RESULT_FAIL => 'local_learningjourney/result_fail',
            default => 'local_learningjourney/result_pending',
        };
    }

    /**
     * Return the AMD modules this page needs, keyed by module name.
     *
     * @return array<string, array<int, mixed>> Module names mapped to init arguments.
     */
    public function get_required_modules(): array {
        $modules = [];

        if ($this->context->is_pass() && $this->has_effects()) {
            $modules['local_learningjourney/celebrate'] = [$this->export_effects()];
        }

        $countdown = $this->export_countdown();
        if ($countdown !== null) {
            $modules['local_learningjourney/countdown'] = [$countdown['seconds'], $countdown['url']];
        }

        return $modules;
    }

    /**
     * Export the flat template context.
     *
     * @param renderer_base $output The renderer exporting this page.
     * @return array<string, mixed> Template context.
     */
    public function export_for_template(renderer_base $output): array {
        unset($output);

        $appearance = $this->context->appearance;
        $messages = $this->context->messages;
        $cards = $this->export_scorecards();
        $badges = $this->export_badges();
        $progress = $this->export_progress();
        $stars = $this->export_stars();
        $countdown = $this->export_countdown();
        $nextstep = $this->context->nextstep;

        $coursecomplete = $this->context->is_pass()
            && $nextstep->type === constants::NEXT_COURSE_COMPLETE
            && $messages->coursecompletetext !== '';

        return [
            'verdict'         => $this->context->verdict,
            'cssvars'         => $this->css_variables(),
            'title'           => $messages->title,
            'message'         => $this->format_message($messages->body),
            'islamic'         => $this->format_message($messages->islamicline),
            'hasislamic'      => trim($messages->islamicline) !== '',
            'advice'          => $this->format_message($messages->adviceline),
            'hasadvice'       => trim($messages->adviceline) !== '',
            'coursecomplete'  => $this->format_message($messages->coursecompletetext),
            'hascoursecomplete' => $coursecomplete,
            'activity'        => format_string($this->context->cm->name, true, [
                'context' => $this->context->cm->context,
            ]),
            'coursename'      => format_string($this->context->course->fullname, true, [
                'context' => $this->context->cm->context,
            ]),
            'scorecards'      => ['cards' => $cards],
            'hascards'        => !empty($cards),
            'starrating'      => $stars,
            'hasstars'        => $stars !== null,
            'badgeblock'      => ['badges' => $badges],
            'hasbadges'       => !empty($badges),
            'progress'        => $progress,
            'hasprogress'     => $progress !== null,
            'actions'         => $this->export_actions(),
            'hasactions'      => !empty($this->context->actions),
            'countdown'       => $countdown,
            'hascountdown'    => $countdown !== null,
            'hasbackground'   => $appearance->backgroundimageurl !== null,
            'hassound'        => $this->context->is_pass()
                && $appearance->has_effect('sound')
                && $appearance->soundurl !== null,
            'soundlabel'      => get_string('label_playsound', constants::PLUGIN),
            'hastrophy'       => $this->context->is_pass() && $appearance->has_effect('trophy'),
            'trophylabel'     => get_string('label_trophy', constants::PLUGIN),
            'iconstyle'       => $this->icon_style(),
            'showicon'        => $this->icon_style() !== 'none',
        ];
    }

    /**
     * Export the result cards in their fixed order.
     *
     * @return array<int, array<string, mixed>> Card descriptions.
     */
    protected function export_scorecards(): array {
        $appearance = $this->context->appearance;
        $grade = $this->context->grade;
        $attempt = $this->context->attempt;
        $cards = [];

        if ($appearance->shows('showscore') && $grade->formattedgrade !== '') {
            $cards[] = $this->card(
                get_string('label_finalscore', constants::PLUGIN),
                $grade->formattedgrade . ' / ' . $grade->formattedmax
            );
        }

        if ($appearance->shows('showpercentage') && $grade->percent !== null) {
            $cards[] = $this->card(
                get_string('label_percentage', constants::PLUGIN),
                $this->percent($grade->percent)
            );
        }

        if ($appearance->shows('showgradepass') && $grade->gradepasspercent !== null) {
            $cards[] = $this->card(
                get_string('label_passinggrade', constants::PLUGIN),
                $this->percent($grade->gradepasspercent),
                $grade->gradepasssource === grade_info::SOURCE_FALLBACK
                    ? get_string('label_fallbackapplied', constants::PLUGIN)
                    : ''
            );
        }

        if ($appearance->shows('showstatus')) {
            $cards[] = $this->card(
                get_string('label_status', constants::PLUGIN),
                $this->status_label()
            );
        }

        if ($appearance->shows('showtimetaken') && $attempt->timetaken > 0) {
            $cards[] = $this->card(
                get_string('label_timetaken', constants::PLUGIN),
                format_time($attempt->timetaken)
            );
        }

        if ($appearance->shows('showattempt')) {
            $sublabel = '';
            if ($attempt->attemptsremaining !== null) {
                $sublabel = get_string('label_attemptsremaining', constants::PLUGIN)
                    . ': ' . $attempt->attemptsremaining;
            }

            $cards[] = $this->card(
                get_string('label_attempt', constants::PLUGIN),
                (string) $attempt->attemptnumber,
                $sublabel
            );
        }

        if ($grade->hasoverallvariance && $grade->overallpercent !== null) {
            $cards[] = $this->card(
                get_string('label_overallgrade', constants::PLUGIN),
                $this->percent($grade->overallpercent)
            );
        }

        return $cards;
    }

    /**
     * Return the icon style the page should render.
     *
     * @return string One of emoji, fontawesome or none.
     */
    protected function icon_style(): string {
        return $this->context->appearance->iconset;
    }

    /**
     * Export the star rating block.
     *
     * @return array<string, mixed>|null The star block, or null when not shown.
     */
    protected function export_stars(): ?array {
        if (!$this->context->is_pass() || !$this->context->appearance->shows('showstars')) {
            return null;
        }

        $earned = $this->context->stars;
        $total = star_rating::MAX_STARS;
        $list = [];

        for ($index = 1; $index <= $total; $index++) {
            $list[] = ['earned' => $index <= $earned];
        }

        return [
            'starlist'  => $list,
            'arialabel' => get_string('aria_stars', constants::PLUGIN, (object) [
                'earned' => $earned,
                'total'  => $total,
            ]),
        ];
    }

    /**
     * Export the badge block.
     *
     * @return array<int, array<string, mixed>> Badge descriptions.
     */
    protected function export_badges(): array {
        $appearance = $this->context->appearance;

        if (!$this->context->is_pass() || !$appearance->shows('showbadges')) {
            return [];
        }

        $badges = [];
        foreach ($this->context->badges as $badge) {
            $badges[] = [
                'name'     => $badge->name,
                'imageurl' => $badge->imageurl === null ? '' : $badge->imageurl->out(false),
                'isreal'   => $badge->isreal,
            ];
        }

        if (empty($badges) && $appearance->has_effect('badge')) {
            $badges[] = [
                'name'     => get_string('label_achievement', constants::PLUGIN),
                'imageurl' => '',
                'isreal'   => false,
            ];
        }

        return $badges;
    }

    /**
     * Export the progress block.
     *
     * @return array<string, mixed>|null The progress block, or null when not shown.
     */
    protected function export_progress(): ?array {
        $progress = $this->context->progress;
        $appearance = $this->context->appearance;

        if (!$progress->available || !$appearance->shows('showprogress')) {
            return null;
        }

        $percent = $appearance->shows('showcoursecompletion') && $progress->coursepercent !== null
            ? (int) round($progress->coursepercent)
            : (int) round($progress->unittotal > 0
                ? ($progress->unitscompleted / $progress->unittotal) * 100
                : 0);

        $percent = max(0, min(100, $percent));

        return [
            'percent'      => $percent,
            'percentlabel' => get_string('label_percentcomplete', constants::PLUGIN, $percent),
            'unitlabel'    => $this->unit_label(),
            'hasunitlabel' => $this->unit_label() !== '',
            'progressline' => $this->context->messages->progressline,
            'hasline'      => $this->context->messages->progressline !== '',
            'arialabel'    => get_string('aria_progressbar', constants::PLUGIN),
        ];
    }

    /**
     * Export the countdown block.
     *
     * @return array<string, mixed>|null The countdown block, or null when disabled.
     */
    protected function export_countdown(): ?array {
        $appearance = $this->context->appearance;
        $nextstep = $this->context->nextstep;

        if (!$appearance->autoredirect || !$this->context->is_pass() || !$nextstep->is_available()) {
            return null;
        }

        return [
            'seconds'   => $appearance->redirectdelay,
            'url'       => $nextstep->url->out(false),
            'staylabel' => get_string('label_stayonpage', constants::PLUGIN),
            'arialabel' => get_string('aria_countdown', constants::PLUGIN),
            'initial'   => get_string('countdown_remaining', constants::PLUGIN, $appearance->redirectdelay),
        ];
    }

    /**
     * Export the celebration effect configuration.
     *
     * @return array<string, mixed> Effect configuration for the AMD module.
     */
    protected function export_effects(): array {
        $appearance = $this->context->appearance;

        return [
            'confetti'  => $appearance->has_effect('confetti'),
            'stars'     => $appearance->has_effect('stars'),
            'trophy'    => $appearance->has_effect('trophy'),
            'fireworks' => $appearance->has_effect('fireworks'),
            'sound'     => $appearance->has_effect('sound') && $appearance->soundurl !== null,
            'soundurl'  => $appearance->soundurl ?? '',
            'colour'    => $appearance->themecolour,
        ];
    }

    /**
     * Export the ordered page actions.
     *
     * @return array<int, array<string, mixed>> Action descriptions.
     */
    protected function export_actions(): array {
        return array_values($this->context->actions);
    }

    /**
     * Determine whether any celebration effect is enabled.
     *
     * @return bool True when at least one effect should run.
     */
    protected function has_effects(): bool {
        $effects = $this->export_effects();
        unset($effects['soundurl'], $effects['colour']);

        return in_array(true, $effects, true);
    }

    /**
     * Build one result card.
     *
     * @param string $label Card label.
     * @param string $value Card value.
     * @param string $sublabel Optional supplementary text.
     * @return array<string, mixed> The card description.
     */
    protected function card(string $label, string $value, string $sublabel = ''): array {
        return [
            'label'       => $label,
            'value'       => $value,
            'sublabel'    => $sublabel,
            'hassublabel' => $sublabel !== '',
        ];
    }

    /**
     * Return the localised status label for the verdict.
     *
     * @return string The status label.
     */
    protected function status_label(): string {
        return match ($this->context->verdict) {
            constants::RESULT_PASS    => get_string('label_passed', constants::PLUGIN),
            constants::RESULT_FAIL    => get_string('label_failed', constants::PLUGIN),
            constants::RESULT_PENDING => get_string('label_pending', constants::PLUGIN),
            default                   => get_string('label_nomark', constants::PLUGIN),
        };
    }

    /**
     * Return the localised label describing the learner's position in the course.
     *
     * @return string The unit label.
     */
    protected function unit_label(): string {
        $progress = $this->context->progress;

        if ($progress->unitindex <= 0 || $progress->unittotal <= 0) {
            return '';
        }

        $key = match ($progress->unitmode) {
            constants::UNIT_ACTIVITY => 'unitlabel_activity',
            constants::UNIT_LESSON   => 'unitlabel_lesson',
            default                  => 'unitlabel_section',
        };

        return get_string($key, constants::PLUGIN, (object) [
            'index' => $progress->unitindex,
            'total' => $progress->unittotal,
        ]);
    }

    /**
     * Format a percentage for display.
     *
     * @param float $value The percentage.
     * @return string The formatted percentage.
     */
    protected function percent(float $value): string {
        return format_float($value, 1, true, true) . '%';
    }

    /**
     * Clean and format an administrator supplied message for output.
     *
     * @param string $raw The stored message.
     * @return string Safe HTML suitable for unescaped output.
     */
    protected function format_message(string $raw): string {
        if (trim($raw) === '') {
            return '';
        }

        return format_text(clean_text($raw, FORMAT_HTML), FORMAT_HTML, [
            'context' => $this->context->cm->context,
            'filter'  => true,
        ]);
    }

    /**
     * Build the validated CSS custom property declarations for the page.
     *
     * @return string A safe custom property declaration list.
     */
    protected function css_variables(): string {
        $appearance = $this->context->appearance;
        $map = [
            '--ljy-theme'         => $appearance->themecolour,
            '--ljy-button-bg'     => $appearance->buttoncolour,
            '--ljy-button-fg'     => $appearance->buttontextcolour,
            '--ljy-progress-fill' => $appearance->progressbarcolour,
            '--ljy-progress-bg'   => $appearance->progressbgcolour,
            '--ljy-bg'            => $appearance->backgroundcolour,
        ];

        $declarations = [];
        foreach ($map as $property => $value) {
            $declarations[] = $property . ':' . $value;
        }

        if ($appearance->backgroundimageurl !== null) {
            $declarations[] = '--ljy-bg-image:url("' . $this->css_url($appearance->backgroundimageurl) . '")';
        }

        return implode(';', $declarations) . ';';
    }

    /**
     * Encode a URL so that it is safe inside a CSS url() token.
     *
     * Quotes, backslashes, parentheses and whitespace are percent encoded, so a
     * filename can never terminate the CSS string or the declaration.
     *
     * @param string $url The absolute URL to encode.
     * @return string The encoded URL.
     */
    protected function css_url(string $url): string {
        return str_replace(
            ['\\', '"', "'", '(', ')', ' ', "\n", "\r", ';'],
            ['%5C', '%22', '%27', '%28', '%29', '%20', '', '', '%3B'],
            $url
        );
    }
}
