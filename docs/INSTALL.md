# Installation guide

## Requirements

* Moodle 4.5 or later
* PHP 8.2 or later
* A database supported by Moodle (PostgreSQL and MariaDB are covered by CI)

No Composer packages, external libraries or CDN resources are required.

## Install from a downloaded archive

1. Extract the archive so that its contents sit at `local/learningjourney`
   inside your Moodle directory. The folder must be named `learningjourney`.
2. Sign in as a site administrator and visit
   *Site administration → Notifications*.
3. Confirm the database update. One table, `local_learningjourney_setting`, is
   created.
4. Review the default configuration at *Site administration → Plugins →
   Local plugins → Learning Journey*.

## Install from the command line

```
php admin/cli/upgrade.php
php admin/cli/purge_caches.php
```

## Verifying the installation

* The plugin appears under *Site administration → Plugins → Plugins overview*.
* Five settings pages are listed under *Local plugins → Learning Journey*.
* Teachers with the manage capability see a *Learning Journey* item in the
  course secondary navigation.

## After installing

The plugin is enabled for all courses by default and requires no further
configuration. Individual courses can opt out from the course level settings
page.
