import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Mount Svelte components into elements with data-svelte-component attribute
const svelteModules = import.meta.glob('./components/*.svelte', { eager: true });

document.querySelectorAll('[data-svelte-component]').forEach((el) => {
    const name = el.dataset.svelteComponent;
    const mod = svelteModules[`./components/${name}.svelte`];
    if (mod) {
        const props = el.dataset.svelteProps
            ? JSON.parse(el.dataset.svelteProps)
            : {};
        new mod.default({ target: el, props });
    }
});
