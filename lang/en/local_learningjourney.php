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

/**
 * English language strings for the Learning Journey plugin.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['advice_reviewnamed'] = 'You may benefit from reviewing {$a} before attempting the quiz again.';
$string['aria_countdown'] = 'Time remaining before you are moved to the next activity';
$string['aria_progressbar'] = 'Course completion';
$string['aria_stars'] = '{$a->earned} out of {$a->total} stars';
$string['countdown_remaining'] = 'Continuing in {$a} seconds';
$string['coursesettings'] = 'Learning Journey';
$string['coursesettings_help'] = 'Each setting below is paired with a "Use site default" checkbox. Clear the checkbox to change the setting for this course only; tick it again to remove the override entirely.';
$string['coursesettings_intro'] = 'These settings apply to {$a} only. Anything left as the site default will follow the site wide configuration.';
$string['default_continuelabel'] = 'Continue learning';
$string['default_coursecomplete'] = 'You have successfully completed this course.';
$string['default_failmessage'] = 'Every attempt helps you improve. Review the lesson and try again.';
$string['default_failtitle'] = 'Keep going';
$string['default_islamicencouragement'] = 'May Allah increase you in knowledge. Success comes through persistence.';
$string['default_islamicsuccess'] = 'May Allah increase you in beneficial knowledge.';
$string['default_pendingmessage'] = 'Your answers have been submitted and are being reviewed. Your result will appear once marking is complete.';
$string['default_pendingtitle'] = 'Answers received';
$string['default_studyadvice'] = 'You may benefit from reviewing the previous lesson before attempting the quiz again.';
$string['default_successmessage'] = 'You have passed this quiz. Keep progressing.';
$string['default_successtitle'] = 'Congratulations!';
$string['error_attemptnotfound'] = 'That quiz attempt could not be found. It may have been deleted, or the link may be incorrect.';
$string['error_invalidcolour'] = 'Enter a colour as a three or six digit hexadecimal value, for example #1d6f42.';
$string['error_notinteger'] = 'Enter a whole number.';
$string['error_outofrange'] = 'Enter a whole number between {$a->min} and {$a->max}.';
$string['error_redirectdelay'] = 'The countdown must be at least {$a} seconds so that learners have time to read their result and cancel it.';
$string['error_thresholdcount'] = 'Enter exactly {$a} thresholds, separated by commas.';
$string['error_thresholdnumeric'] = 'Every threshold must be a whole number.';
$string['error_thresholdorder'] = 'Thresholds must ascend and must not exceed 100.';
$string['iconset_emoji'] = 'Emoji';
$string['iconset_fontawesome'] = 'Theme icons';
$string['iconset_none'] = 'No icons';
$string['label_achievement'] = 'Achievement unlocked';
$string['label_attempt'] = 'Attempt';
$string['label_attemptsremaining'] = 'Attempts remaining';
$string['label_continuestudying'] = 'Continue studying';
$string['label_continueto'] = 'Continue to {$a}';
$string['label_failed'] = 'Not yet passed';
$string['label_fallbackapplied'] = 'The site default pass mark was applied to this quiz.';
$string['label_finalscore'] = 'Final score';
$string['label_nomark'] = 'No pass mark set';
$string['label_overallgrade'] = 'Overall quiz grade';
$string['label_passed'] = 'Passed';
$string['label_passinggrade'] = 'Pass mark';
$string['label_pending'] = 'Awaiting marking';
$string['label_percentage'] = 'Percentage';
$string['label_percentcomplete'] = '{$a}% complete';
$string['label_playsound'] = 'Play sound';
$string['label_returntocourse'] = 'Return to course';
$string['label_reviewlesson'] = 'Review lesson';
$string['label_reviewquiz'] = 'Review quiz';
$string['label_status'] = 'Status';
$string['label_stayonpage'] = 'Stay on this page';
$string['label_timetaken'] = 'Time taken';
$string['label_trophy'] = 'Trophy awarded';
$string['label_tryagain'] = 'Try again';
$string['layout_focused'] = 'Focused layout without secondary navigation';
$string['layout_standard'] = 'Standard page layout';
$string['learningjourney:manage'] = 'Configure Learning Journey for a course';
$string['learningjourney:viewothers'] = 'View another learner\'s Learning Journey result page';
$string['mobile_nodata'] = 'There is no Learning Journey information to show yet.';
$string['mobile_viewprogress'] = 'Learning Journey';
$string['pluginname'] = 'Learning Journey';
$string['privacy:metadata'] = 'The Learning Journey plugin stores no personal data in the database. Course level settings are configuration rather than user data.';
$string['privacy:preference:mute'] = 'Whether the learner has chosen to mute the optional celebration sound.';
$string['privacy:preference:mute:off'] = 'The celebration sound is not muted.';
$string['privacy:preference:mute:on'] = 'The celebration sound is muted.';
$string['progress_allcomplete'] = 'You have completed every lesson in this course.';
$string['progress_line'] = 'You have completed {$a->completed} of {$a->total} lessons. Only {$a->remaining} remaining. Keep up the excellent work.';
$string['report_course'] = 'Course';
$string['report_noquizzes'] = 'Every quiz on this site has a pass mark configured.';
$string['report_quiz'] = 'Quiz';
$string['report_title'] = 'Quizzes without a pass mark';
$string['resulttitle'] = 'Your result';
$string['setting_autoredirect'] = 'Automatic redirect';
$string['setting_autoredirect_desc'] = 'Move the learner on automatically after a visible, cancellable countdown. Disabled by default.';
$string['setting_backgroundcolour'] = 'Background colour';
$string['setting_backgroundcolour_desc'] = 'Background colour of the result panel.';
$string['setting_backgroundimage'] = 'Background image';
$string['setting_backgroundimage_desc'] = 'An optional decorative background image for the result panel.';
$string['setting_buttoncolour'] = 'Button colour';
$string['setting_buttoncolour_desc'] = 'Background colour of the primary button.';
$string['setting_buttontextcolour'] = 'Button text colour';
$string['setting_buttontextcolour_desc'] = 'Text colour of the primary button. Choose a value that contrasts well with the button colour.';
$string['setting_continuelabel'] = 'Continue button label';
$string['setting_continuelabel_desc'] = 'Overrides the label of the main continue button. Leave empty to name the next activity.';
$string['setting_coursecompletemessage'] = 'Course completed message';
$string['setting_coursecompletemessage_desc'] = 'Message shown when no further activity remains in the course.';
$string['setting_effectbadge'] = 'Achievement badge';
$string['setting_effectbadge_desc'] = 'Show a decorative achievement badge when no Moodle badge has been issued.';
$string['setting_effectconfetti'] = 'Confetti';
$string['setting_effectconfetti_desc'] = 'Show a short confetti animation after a successful attempt.';
$string['setting_effectfireworks'] = 'Fireworks';
$string['setting_effectfireworks_desc'] = 'Show a short fireworks animation after a successful attempt.';
$string['setting_effectsound'] = 'Applause sound';
$string['setting_effectsound_desc'] = 'Offer a play control for an applause sound. Sound never plays automatically.';
$string['setting_effectstars'] = 'Star animation';
$string['setting_effectstars_desc'] = 'Animate the star rating as it appears.';
$string['setting_effecttrophy'] = 'Trophy';
$string['setting_effecttrophy_desc'] = 'Show a trophy alongside the success message.';
$string['setting_enabled'] = 'Enable Learning Journey';
$string['setting_enabled_desc'] = 'When enabled, learners see the Learning Journey page immediately after submitting a quiz attempt.';
$string['setting_failmessage'] = 'Encouragement message';
$string['setting_failmessage_desc'] = 'Message shown to a learner who has not yet reached the pass mark. Leave empty to use the translated default.';
$string['setting_failtitle'] = 'Encouragement heading';
$string['setting_failtitle_desc'] = 'Heading shown to a learner who has not yet reached the pass mark. Leave empty to use the translated default.';
$string['setting_fallbackgradepass'] = 'Fallback pass mark';
$string['setting_fallbackgradepass_desc'] = 'The percentage applied when a quiz has no pass grade of its own.';
$string['setting_iconset'] = 'Icon style';
$string['setting_iconset_desc'] = 'Choose which style of icon is used on the result page.';
$string['setting_islamicencouragement'] = 'Additional encouragement message';
$string['setting_islamicencouragement_desc'] = 'An optional supplementary line shown beneath the encouragement message.';
$string['setting_islamicsuccess'] = 'Additional success message';
$string['setting_islamicsuccess_desc'] = 'An optional supplementary line shown beneath the success message.';
$string['setting_layout'] = 'Page layout';
$string['setting_layout_desc'] = 'Choose whether the result page keeps the standard course navigation or uses a focused layout.';
$string['setting_manualbadgeid'] = 'Manual badge to award';
$string['setting_manualbadgeid_desc'] = 'Identifier of a manual badge to issue on a successful attempt. Leave as zero to let the Moodle badge system handle all awards.';
$string['setting_progressbarcolour'] = 'Progress bar colour';
$string['setting_progressbarcolour_desc'] = 'Fill colour of the progress bar.';
$string['setting_progressbgcolour'] = 'Progress bar track colour';
$string['setting_progressbgcolour_desc'] = 'Colour of the unfilled part of the progress bar.';
$string['setting_redirectdelay'] = 'Redirect delay';
$string['setting_redirectdelay_desc'] = 'How long the countdown runs before the learner is moved on. Ten seconds is the shortest permitted delay.';
$string['setting_retrylabel'] = 'Try again button label';
$string['setting_retrylabel_desc'] = 'Overrides the label of the try again button.';
$string['setting_showattempt'] = 'Show attempt number';
$string['setting_showattempt_desc'] = 'Display which attempt this was, and how many remain.';
$string['setting_showbadges'] = 'Show badges';
$string['setting_showbadges_desc'] = 'Display badges the learner has earned in this course.';
$string['setting_showcoursecompletion'] = 'Show course completion';
$string['setting_showcoursecompletion_desc'] = 'Display the overall course completion percentage.';
$string['setting_showgradepass'] = 'Show pass mark';
$string['setting_showgradepass_desc'] = 'Display the pass mark that was applied.';
$string['setting_showpercentage'] = 'Show percentage';
$string['setting_showpercentage_desc'] = 'Display the percentage achieved on the attempt.';
$string['setting_showprogress'] = 'Show progress bar';
$string['setting_showprogress_desc'] = 'Display the course progress bar. Turning this off also skips its calculation.';
$string['setting_showreviewlink'] = 'Show review link';
$string['setting_showreviewlink_desc'] = 'Offer a link to the standard Moodle quiz review page when review is permitted.';
$string['setting_showscore'] = 'Show final score';
$string['setting_showscore_desc'] = 'Display the score achieved on the attempt.';
$string['setting_showstars'] = 'Show star rating';
$string['setting_showstars_desc'] = 'Display the star rating earned by the learner.';
$string['setting_showstatus'] = 'Show pass or fail status';
$string['setting_showstatus_desc'] = 'Display the outcome of the attempt as a status card.';
$string['setting_showtimetaken'] = 'Show time taken';
$string['setting_showtimetaken_desc'] = 'Display how long the learner spent on the attempt.';
$string['setting_soundfile'] = 'Applause sound file';
$string['setting_soundfile_desc'] = 'Upload the sound file to be offered. No sound is supplied with the plugin.';
$string['setting_starthresholds'] = 'Star thresholds';
$string['setting_starthresholds_desc'] = 'Five ascending percentages, separated by commas, at which each additional star is earned.';
$string['setting_studyadvice'] = 'Study advice';
$string['setting_studyadvice_desc'] = 'Optional advice offered after an unsuccessful attempt.';
$string['setting_successmessage'] = 'Success message';
$string['setting_successmessage_desc'] = 'Message shown to a learner who reaches the pass mark. Leave empty to use the translated default.';
$string['setting_successtitle'] = 'Success heading';
$string['setting_successtitle_desc'] = 'Heading shown to a learner who reaches the pass mark. Leave empty to use the translated default.';
$string['setting_themecolour'] = 'Theme colour';
$string['setting_themecolour_desc'] = 'Primary colour used for headings and highlights.';
$string['setting_unitmode'] = 'What counts as a lesson';
$string['setting_unitmode_desc'] = 'Choose how progress such as "Lesson 1 of 5" is counted.';
$string['setting_usefallbackgradepass'] = 'Use a fallback pass mark';
$string['setting_usefallbackgradepass_desc'] = 'Apply a site wide pass mark to quizzes that have no pass grade set in the gradebook.';
$string['settingspage_appearance'] = 'Appearance';
$string['settingspage_appearance_intro'] = 'Colours, background and icon style for the result page. Colour values must be hexadecimal, and a warning appears if a chosen pair falls below the recommended contrast ratio.';
$string['settingspage_display'] = 'Display and scoring';
$string['settingspage_display_intro'] = 'Choose which parts of the result page a learner sees, and where each additional star is earned.';
$string['settingspage_effects'] = 'Effects';
$string['settingspage_effects_intro'] = 'Optional celebrations shown after a successful attempt. Every effect is suppressed automatically for learners who have asked for reduced motion, and sound never plays without a deliberate click.';
$string['settingspage_general'] = 'General';
$string['settingspage_general_intro'] = 'Controls when the Learning Journey page appears and how the pass mark is decided. The plugin works with no configuration at all; everything below has a sensible default.';
$string['settingspage_messages'] = 'Messages';
$string['settingspage_messages_intro'] = 'Every message left empty falls back to the translated default, so an untouched site is already correct in English and Arabic. Fill a field in only when you want to replace the shipped wording.';
$string['unitlabel_activity'] = 'Activity {$a->index} of {$a->total}';
$string['unitlabel_completed'] = '{$a->completed} of {$a->total} lessons completed';
$string['unitlabel_lesson'] = 'Lesson {$a->index} of {$a->total}';
$string['unitlabel_section'] = 'Lesson {$a->index} of {$a->total}';
$string['unitmode_activity'] = 'Completion tracked activity';
$string['unitmode_lesson'] = 'Lesson activity';
$string['unitmode_section'] = 'Course section';
$string['usesitedefault'] = 'Use site default';
$string['warning_lowcontrast'] = 'The colours chosen for {$a->name} give a contrast ratio of {$a->ratio} to 1, which is below the recommended 4.5 to 1.';
