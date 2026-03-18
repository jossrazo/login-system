/**
 * app.js — Main JavaScript entry point
 *
 * Responsibilities:
 * 1. Bootstrap Axios with the CSRF token header so all AJAX requests are protected.
 * 2. Start Alpine.js for lightweight reactive UI (form validation, dropdowns, etc.).
 * 3. Auto-discover and mount Svelte 5 components embedded in Blade views.
 *
 * How Svelte components are embedded in Blade:
 *   <div data-svelte-component="DeleteConfirm"
 *        data-svelte-props='{"recordId":1,"recordName":"John Doe",...}'></div>
 *
 * At page load this script scans for those elements, looks up the matching
 * .svelte file, parses the JSON props, and mounts the component into the element.
 */

import './bootstrap';

// Alpine.js — handles dropdowns, toggles, and form validation in Blade views
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// ---------------------------------------------------------------
// Svelte 5 component mounting
// ---------------------------------------------------------------
import { mount } from 'svelte';

/**
 * Eagerly import all .svelte files from the components directory.
 * import.meta.glob is a Vite feature that resolves the file list at build time.
 * { eager: true } means they're bundled up-front rather than lazy-loaded.
 */
const svelteModules = import.meta.glob('./components/*.svelte', { eager: true });

/**
 * For every element tagged with data-svelte-component:
 * - Find the matching compiled Svelte module.
 * - Parse the JSON props from data-svelte-props (passed from the Blade view).
 * - Mount the Svelte component into that DOM element.
 *
 * This pattern lets us sprinkle interactive Svelte components (like the delete
 * confirmation modal) inside server-rendered Blade pages without a full SPA setup.
 */
document.querySelectorAll('[data-svelte-component]').forEach((el) => {
    const name = el.dataset.svelteComponent;
    const mod  = svelteModules[`./components/${name}.svelte`];

    if (mod) {
        // Parse props passed from Blade via data-svelte-props JSON attribute
        const props = el.dataset.svelteProps
            ? JSON.parse(el.dataset.svelteProps)
            : {};

        // mount() is the Svelte 5 API for attaching a component to a DOM element
        mount(mod.default, { target: el, props });
    }
});
