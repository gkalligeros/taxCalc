import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

function getValueByPath(obj, path) {
    return path.split('.').reduce((carry, part) => {
        if (carry && Object.prototype.hasOwnProperty.call(carry, part)) {
            return carry[part];
        }

        return undefined;
    }, obj);
}

export function useI18n() {
    const page = usePage();

    const locale = computed(() => page.props.locale || 'en');
    const supportedLocales = computed(() => page.props.supportedLocales || ['en', 'el']);
    const ui = computed(() => page.props.translations?.ui || {});
    const countries = computed(() => page.props.translations?.countries || {});

    function t(key, replacements = {}) {
        const value = getValueByPath(ui.value, key);

        if (typeof value !== 'string') {
            return key;
        }

        return Object.entries(replacements).reduce((text, [replacementKey, replacementValue]) => {
            return text.replaceAll(`:${replacementKey}`, String(replacementValue));
        }, value);
    }

    function switchLocale(nextLocale) {
        if (nextLocale === locale.value) {
            return;
        }

        const url = new URL(window.location.href);
        url.searchParams.set('lang', nextLocale);

        router.visit(`${url.pathname}${url.search}`, {
            method: 'get',
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }

    return {
        locale,
        supportedLocales,
        countries,
        t,
        switchLocale,
    };
}
