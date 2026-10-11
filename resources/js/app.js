// Alpine.js ships with Livewire and starts automatically. This bundle adds the storefront motion
// (GSAP); it waits for the web font so headings split into the lines the visitor actually sees.
import { initMotion } from './motion';

const start = () => initMotion();

if (document.fonts?.ready) {
    document.fonts.ready.then(start, start);
} else {
    start();
}
