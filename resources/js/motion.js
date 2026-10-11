/**
 * Storefront motion (GSAP + ScrollTrigger + SplitText).
 *
 * Markup hooks — nothing here invents content, it only choreographs what is already on the page:
 *   [data-hero]             hero slider; [data-hero-slide] slides, [data-hero-title] / [data-hero-item] copy
 *   .reveal                 a heading block: its h1/h2 rises line by line, the rest fades up after it
 *   .underline-swoosh       the cognac underline draws from left to right once its title is in view
 *   [data-anim="stagger"]   direct children enter one after another
 *   [data-anim="cards"]     product cards enter in batches; their photos settle from a slight zoom
 *   [data-anim="panel"]     a dark panel opens from a slightly inset clip
 *   [data-count]            the single number inside counts up
 *   [data-marquee]          the promise strip loops with GSAP and reacts to scroll speed
 *
 * Everything runs inside gsap.matchMedia('(prefers-reduced-motion: no-preference)'): visitors who
 * prefer reduced motion get the static page (CSS shows the pre-hidden hero copy for them too).
 */
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { SplitText } from 'gsap/SplitText';

gsap.registerPlugin(ScrollTrigger, SplitText);

const EASE = 'power3.out';
const root = document.documentElement;

/** Lines of a heading slide up from behind a mask. Returns the tween (or null when nothing to do). */
function riseLines(el, options = {}) {
    // Headings that contain their own markup (e.g. the swoosh span) move as one block.
    if (el.querySelector('.underline-swoosh')) {
        return gsap.from(el, { yPercent: 30, autoAlpha: 0, duration: 0.9, ease: EASE, ...options });
    }
    const split = SplitText.create(el, { type: 'lines', mask: 'lines', linesClass: 'lx-line', aria: 'auto' });

    return gsap.from(split.lines, {
        yPercent: 110,
        duration: 0.95,
        ease: 'power4.out',
        stagger: 0.09,
        ...options,
        onComplete() {
            split.revert();
            options.onComplete?.();
        },
    });
}

function hero() {
    const section = document.querySelector('[data-hero]');
    if (!section) {
        return;
    }

    const play = (slide, delay = 0) => {
        if (!slide) {
            return;
        }
        const title = slide.querySelector('[data-hero-title]');
        const items = slide.querySelectorAll('[data-hero-item]');
        const tl = gsap.timeline({ delay });

        gsap.set([title, ...items], { autoAlpha: 1 });
        if (title) {
            tl.add(riseLines(title), 0);
        }
        tl.from(items, { y: 22, autoAlpha: 0, duration: 0.8, ease: EASE, stagger: 0.08, clearProps: 'transform' }, 0.25);

        return tl;
    };

    play(section.querySelector('[data-hero-slide="0"]'), 0.1);
    section.addEventListener('hero:slide', (event) => {
        play(section.querySelector(`[data-hero-slide="${event.detail.index}"]`), 0.15);
    });
}

function headings() {
    gsap.utils.toArray('.reveal').forEach((block) => {
        if (block.closest('[data-hero]')) {
            return;
        }
        const title = block.querySelector('h1, h2');
        const rest = [...block.children].filter((child) => child !== title && !child.contains(title));
        const tl = gsap.timeline({ scrollTrigger: { trigger: block, start: 'top 88%', once: true } });

        if (title) {
            tl.add(riseLines(title));
        }
        if (rest.length) {
            tl.from(rest, { y: 18, autoAlpha: 0, duration: 0.7, ease: EASE, stagger: 0.08, clearProps: 'transform' }, title ? 0.3 : 0);
        }
    });

    // The swoosh is a background image: draw it by growing its width.
    gsap.utils.toArray('.underline-swoosh').forEach((swoosh) => {
        gsap.fromTo(
            swoosh,
            { backgroundSize: '0% 0.32em' },
            {
                backgroundSize: '100% 0.32em',
                duration: 1.1,
                ease: 'power2.inOut',
                delay: 0.35,
                scrollTrigger: { trigger: swoosh, start: 'top 88%', once: true },
            },
        );
    });
}

function staggers() {
    gsap.utils.toArray('[data-anim="stagger"]').forEach((list) => {
        const items = list.tagName === 'TBODY' ? list.rows : list.children;
        if (!items.length) {
            return;
        }
        gsap.from(items, {
            y: 20,
            autoAlpha: 0,
            duration: 0.7,
            ease: EASE,
            stagger: 0.07,
            clearProps: 'transform',
            scrollTrigger: { trigger: list, start: 'top 90%', once: true },
        });
    });
}

