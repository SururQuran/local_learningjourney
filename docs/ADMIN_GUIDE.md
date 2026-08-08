# Administrator guide

## What the plugin does

When a learner submits a quiz attempt, Learning Journey shows a full page
result instead of the ordinary review screen. The page reports the score, the
pass mark applied, a star rating, course progress, any badges earned and a
clear route to the next activity. The standard Moodle review page remains
reachable from that result page whenever the quiz's own review options permit
it.

## Scope

The plugin is enabled site wide by default. Two levers control scope:

* **Site level** — the *Enable Learning Journey* setting on the General page.
* **Course level** — the same setting on a course's Learning Journey page,
  which lets a single course opt out without affecting the rest of the site.

## Settings pages

| Page | Controls |
| --- | --- |
| General | Enabling, layout, automatic redirect, lesson unit, fallback pass mark |
| Messages | Headings, body messages, supplementary lines, button labels |
| Appearance | Colours, background image, icon style |
| Effects | Confetti, stars, trophy, fireworks, achievement badge, applause |
| Display and scoring | Which cards appear, star thresholds, manual badge mapping |

Every message setting falls back to a translated language string when left
empty, so an untouched installation is already correctly translated in every
installed language.

## Pass marks

The verdict always comes from the gradebook, which is the same source Moodle
uses for the quiz *Require passing grade* completion condition. A quiz with no
pass grade set has no verdict of its own, so the plugin applies the site wide
fallback of sixty percent by default. The report at *Local plugins → Learning
Journey → Quizzes without a pass mark* lists every quiz relying on that
fallback.

## Badges

The Moodle badge system remains authoritative. Configure badge criteria in the
usual way; the plugin displays badges the learner has earned and never issues
criteria driven badges itself. Course completion badges are awarded by cron and
may therefore appear shortly after the result page is shown, which is why the
page falls back to a decorative achievement badge.

The optional *Manual badge to award* setting issues one explicitly mapped
manual badge and is empty by default.

## Accessibility statement

The plugin targets WCAG 2.2 Level AA. Automatic redirect is disabled by default
and, when enabled, always shows a visible countdown with a control to remain on
the page. Sound never plays automatically. Colour settings display a contrast
warning when a pair falls below the recommended 4.5 to 1 ratio.

## Accessibility behaviour worth knowing

* The outcome panel is a labelled region headed by a single `h1`, so screen
  reader users hear the result as the first content on the page.
* Stars, icons, the trophy and the progress fill are decorative and hidden from
  assistive technology; the meaning is carried by text and by the progress bar's
  own value.
* Every action is a real link with a 44 pixel minimum target, so keyboard and
  touch users reach everything without script.
* All animation is suppressed for visitors who have asked for reduced motion,
  and the page remains fully usable with JavaScript disabled.

## Mobile app

The official app submits attempts over web services and renders its own
interface, so the result page cannot be shown at the moment of submission
inside the app. The plugin adds a *Learning Journey* item to the app's course
menu and exposes a read-only web service instead.
