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
 * Optional celebration effects for the Learning Journey result page.
 *
 * The module is queued only when an administrator has enabled at least one
 * effect. It draws to a single decorative canvas, tears itself down when the
 * animation finishes, and does nothing at all when the visitor has asked for
 * reduced motion.
 *
 * @module     local_learningjourney/celebrate
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Pending from 'core/pending';

/** @type {number} Hard upper bound on the number of particles drawn. */
const MAX_PARTICLES = 150;

/** @type {number} Hard upper bound on the animation length, in milliseconds. */
const MAX_DURATION = 4000;

/** @type {string[]} Palette used when no theme colour is supplied. */
const PALETTE = ['#1d6f42', '#f2c14e', '#4a90d9', '#d9534f', '#8e6bbf'];

/**
 * Determine whether the visitor has asked for reduced motion.
 *
 * @returns {boolean} True when animation should be suppressed.
 */
const prefersReducedMotion = () => {
    return Boolean(window.matchMedia)
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
};

/**
 * Create the decorative canvas the effects are drawn on.
 *
 * @returns {HTMLCanvasElement} The canvas, already attached to the document.
 */
const createCanvas = () => {
    const canvas = document.createElement('canvas');
    const ratio = window.devicePixelRatio || 1;

    canvas.className = 'ljy-canvas';
    canvas.setAttribute('aria-hidden', 'true');
    canvas.width = window.innerWidth * ratio;
    canvas.height = window.innerHeight * ratio;
    canvas.style.width = window.innerWidth + 'px';
    canvas.style.height = window.innerHeight + 'px';

    document.body.appendChild(canvas);

    return canvas;
};

/**
 * Build the particle set for the enabled effects.
 *
 * @param {Object} config Effect configuration supplied by the server.
 * @param {number} width Canvas width in device pixels.
 * @param {number} height Canvas height in device pixels.
 * @returns {Array<Object>} The particles to animate.
 */
const buildParticles = (config, width, height) => {
    const palette = config.colour ? [config.colour].concat(PALETTE) : PALETTE;
    const particles = [];
    const kinds = [];

    if (config.confetti) {
        kinds.push('confetti');
    }
    if (config.stars) {
        kinds.push('star');
    }
    if (config.fireworks) {
        kinds.push('firework');
    }
    if (config.trophy && kinds.length === 0) {
        kinds.push('star');
    }

    if (kinds.length === 0) {
        return particles;
    }

    const perKind = Math.floor(MAX_PARTICLES / kinds.length);

    kinds.forEach((kind) => {
        for (let index = 0; index < perKind; index++) {
            const burst = kind === 'firework';
            const angle = Math.random() * Math.PI * 2;
            const speed = burst ? 2 + Math.random() * 4 : 1 + Math.random() * 2;

            particles.push({
                kind: kind,
                x: burst ? width / 2 : Math.random() * width,
                y: burst ? height / 3 : -Math.random() * height * 0.3,
                vx: burst ? Math.cos(angle) * speed : (Math.random() - 0.5) * 1.5,
                vy: burst ? Math.sin(angle) * speed : speed,
                size: 4 + Math.random() * 6,
                spin: (Math.random() - 0.5) * 0.2,
                angle: Math.random() * Math.PI,
                colour: palette[Math.floor(Math.random() * palette.length)]
            });
        }
    });

    return particles;
};

/**
 * Draw a single particle.
 *
 * @param {CanvasRenderingContext2D} context The drawing context.
 * @param {Object} particle The particle to draw.
 * @param {number} alpha Current opacity between zero and one.
 * @returns {void}
 */
const drawParticle = (context, particle, alpha) => {
    context.save();
    context.globalAlpha = alpha;
    context.fillStyle = particle.colour;
    context.translate(particle.x, particle.y);
    context.rotate(particle.angle);

    if (particle.kind === 'star') {
        context.beginPath();
        for (let point = 0; point < 5; point++) {
            const outer = (point * 2 * Math.PI) / 5 - Math.PI / 2;
            const inner = outer + Math.PI / 5;
            context.lineTo(Math.cos(outer) * particle.size, Math.sin(outer) * particle.size);
            context.lineTo(Math.cos(inner) * particle.size * 0.45, Math.sin(inner) * particle.size * 0.45);
        }
        context.closePath();
        context.fill();
    } else if (particle.kind === 'firework') {
        context.beginPath();
        context.arc(0, 0, particle.size * 0.35, 0, Math.PI * 2);
        context.fill();
    } else {
        context.fillRect(-particle.size / 2, -particle.size / 4, particle.size, particle.size / 2);
    }

    context.restore();
};

/**
 * Run the particle animation until it completes, then remove the canvas.
 *
 * @param {Object} config Effect configuration supplied by the server.
 * @param {Pending} pending Pending promise released once the animation ends.
 * @returns {void}
 */
const animate = (config, pending) => {
    const canvas = createCanvas();
    const context = canvas.getContext('2d');

    if (!context) {
        canvas.remove();
        pending.resolve();
        return;
    }

    const ratio = window.devicePixelRatio || 1;
    const particles = buildParticles(config, canvas.width, canvas.height);

    if (particles.length === 0) {
        canvas.remove();
        pending.resolve();
        return;
    }

    context.scale(1, 1);
    const started = window.performance.now();
    const gravity = 0.05 * ratio;

    const step = (now) => {
        const elapsed = now - started;

        if (elapsed >= MAX_DURATION) {
            canvas.remove();
            pending.resolve();
            return;
        }

        const alpha = Math.max(0, 1 - (elapsed / MAX_DURATION));
        context.clearRect(0, 0, canvas.width, canvas.height);

        particles.forEach((particle) => {
            particle.x += particle.vx * ratio;
            particle.y += particle.vy * ratio;
            particle.vy += gravity;
            particle.angle += particle.spin;
            drawParticle(context, particle, alpha);
        });

        window.requestAnimationFrame(step);
    };

    window.requestAnimationFrame(step);
};

/**
 * Wire the optional applause control.
 *
 * Sound is never played automatically; it requires an explicit user gesture,
 * and the mute choice is stored as a Moodle user preference.
 *
 * @param {Object} config Effect configuration supplied by the server.
 * @returns {void}
 */
const wireSound = (config) => {
    const control = document.querySelector('[data-ljy-sound]');

    if (!control || !config.sound || !config.soundurl) {
        if (control) {
            control.remove();
        }
        return;
    }

    control.addEventListener('click', () => {
        const audio = new Audio(config.soundurl);
        audio.play().catch(() => {
            control.remove();
        });
    });
};

/**
 * Start the celebration effects.
 *
 * @param {Object} config Effect configuration supplied by the server.
 * @returns {void}
 */
export const init = (config) => {
    const pending = new Pending('local_learningjourney/celebrate');
    const settings = config || {};

    wireSound(settings);

    if (prefersReducedMotion()) {
        pending.resolve();
        return;
    }

    animate(settings, pending);
};
