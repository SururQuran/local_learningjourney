# Troubleshooting guide

## The result page does not appear after a submission

The plugin intervenes only in the narrow window immediately after a learner
submits their own attempt in a browser. The following are working as designed
and always show standard Moodle behaviour:

* Attempts closed by the overdue handling cron task.
* Attempts finished by a teacher on a learner's behalf.
* Attempts submitted through a web service, including the mobile app.
* Attempts made in preview mode by a user with `mod/quiz:preview`.

Otherwise, check that the plugin is enabled at both site and course level, and
that the learner is not a guest.

## The page shows no verdict

The quiz has no pass grade set in the gradebook and the fallback pass mark is
disabled. Either set a pass grade on the quiz, or enable *Use a fallback pass
mark*. The *Quizzes without a pass mark* report lists every affected quiz.

## The page says answers are being reviewed

The attempt contains manually graded questions that have not yet been marked.
The plugin deliberately withholds a verdict rather than announcing a false
failure. The result becomes available once marking is complete.

## A badge the learner earned is not shown

Course completion badges are issued by Moodle's completion cron task and may be
awarded a few minutes after the result page is displayed. The decorative
achievement badge is shown in the meantime. Confirm that cron is running
regularly.

## Two plugins redirect from the same request

The handoff token is consumed on first use and expires after two minutes, so
Learning Journey diverts at most once per submission and never competes for
unrelated requests. If another plugin redirects first, its redirect wins and
the Learning Journey token simply expires.

## The learner sees "That quiz attempt could not be found"

The attempt referenced by the link no longer exists, usually because the attempt
or its quiz was deleted, or because the link was edited by hand. The message is
deliberate: the plugin never reveals whether an unfamiliar attempt identifier
belongs to somebody else.

## Colours look wrong or unreadable

Colour values must be hexadecimal. An invalid stored value falls back to the
shipped default rather than breaking the page. Check the contrast warning shown
when saving the button colour.

## Right to left layout problems

The stylesheet uses logical properties throughout and inherits direction from
Moodle. If a custom theme forces `left` or `right` positioning on the plugin's
containers, override that rule in the theme rather than in the plugin.
