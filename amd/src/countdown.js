// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Accessible, cancellable automatic redirect for the Learning Journey page.
 *
 * The countdown is always visible and always cancellable, so the page meets the
 * WCAG timing adjustable requirement. Any keyboard activity, any focus change
 * inside the page, or hiding the tab cancels it permanently.
 *
 * @module     local_learningjourney/countdown
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Pending from 'core/pending';
import {get_string as getString} from 'core/str';

/** @type {number} Shortest permitted delay, in seconds. */
const MINIMUM_SECONDS = 10;

/** @type {number} Length of one tick, in milliseconds. */
const TICK = 1000;

/**
 * Start the visible countdown towards the next activity.
 *
 * @param {number} seconds Delay before navigation, in seconds.
 * @param {string} url Destination supplied by the server.
 * @returns {void}
 */
export const init = (seconds, url) => {
    const pending = new Pending('local_learningjourney/countdown');
    const container = document.querySelector('[data-ljy-countdown]');
    const parsed = parseInt(seconds, 10);
    const delay = Math.max(MINIMUM_SECONDS, isNaN(parsed) ? MINIMUM_SECONDS : parsed);

    if (!container || !url) {
        pending.resolve();
        return;
    }

    const output = container.querySelector('[data-ljy-countdown-text]');
    const stay = container.querySelector('[data-ljy-stay]');
    let remaining = delay;
    let timer = null;
    let cancelled = false;

    /**
     * Stop the countdown permanently and tidy up its listeners.
     *
     * @returns {void}
     */
    const cancel = () => {
        if (cancelled) {
            return;
        }

        cancelled = true;

        if (timer !== null) {
            window.clearInterval(timer);
            timer = null;
        }

        document.removeEventListener('keydown', cancel, true);
        document.removeEventListener('focusin', cancel, true);
        document.removeEventListener('visibilitychange', onVisibilityChange);
        container.remove();
    };

    /**
     * Cancel the countdown when the tab is hidden.
     *
     * @returns {void}
     */
    const onVisibilityChange = () => {
        if (document.hidden) {
            cancel();
        }
    };

    /**
     * Advance the countdown by one second.
     *
     * @returns {void}
     */
    const tick = () => {
        remaining -= 1;

        if (remaining <= 0) {
            if (timer !== null) {
                window.clearInterval(timer);
                timer = null;
            }
            window.location.assign(url);
            return;
        }

        getString('countdown_remaining', 'local_learningjourney', remaining)
            .then((text) => {
                if (output && !cancelled) {
                    output.textContent = text;
                }
                return text;
            })
            .catch(() => {
                cancel();
            });
    };

    if (stay) {
        stay.addEventListener('click', cancel);
    }

    document.addEventListener('keydown', cancel, true);
    document.addEventListener('focusin', cancel, true);
    document.addEventListener('visibilitychange', onVisibilityChange);

    timer = window.setInterval(tick, TICK);

    // Initialisation is finished. The countdown itself is a visible, user
    // cancellable timer rather than pending work, so the Pending is released
    // here; holding it for the whole countdown would block every test that
    // waits for JavaScript to settle.
    pending.resolve();
};
