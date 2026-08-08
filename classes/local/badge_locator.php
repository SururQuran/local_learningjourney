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

use context_course;
use context_system;
use local_learningjourney\local\model\badge_info;
use moodle_url;
use stdClass;
use Throwable;

/**
 * Read only discovery of badges to show on the result page.
 *
 * The Moodle badge system remains authoritative. This class never issues a
 * criteria driven badge, and issues a manual badge only when an administrator
 * has explicitly mapped one to the course.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class badge_locator {
    /** @var int Seconds of tolerance applied when matching recently issued badges. */
    protected const ISSUE_TOLERANCE = 60;

    /** @var stdClass Course whose badges are inspected. */
    protected stdClass $course;

    /** @var int Learner whose badges are inspected. */
    protected int $userid;

    /** @var settings_resolver Effective settings for the course. */
    protected settings_resolver $settings;

    /** @var stdClass[]|null Memoised badges held by the learner in this course. */
    protected ?array $badges = null;

    /**
     * Create a badge locator for one learner and one course.
     *
     * @param stdClass $course Course whose badges are inspected.
     * @param int $userid Learner whose badges are inspected.
     * @param settings_resolver $settings Effective settings for the course.
     */
    public function __construct(stdClass $course, int $userid, settings_resolver $settings) {
        $this->course = $course;
        $this->userid = $userid;
        $this->settings = $settings;
    }

    /**
     * Find badges issued to the learner in this course since a given time.
     *
     * @param int $since Unix timestamp from which badges are considered recent.
     * @return badge_info[] Badges to display, possibly empty.
     */
    public function find_recent(int $since): array {
        $threshold = max(0, $since - self::ISSUE_TOLERANCE);
        $found = [];

        foreach ($this->load_course_badges() as $badge) {
            if ((int) $badge->dateissued < $threshold) {
                continue;
            }

            $found[] = $this->to_model($badge);
        }

        return $found;
    }

    /**
     * Determine whether the learner holds any badge in this course.
     *
     * @return bool True when at least one badge has been issued.
     */
    public function has_any(): bool {
        return !empty($this->load_course_badges());
    }

    /**
     * Issue the manual badge an administrator has mapped to this course.
     *
     * @return badge_info|null The badge issued, or null when none applies.
     */
    public function award_mapped_badge(): ?badge_info {
        global $CFG;

        $badgeid = $this->settings->get_int('manualbadgeid');
        if ($badgeid <= 0 || empty($CFG->enablebadges)) {
            return null;
        }

        try {
            require_once($CFG->libdir . '/badgeslib.php');

            $badge = new \core_badges\badge($badgeid);

            if (!$badge->is_active() || !$badge->has_manual_award_criteria()) {
                return null;
            }

            if (
                (int) $badge->type === BADGE_TYPE_COURSE
                    && (int) $badge->courseid !== (int) $this->course->id
            ) {
                return null;
            }

            if ($badge->is_issued($this->userid)) {
                return null;
            }

            $badge->issue($this->userid, true);
            $this->badges = null;

            return new badge_info(
                id: (int) $badge->id,
                name: format_string($badge->name, true, ['context' => $this->badge_context($badge)]),
                description: format_text((string) $badge->description, FORMAT_HTML, [
                    'context' => $this->badge_context($badge),
                ]),
                imageurl: $this->badge_image_url($badge),
                dateissued: time(),
                isreal: true,
            );
        } catch (Throwable $e) {
            debugging('Learning Journey manual badge award failed: ' . $e->getMessage(), DEBUG_DEVELOPER);

            return null;
        }
    }

    /**
     * Load and memoise the badges the learner holds in this course.
     *
     * @return stdClass[] Badge records, newest first.
     */
    protected function load_course_badges(): array {
        global $CFG;

        if ($this->badges !== null) {
            return $this->badges;
        }

        $this->badges = [];

        if (empty($CFG->enablebadges)) {
            return $this->badges;
        }

        try {
            require_once($CFG->libdir . '/badgeslib.php');

            $this->badges = badges_get_user_badges($this->userid, (int) $this->course->id);
        } catch (Throwable $e) {
            debugging('Learning Journey badge lookup failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
            $this->badges = [];
        }

        return $this->badges;
    }

    /**
     * Convert a badge record into the immutable display model.
     *
     * @param stdClass $badge The badge record.
     * @return badge_info The badge description.
     */
    protected function to_model(stdClass $badge): badge_info {
        $context = $this->badge_context($badge);

        return new badge_info(
            id: (int) $badge->id,
            name: format_string($badge->name, true, ['context' => $context]),
            description: format_text((string) ($badge->description ?? ''), FORMAT_HTML, ['context' => $context]),
            imageurl: $this->badge_image_url($badge),
            dateissued: (int) $badge->dateissued,
            isreal: true,
        );
    }

    /**
     * Return the context a badge belongs to.
     *
     * @param stdClass $badge The badge record.
     * @return \context The badge context.
     */
    protected function badge_context(stdClass $badge) {
        if ((int) $badge->type === BADGE_TYPE_COURSE && !empty($badge->courseid)) {
            return context_course::instance((int) $badge->courseid);
        }

        return context_system::instance();
    }

    /**
     * Build the URL of a badge image.
     *
     * @param stdClass $badge The badge record.
     * @return moodle_url The badge image URL.
     */
    protected function badge_image_url(stdClass $badge): moodle_url {
        return moodle_url::make_pluginfile_url(
            $this->badge_context($badge)->id,
            'badges',
            'badgeimage',
            (int) $badge->id,
            '/',
            'f1',
            false
        );
    }
}
