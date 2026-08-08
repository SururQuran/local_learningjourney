# Upgrade guide

## Upgrading the plugin

1. Back up your Moodle database and `local/learningjourney` directory.
2. Replace the plugin directory with the new release.
3. Visit *Site administration → Notifications*, or run
   `php admin/cli/upgrade.php`.
4. Purge caches once the upgrade completes.

The course settings cache is purged automatically on every plugin upgrade.

## Schema stability

Learning Journey stores per course overrides in a sparse key/value table. New
settings therefore require no database change, which removes the most common
source of upgrade failure. A release that does need a schema step will say so
explicitly in `CHANGELOG.md`.

## Moodle version upgrades

`version.php` declares support for Moodle 4.5 through 5.0. Four files hold every
point of contact with volatile core APIs and are the first place to look after
a major Moodle upgrade:

* `classes/local/quiz_adapter.php` — mod_quiz classes
* `classes/local/next_activity_finder.php` — course and modinfo APIs
* `classes/hook/output_callbacks.php` — the output hook
* `lib.php` — the course navigation callback

## Removed settings

A setting withdrawn in a future release is ignored by the resolver for one
major version before its rows are deleted, so downgrading within that window
remains safe.
