# Developer documentation

## Layering

The plugin is strictly layered and dependencies point one way only.

| Layer | Namespace | Rule |
| --- | --- | --- |
| Interception | `event\observer`, `hook` | No output, no database writes |
| Domain | `local` | Pure, side effect free, no `$PAGE` or `$OUTPUT` |
| Models | `local\model` | Immutable value objects with no dependencies |
| Presentation | `output` | No data access; templates hold all markup |

## Interception mechanism

1. `\mod_quiz\event\attempt_submitted` fires. The observer applies its guards
   and writes a token to the session cache. It never writes to the database.
2. On the next request, `\core\hook\output\before_http_headers` dispatches to
   `hook\output_callbacks`, which reads the token, consumes it and redirects.

The token is consumed on read and carries a two minute lifetime, so a refresh
cannot loop and a stale token cannot divert an unrelated page.

Should the hook be removed in a future Moodle release, the same logic is
reachable from a `local_learningjourney_before_http_headers()` function in
`lib.php`; the listener body is three lines.

## Settings register

`settings_resolver::all_definitions()` is the single declaration of every
setting. `settings.php`, `form\course_settings_form` and the backup handler all
read from it. Adding a setting means one entry in the register plus two
language strings — never a schema change.

## Volatile core API watch list

* `classes/local/quiz_adapter.php` — the only file referencing mod_quiz.
* `classes/local/next_activity_finder.php` — course and modinfo APIs.
* `classes/hook/output_callbacks.php` — the output hook contract.
* `lib.php` — the course navigation callback.

## Performance contracts

Measured on Moodle 4.5.12 with PostgreSQL 16:

* A page that is not a post submission request costs **zero** database queries;
  the interception hook exits on a session cache read.
* A warm result build on the learner's own page costs **13 database reads and no
  writes**, and that figure is flat with respect to course size.
* The plugin issues six core API calls; the Grades API (6 reads) and the quiz
  access manager (3 reads) expand those into most of the total. The earlier
  estimate of six queries counted plugin API calls, not the SQL core issues on
  their behalf.
* The observer performs no database writes.

These are asserted in the test suite rather than left to review.

### Known cost on the staff view

`completion_info::get_data()` and `core_completion\progress` only preload for the
user who is logged in. When staff open another learner's result, core issues
per-activity queries that the plugin cannot preload without reimplementing the
availability engine, which the architecture forbids. The plugin's own two N+1
paths were removed by bulk loading completion for other users; the remainder is
core behaviour and affects only the staff view, not learners.

## Privacy

The plugin stores no personal data in the database. `local_learningjourney_setting`
holds course configuration only, which is why it is not declared in the privacy
metadata. A test asserts that the table has no user identifying column, so any
future change that introduces user data will fail until the provider is updated.

## Coding standards

Moodle coding style, PSR-12 formatting where the two agree, full PHPDoc, typed
signatures, AMD modules only for JavaScript, and no deprecated APIs. Run
`moodle-plugin-ci` locally before submitting a change.

## Testing

```
vendor/bin/phpunit --testsuite local_learningjourney_testsuite
vendor/bin/behat --tags=@local_learningjourney
```

## Verification status

The following are verified automatically and must stay green:

* `grunt amd` regenerates `amd/build/` from `amd/src/`; the build output is
  committed and must never be hand edited.
* `grunt eslint` and `grunt stylelint` pass with no errors.
* Every Mustache template parses, documents its full context, and ships a valid
  JSON example.
* Every language string is present in both packs, and none is unused.

PHPUnit and Behat require a working Moodle site with a database. They are run by
the continuous integration workflow rather than locally by default.
