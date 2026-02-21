<template>
    <Transition name="slide-up">
        <div
            v-if="visible"
            class="fixed bottom-0 inset-x-0 z-50 bg-white border-t border-gray-200 shadow-lg px-4 py-4 sm:px-6"
        >
            <div class="max-w-3xl mx-auto flex flex-col sm:flex-row items-start sm:items-center gap-3">
                <p class="text-sm text-gray-600 flex-1">
                    {{ t('cookie_banner_text') }}
                </p>
                <div class="flex gap-2 shrink-0">
                    <button
                        @click="decline"
                        class="px-4 py-2 text-sm rounded border border-gray-300 text-gray-600 hover:bg-gray-100 transition"
                    >
                        {{ t('cookie_decline') }}
                    </button>
                    <button
                        @click="accept"
                        class="px-4 py-2 text-sm rounded bg-indigo-600 text-white hover:bg-indigo-700 transition"
                    >
                        {{ t('cookie_accept') }}
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useI18n } from '../composables/useI18n';

const { t } = useI18n();
const visible = ref(false);

const GA_ID = 'G-4NRB0P6JH7';

function loadGtag() {
    if (document.querySelector(`script[src*="googletagmanager.com/gtag/js?id=${GA_ID}"]`)) {
        return;
    }
    const script = document.createElement('script');
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtag/js?id=${GA_ID}`;
    document.head.appendChild(script);

    window.dataLayer = window.dataLayer || [];
    function gtag() { window.dataLayer.push(arguments); }
    gtag('js', new Date());
    gtag('config', GA_ID);
}

function accept() {
    localStorage.setItem('cookie_consent', 'accepted');
    visible.value = false;
    loadGtag();
}

function decline() {
    localStorage.setItem('cookie_consent', 'declined');
    visible.value = false;
}

onMounted(() => {
    const consent = localStorage.getItem('cookie_consent');
    if (consent === 'accepted') {
        loadGtag();
    } else if (!consent) {
        visible.value = true;
    }
});
</script>

<style scoped>
.slide-up-enter-active,
.slide-up-leave-active {
    transition: transform 0.3s ease, opacity 0.3s ease;
}
.slide-up-enter-from,
.slide-up-leave-to {
    transform: translateY(100%);
    opacity: 0;
}
</style>
