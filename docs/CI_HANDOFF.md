# Continuous integration handoff

This document describes what a real CI runner must provide, exactly which
commands it runs, and the gate that must be green before Learning Journey can be
called production ready.

It is written for the workflow already committed at
`.github/workflows/ci.yml`; the commands below are taken from that file rather
than invented.

## Environment required

| Requirement | Value |
| --- | --- |
| Moodle | 4.5.x (`MOODLE_405_STABLE`); `main` is also built as an early warning |
| PHP | 8.2 and 8.3 |
| PHP extensions | pgsql, mysqli, gd, zip, soap, intl (plus PHP defaults) |
| Database | PostgreSQL 14 and MariaDB 10.11, as GitHub service containers |
| Browser | Chrome, supplied by `moodle-plugin-ci behat --profile chrome` |
| Node and npm | Installed by `moodle-plugin-ci install` from Moodle's `.nvmrc` |
| Composer | Used once, to install `moodlehq/moodle-plugin-ci ^4` |
| Locale | `en_AU.UTF-8`, generated during setup |
| `max_input_vars` | 5000 |

`moodle-plugin-ci install` checks out Moodle, creates the database, writes
`config.php`, initialises the PHPUnit and Behat environments, and places the
plugin at `local/learningjourney`. Nothing else needs to place the plugin by
hand.

## Commands the workflow runs

| Check | Command |
| --- | --- |
| Setup | `moodle-plugin-ci install --plugin ./plugin --db-host=127.0.0.1` |
| PHP syntax | `moodle-plugin-ci phplint` |
| Mess detector | `moodle-plugin-ci phpmd` |
| Coding standards | `moodle-plugin-ci phpcs --max-warnings 0` |
| PHPDoc | `moodle-plugin-ci phpdoc --max-warnings 0` |
| Plugin validation | `moodle-plugin-ci validate` |
| Upgrade savepoints | `moodle-plugin-ci savepoints` |
| Mustache lint | `moodle-plugin-ci mustache` |
| Grunt, ESLint, stylelint, AMD build | `moodle-plugin-ci grunt --max-lint-warnings 0` |
| PHPUnit | `moodle-plugin-ci phpunit --fail-on-warning` |
| Behat | `moodle-plugin-ci behat --profile chrome` |
| Asset budgets | inline: `styles.css` <= 12288 bytes, `amd/build/*.min.js` <= 8192 bytes |
| Right to left audit | inline: no physical `left`/`right` CSS properties |

The `main` branch leg of the matrix is marked `continue-on-error`, because the
plugin declares `$plugin->supported = [405, 500]` and `validate` legitimately
objects once `main` moves beyond Moodle 5.0. It is an early warning about
upstream change, not a release gate.

## Running the same checks locally

```
moodle-plugin-ci install --plugin /path/to/local_learningjourney
moodle-plugin-ci phpcs --max-warnings 0
moodle-plugin-ci phpunit --fail-on-warning
moodle-plugin-ci behat --profile chrome
```

To run only this plugin's PHPUnit tests inside a configured Moodle tree:

```
vendor/bin/phpunit --filter local_learningjourney
```

## What the tests cover

* 11 PHPUnit classes, 70 test methods, in `tests/`. The shared fixture is the
  abstract `local_learningjourney\tests\journey_testcase` in `tests/classes/`,
  which Moodle autoloads through the `<component>/tests/classes` mapping.
* 12 Behat features in `tests/behat/`, plus the page resolver
  `behat_local_learningjourney`, which backs the
  `I am on the "<id>" "local_learningjourney > Result" page` step.

## Release gate

Learning Journey must not be called production ready until every row is green.

| Gate | Owner |
| --- | --- |
| PHPUnit green | CI |
| Behat green | CI |
| Coding standards, PHPDoc, Mustache, Grunt green | CI |
| Clean installation verified on a real 4.5 site | Human |
| Upgrade from the previous release verified | Human |
| Backup, restore and course duplication verified | Human |
| Native speaker Arabic review complete | Human |
| Accessibility review, including an axe audit | Human |
| Right to left visual review | Human |
| Mobile web rendering reviewed at phone, tablet and desktop widths | Human |

Verification performed outside CI, on Moodle 4.5.12 with PostgreSQL 16, is
recorded in `CHANGELOG.md` and `docs/DEVELOPER.md`. That evidence came from a
command line integration harness, not from PHPUnit, and does not substitute for
the two CI rows above.
