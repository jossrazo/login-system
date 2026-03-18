import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Mount Svelte 5 components into elements with data-svelte-component attribute.
// Components live in resources/js/components/*.svelte
import { mount } from 'svelte';

const svelteModules = import.meta.glob('./components/*.svelte', { eager: true });

document.querySelectorAll('[data-svelte-component]').forEach((el) => {
    const name = el.dataset.svelteComponent;
    const mod  = svelteModules[`./components/${name}.svelte`];
    if (mod) {
        const props = el.dataset.svelteProps
            ? JSON.parse(el.dataset.svelteProps)
            : {};
        mount(mod.default, { target: el, props });
    }
});
