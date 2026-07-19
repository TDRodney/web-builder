import type { PageProps } from '@inertiajs/core';
import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import type { InertiaApp, InertiaAppProps } from '@inertiajs/vue3/types/app';
import { renderToString } from '@vue/server-renderer';
import { createSSRApp, h } from 'vue';
import type { Plugin } from 'vue';

interface SsrSetupOptions {
    el: null;
    App: InertiaApp;
    props: InertiaAppProps<PageProps>;
    plugin: Plugin;
}

createServer((page) =>
    createInertiaApp({
        page,
        render: renderToString,
        setup({ App, props, plugin }: SsrSetupOptions) {
            return createSSRApp({ render: () => h(App, props) }).use(plugin);
        },
    }),
);
