import '../css/app.css';
import './bootstrap';
import '../css/style.css';
import 'element-plus/dist/index.css'

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import ElementPlus from 'element-plus'
import store from './store'

// import { createVuetify } from 'vuetify';
import 'vuetify/styles';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import { aliases, mdi } from 'vuetify/iconsets/mdi'
import '@mdi/font/css/materialdesignicons.css';

const appName = import.meta.env.VITE_APP_NAME || 'Video';

// const vuetify = createVuetify({
//     components,
//     directives,
//     icons: {
//         defaultSet: 'mdi',
//         aliases,
//         sets: {
//             mdi,
//         },
//     },
// });
createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(ElementPlus)
            .use(store)

        // Initialize current user into Vuex (expects auth.user in Inertia props)
        try {
            const currentUser = props?.initialPage?.props?.auth?.user ?? null
            store.commit('auth/SET_USER', currentUser)
            // If no user from Inertia but we might have a token (Passport), fetch via API
            const token = store.getters['auth/token']
            if (!currentUser && token) {
                store.dispatch('auth/getUser').catch(() => {})
            }
            // If redirected with apiToken in URL (after Bitrix login), persist it and fetch user
            const params = new URLSearchParams(window.location.search)
            const apiToken = params.get('apiToken')
            if (apiToken) {
                store.dispatch('auth/setAuth', { token: apiToken, user: null })
                    .then(() => store.dispatch('auth/getUser'))
                    .catch(() => {})
                    .finally(() => {
                        // Clean the URL (remove apiToken param) without reload
                        params.delete('apiToken')
                        const newSearch = params.toString()
                        const newUrl = window.location.pathname + (newSearch ? `?${newSearch}` : '') + window.location.hash
                        window.history.replaceState({}, '', newUrl)
                    })
            }
        } catch (e) {
            // no-op if structure differs
        }

        return app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
