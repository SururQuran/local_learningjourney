# Learning Journey (local_learningjourney)

Learning Journey replaces the ordinary Moodle quiz completion page with a
professional post-quiz experience. Immediately after a learner submits an
attempt, the plugin determines whether they passed and shows a full page
result with their score, star rating, course progress and a clear route to the
next activity.

* **Plugin type:** local
* **Component:** `local_learningjourney`
* **Requires:** Moodle 4.5 or later, PHP 8.2 or later
* **Licence:** GNU GPL v3 or later

## Design principles

* **No core modification.** The plugin observes `\mod_quiz\event\attempt_submitted`
  and diverts the following request from the `\core\hook\output\before_http_headers`
  hook. Nothing in Moodle core is patched or overridden.
* **Native APIs only.** Grades, completion, availability, course structure and
  badges all come from core APIs. The plugin composes and presents; it does not
  recalculate what Moodle already knows.
* **Lightweight by construction.** On a page that is not a post-submission
  request the plugin costs one session cache read and no database queries. The
  result page itself is budgeted at six queries, asserted in the test suite.
* **No dependencies.** No Composer packages, no external JavaScript or CSS
  frameworks, no CDN, no third-party libraries.

## What it does

* **Detects the submission** through `\mod_quiz\event\attempt_submitted` and
  diverts the following request from the output hook.
* **Determines the verdict** from the gradebook, applying the quiz pass grade,
  then the course level Learning Journey override, then the site default of 60%.
* **Withholds a verdict** while manually graded questions are outstanding, so a
  learner is never told they failed an unmarked attempt.
* **Reports the attempt**: score, percentage, pass mark, status, time taken,
  attempt number and attempts remaining.
* **Calculates progress** from core completion, counting lessons as course
  sections, completion tracked activities or lesson activities.
* **Finds the next activity** from course modinfo, honouring restrict access,
  availability conditions, activity completion and user visibility, and never
  disclosing why something is unavailable.
* **Offers the right actions only**: try again appears when the quiz access
  rules actually permit it, review quiz appears when the quiz review options
  permit it, and review lesson points at the preceding activity.
* **Displays badges** issued by Moodle's own badge system, falling back to a
  decorative achievement badge while course completion badges are still pending
  in cron.
* **Celebrates optionally**: confetti, stars, fireworks and an administrator
  supplied applause sound, all off unless enabled and all suppressed under
  reduced motion. The automatic redirect is disabled by default and always
  cancellable.

## Known platform limitation

The official Moodle Mobile App submits quiz attempts through web services and
renders its own interface, so the web diversion cannot apply inside the app.
The plugin therefore ships a course menu addon and a read-only web service
(`local_learningjourney_get_result`) so the journey remains reachable there,
and the web templates are fully responsive for mobile web and the app's
embedded browser.

## Documentation

See the `docs/` directory for installation, administration, configuration,
upgrade, uninstall, troubleshooting and developer guides.