function cards() {
    gsap.utils.toArray('[data-anim="cards"]').forEach((list) => {
        const items = [...list.children];
        if (!items.length) {
            return;
        }
        gsap.set(items, { autoAlpha: 0, y: 36 });
        ScrollTrigger.batch(items, {
            start: 'top 92%',
            once: true,
            onEnter: (batch) => {
                gsap.to(batch, { autoAlpha: 1, y: 0, duration: 0.8, ease: EASE, stagger: 0.08, clearProps: 'transform' });
                const photos = batch.flatMap((item) => [...item.querySelectorAll('article img')]);
                if (!photos.length) {
                    return;
                }
                gsap.from(photos, {
                    scale: 1.12,
                    duration: 1.4,
                    ease: 'power2.out',
                    stagger: 0.08,
                    clearProps: 'transform',
                });
            },
        });
    });
}

function panels() {
    gsap.utils.toArray('[data-anim="panel"]').forEach((panel) => {
        gsap.fromTo(
            panel,
            { clipPath: 'inset(7% 5% 7% 5% round 28px)' },
            {
                clipPath: 'inset(0% 0% 0% 0% round 12px)',
                duration: 1.2,
                ease: 'power3.inOut',
                clearProps: 'clipPath',
                scrollTrigger: { trigger: panel, start: 'top 85%', once: true },
            },
        );
    });
}

/** Count up the one whole number in an element ("৳300", "15"); ranges such as "2–3" are left alone. */
function counters() {
    gsap.utils.toArray('[data-count]').forEach((el) => {
        const walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT);
        let node;
        while ((node = walker.nextNode())) {
            const numbers = node.nodeValue.match(/\d[\d,]*/g);
            if (!numbers) {
                continue;
            }
            if (numbers.length !== 1) {
                return;
            }
            const text = node.nodeValue;
            const target = Number(numbers[0].replace(/,/g, ''));
            const counter = { value: 0 };
            const format = (value) => text.replace(numbers[0], Math.round(value).toLocaleString('en-US'));
            const textNode = node;

            gsap.to(counter, {
                value: target,
                duration: 1.4,
                ease: 'power2.out',
                scrollTrigger: { trigger: el, start: 'top 90%', once: true },
                onStart: () => (textNode.nodeValue = format(0)),
                onUpdate: () => (textNode.nodeValue = format(counter.value)),
                onComplete: () => (textNode.nodeValue = text),
            });

            return;
        }
    });
}

/** The promise strip: a GSAP loop that speeds up (and follows the scroll direction) while the page scrolls. */
function marquee() {
    document.querySelectorAll('[data-marquee]').forEach((strip) => {
        const track = strip.querySelector('.marquee-track');
        if (!track) {
            return;
        }
        track.style.animation = 'none';
        const loop = gsap.to(track, { xPercent: -50, duration: 38, ease: 'none', repeat: -1 });
        let paused = false;
        let direction = 1;

        strip.querySelector('[data-marquee-toggle]')?.addEventListener('click', () => {
            paused = !paused;
            paused ? loop.pause() : loop.resume();
        });

        ScrollTrigger.create({
            trigger: strip,
            start: 'top bottom',
            end: 'bottom top',
            onToggle: (self) => (self.isActive && !paused ? loop.resume() : loop.pause()),
            onUpdate: (self) => {
                if (paused) {
                    return;
                }
                direction = self.direction;
                const boost = Math.min(Math.abs(self.getVelocity()) / 300, 5);
                gsap.timeline({ overwrite: true })
                    .to(loop, { timeScale: direction * (1 + boost), duration: 0.2 })
                    .to(loop, { timeScale: direction, duration: 1.2, ease: 'power1.out' });
            },
        });
    });
}

/** Mega-menu panel: collection names cascade in when a panel opens (called from the header). */
function menuPanel(panel) {
    const names = panel.querySelectorAll('ul li');
    gsap.from(names, { x: -14, autoAlpha: 0, duration: 0.45, ease: EASE, stagger: 0.04, clearProps: 'all', overwrite: true });
}

export function initMotion() {
    const mm = gsap.matchMedia();

    mm.add('(prefers-reduced-motion: no-preference)', () => {
        hero();
        headings();
        staggers();
        cards();
        panels();
        counters();
        marquee();
        window.lxMotion = { menuPanel };

        // Images and web fonts change heights after load; keep trigger positions honest.
        window.addEventListener('load', () => ScrollTrigger.refresh(), { once: true });

        return () => delete window.lxMotion;
    });

    root.classList.add('motion-ready');
}
