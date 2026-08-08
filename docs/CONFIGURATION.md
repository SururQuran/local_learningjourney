# Configuration guide

## Site settings

All site settings live under *Site administration → Plugins → Local plugins →
Learning Journey* and are grouped across five pages. Each setting is declared
once in the plugin's settings register, which also generates the course
override form, so the two always stay in step.

## Course overrides

Teachers and managers holding `local/learningjourney:manage` see a *Learning
Journey* item in the course secondary navigation. Every setting on that page
pairs a **Use site default** checkbox with the value it controls. Clearing the
checkbox enables the field and records an override for that course only;
re-checking it removes the override entirely.

Only the settings a course actually changes are stored, so a site with
thousands of courses typically holds only a handful of rows.

## Message resolution order

1. Course override, when one is recorded.
2. Site setting, when it is not empty.
3. The translated language string shipped with the plugin.

This is why leaving a message empty is the recommended way to obtain correct
Arabic and English text without any manual translation work.

## Validation

Every setting is validated at the point of entry, on the site page and on the
course override form alike, because both read the same declared bounds from the
settings register:

* the fallback pass mark accepts a whole number from 0 to 100;
* the redirect delay is rejected below ten seconds;
* colours must be three or six digit hexadecimal values;
* star thresholds must be five ascending whole numbers no greater than 100.

Nothing is silently corrected after saving, so what an administrator sees on the
settings page is exactly what learners get.

## Star thresholds

Thresholds are five ascending percentages separated by commas. The default,
`60,70,80,90,95`, awards one star from sixty percent and five stars from
ninety five percent. A malformed value is rejected on save; an existing
malformed value is repaired to the defaults at display time rather than
breaking the page.

## Changing the pass mark

The shipped default is 60%, and it is a setting rather than a constant: raise it
to 70, 75, 80 or any other whole percentage on the General page, or override it
for a single course from that course's Learning Journey page. A quiz that has
its own pass grade in the gradebook always wins over both, so the fallback only
applies where no pass grade has been set.

## Automatic redirect

Disabled by default. When enabled the delay is at least ten seconds, the
countdown is always visible, and the learner can cancel it with the *Stay on
this page* control or by pressing any key.

## Colours and contrast

Colours must be three or six digit hexadecimal values. When a chosen button
colour and button text colour fall below a 4.5 to 1 contrast ratio, the plugin
saves the value and shows an advisory warning; it does not block the choice.

## Files

Background images accept `.jpg`, `.jpeg`, `.png`, `.webp` and `.svg` up to two
megabytes. Applause sounds accept `.mp3` and `.ogg` up to one megabyte. No
sound file is shipped with the plugin.
