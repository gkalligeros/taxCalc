<template>
    <div class="min-h-screen bg-gray-100 py-12">
        <div class="max-w-4xl mx-auto px-4">
            <div class="flex justify-between items-center mb-8 gap-4">
                <h1 class="text-3xl font-bold text-gray-800">{{ t('tax_scales') }}</h1>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xs uppercase text-gray-500">{{ t('language') }}</span>
                        <button
                            v-for="lang in supportedLocales"
                            :key="lang"
                            @click="switchLocale(lang)"
                            class="px-2 py-1 text-xs rounded border transition"
                            :class="lang === locale ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-100'"
                        >
                            {{ { en: t('english'), el: t('greek'), it: t('italian') }[lang] ?? lang }}
                        </button>
                    </div>
                    <a href="/" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                        &larr; {{ t('calculator') }}
                    </a>
                </div>
            </div>

            <div v-if="$page.props.flash?.success" class="mb-4 p-3 bg-green-50 border border-green-200 rounded text-green-800 text-sm">
                {{ $page.props.flash.success }}
            </div>

            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-3">{{ t('create_new_scale') }}</h2>
                <form @submit.prevent="createScale" class="space-y-3">
                    <div class="flex gap-3">
                        <input
                            v-model="newScale.name"
                            type="text"
                            :placeholder="t('scale_name_placeholder')"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            required
                        />
                        <select
                            v-model="newScale.country_code"
                            @change="onCreateCountryChange"
                            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            required
                        >
                            <option v-for="(name, code) in availableCountries" :key="code" :value="code">
                                {{ code }} - {{ name }}
                            </option>
                        </select>
                        <select
                            v-model.number="newScale.salaries_per_year"
                            class="w-36 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            required
                        >
                            <option :value="12">{{ t('salary_division_12') }}</option>
                            <option :value="13">{{ t('salary_division_13') }}</option>
                            <option :value="14">{{ t('salary_division_14') }}</option>
                        </select>
                        <input
                            v-model="newScale.state"
                            type="text"
                            :placeholder="t('state_optional')"
                            class="w-40 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                        />
                        <button
                            type="submit"
                            :disabled="!newScale.name || !newScale.country_code"
                            class="px-5 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition"
                        >
                            {{ t('create') }}
                        </button>
                    </div>
                </form>
            </div>

            <div class="space-y-3">
                <div
                    v-for="scale in scales"
                    :key="scale.id"
                    class="bg-white rounded-lg shadow p-5 flex items-center justify-between"
                    :class="{ 'ring-2 ring-indigo-500': scale.is_active }"
                >
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-semibold text-gray-800">{{ scale.name }}</h3>
                            <span class="px-2 py-0.5 text-xs font-medium bg-gray-200 text-gray-700 rounded-full">
                                {{ scale.country_code }}{{ scale.state ? ' / ' + scale.state : '' }}
                            </span>
                            <span class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 rounded-full">
                                {{ scale.currency ?? 'EUR' }}
                            </span>
                            <span v-if="scale.is_active" class="px-2 py-0.5 text-xs font-medium bg-indigo-100 text-indigo-700 rounded-full">
                                {{ t('active') }}
                            </span>
                        </div>
                        <div class="text-sm text-gray-500 mt-1">
                            {{ scale.brackets_count }} {{ t('brackets_label') }} &middot; {{ scale.deductions_count }} {{ t('deductions_label') }} &middot; {{ scale.salaries_per_year }}x
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            v-if="!scale.is_active"
                            @click="activateScale(scale)"
                            class="px-3 py-1.5 text-sm bg-green-100 text-green-700 rounded hover:bg-green-200 transition"
                        >
                            {{ t('activate') }}
                        </button>
                        <a
                            :href="`/admin/scales/${scale.id}`"
                            class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition"
                        >
                            {{ t('edit') }}
                        </a>
                        <button
                            @click="deleteScale(scale)"
                            class="px-3 py-1.5 text-sm bg-red-100 text-red-700 rounded hover:bg-red-200 transition"
                        >
                            {{ t('delete') }}
                        </button>
                    </div>
                </div>

                <div v-if="!scales.length" class="text-center py-12 text-gray-500">
                    {{ t('no_tax_scales_yet') }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from '../../../composables/useI18n';

defineProps({
    scales: Array,
    availableCountries: Object,
});

const { locale, supportedLocales, t, switchLocale } = useI18n();
const newScale = ref({ name: '', country_code: 'GR', salaries_per_year: 14, state: '' });

function defaultSalariesForCountry(countryCode) {
    return countryCode === 'GR' ? 14 : 12;
}

function onCreateCountryChange() {
    newScale.value.salaries_per_year = defaultSalariesForCountry(newScale.value.country_code);
}

function createScale() {
    router.post('/admin/scales', {
        name: newScale.value.name,
        country_code: newScale.value.country_code,
        salaries_per_year: newScale.value.salaries_per_year,
        state: newScale.value.state || null,
    }, {
        onSuccess: () => { newScale.value = { name: '', country_code: 'GR', salaries_per_year: 14, state: '' }; },
    });
}

function activateScale(scale) {
    router.post(`/admin/scales/${scale.id}/activate`);
}

function deleteScale(scale) {
    if (confirm(t('delete_scale_confirm', { name: scale.name }))) {
        router.delete(`/admin/scales/${scale.id}`);
    }
}
</script>
