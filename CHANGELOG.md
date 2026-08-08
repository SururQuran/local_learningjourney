# Changelog

All notable changes to Learning Journey are documented in this file. The
format follows Keep a Changelog, and this project adheres to semantic
versioning.

## [1.0.0] - 2026-08-07

### Added

* Quiz submission detection through a guarded `attempt_submitted` observer and a
  one shot, session scoped handoff token consumed by the output hook.
* Grade resolution from the gradebook, with the pass mark taken from the quiz
  grade item, then the course override, then the site wide default.
* Pending manual grading detection, so no verdict is announced before marking is
  complete.
* Attempt reporting: attempts used, attempts remaining, attempt number and time
  taken, with retry availability decided by the mod_quiz access manager.
* Course and lesson progress from core completion aggregates, with the lesson
  unit configurable as course section, tracked activity or lesson activity.
* Next activity detection from course modinfo, honouring restrict access,
  availability conditions, activity completion and user visibility.
* Verdict specific actions for continue, try again, review lesson, review quiz,
  continue studying and return to course.
* Native badge display with a decorative achievement fallback, and an optional
  explicitly mapped manual badge award.
* Star rating, celebration effects and an accessible, cancellable countdown.
* Course override persistence, including background image and applause sound
  files stored per course.

### Fixed

* Colour settings are stored correctly. The colour picker override returned the
  wrong type for the parent contract, which meant every saved colour was written
  as "1" instead of the chosen value.
* The minimum countdown length is now enforced on save. The override targeted a
  method the duration setting never calls, so a delay below ten seconds was
  accepted and silently corrected only at display time.
* The badge class is referenced by its real namespaced name rather than the
  legacy global alias, which is scheduled for removal.
* The obsolete fourth argument to `completion_info::get_data()` was removed.
* A background image whose filename contains an apostrophe can no longer break
  out of the CSS `url()` token; the URL is now encoded server side.
* Three stylesheet rules corrected to satisfy Moodle's own stylelint
  configuration.

### Fixed (verified on Moodle 4.5.12 with PostgreSQL 16)

* Removed an N+1 query on the staff view. `completion_info::get_data()` and
  `core_completion\progress` only preload for the user who is logged in, so a
  teacher opening a learner's result triggered one query per activity. Completion
  is now bulk loaded for other users, and the percentage is derived from the same
  state. Measured on a 45 activity course: 97 reads before, 55 after.
* Corrected the documented performance contract. The learner's own result page
  costs 13 database reads and no writes, flat with respect to course size. The
  earlier figure of six counted plugin API calls rather than the SQL that core
  issues on their behalf.
* Resolved 174 Moodle coding standard violations and 16 PHPDoc checker findings,
  including missing member documentation on promoted constructor properties and
  generic array types the PHPDoc parser cannot read.

### User and administrator experience

* Outcome icons, decorative trophy, animated star reveal and a CSS driven
  progress fill, all suppressed under reduced motion.
* Result panel exposed as a labelled region with a single heading, decorative
  imagery hidden from assistive technology, 44 pixel minimum targets, visible
  focus rings and full keyboard operation without script.
* High contrast and forced colours support, responsive layout down to 320 pixels
  with stacked full width actions on small screens, and a print stylesheet.
* Right to left layout throughout using logical properties only, enforced by a
  continuous integration check.
* Introductions on all five administration pages, and an explanatory notice on
  the course override form.
* Validation at the point of entry for the pass mark range, redirect delay
  minimum, colour format and star thresholds, shared between the site settings
  and the course override form.
* Localised error page when a quiz attempt cannot be found.
* Richer mobile app view with a labelled progress bar.

### Infrastructure

* Plugin foundation for Moodle 4.5 and later: `version.php`, capabilities,
  install schema, cache definitions, event observer and hook registrations.
* Settings register declaring all forty five configurable values, driving five
  generated administration pages and the course override form.
* Sparse per course override table `local_learningjourney_setting`, indexed
  uniquely on course and setting name.
* Domain, model, output, external, form, admin, privacy and backup class
  structure with full PHPDoc and typed signatures.
* Mustache templates for the pass, fail, pending and mobile views, with
  supporting partials for score cards, stars, badges, progress and actions.
* AMD module scaffolding for celebration effects and the accessible countdown.
* Stylesheet using CSS custom properties and logical properties throughout for
  right to left support.
* English and Arabic language packs.
* Read-only external function `local_learningjourney_get_result` and the mobile
  app course addon.
* Initial PHPUnit suite covering colour handling, star bands, the settings
  register and the privacy provider, plus a Behat feature for the settings
  pages.
