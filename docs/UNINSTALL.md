# Uninstall guide

## Removing the plugin

1. Go to *Site administration → Plugins → Plugins overview*.
2. Find **Learning Journey** in the local plugins list and choose *Uninstall*.
3. Confirm. Moodle drops `local_learningjourney_setting` and removes the
   plugin's configuration values.
4. Delete the `local/learningjourney` directory from the server if Moodle does
   not remove it for you.
5. Purge caches.

## What is left behind

Nothing that affects learners. The plugin never modifies core files, never
writes to core tables and stores no personal data, so removing it restores the
standard Moodle quiz review behaviour immediately.

Files uploaded through the plugin's background and sound settings are removed
with the plugin's configuration. Course backups taken while the plugin was
installed remain restorable; the Learning Journey section is simply skipped
when the plugin is absent.

## Temporarily disabling instead

If you only want to stop the diversion, turn off *Enable Learning Journey* on
the General settings page, or disable it for individual courses. This is
reversible and requires no uninstall.
