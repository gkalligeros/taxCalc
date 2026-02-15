<template>
    <div class="min-h-screen bg-gray-100 py-12">
        <div class="max-w-4xl mx-auto px-4">
            <div class="flex justify-between items-center mb-8 gap-4">
                <div>
                    <a href="/admin/scales" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">&larr; {{ t('back_to_scales') }}</a>
                    <h1 class="text-3xl font-bold text-gray-800 mt-2">{{ t('edit_scale') }}</h1>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs uppercase text-gray-500">{{ t('language') }}</span>
                    <button
                        v-for="lang in supportedLocales"
                        :key="lang"
                        @click="switchLocale(lang)"
                        class="px-2 py-1 text-xs rounded border transition"
                        :class="lang === locale ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-100'"
                    >
                        {{ lang === 'el' ? t('greek') : t('english') }}
                    </button>
                </div>
            </div>

            <div v-if="$page.props.flash?.success" class="mb-4 p-3 bg-green-50 border border-green-200 rounded text-green-800 text-sm">
                {{ $page.props.flash.success }}
            </div>

            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-3">{{ t('scale_details') }}</h2>
                <form @submit.prevent="updateDetails" class="flex gap-3 items-end">
                    <div class="flex-1">
                        <label class="block text-xs text-gray-500 mb-1">{{ t('name') }}</label>
                        <input
                            v-model="scaleName"
                            type="text"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            required
                        />
                    </div>
                    <div class="w-36">
                        <label class="block text-xs text-gray-500 mb-1">{{ t('country') }}</label>
                        <select
                            v-model="scaleCountry"
                            @change="onEditCountryChange"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            required
                        >
                            <option v-for="(name, code) in availableCountries" :key="code" :value="code">
                                {{ code }} - {{ name }}
                            </option>
                        </select>
                    </div>
                    <div class="w-36">
                        <label class="block text-xs text-gray-500 mb-1">{{ t('salaries_per_year') }}</label>
                        <select
                            v-model.number="scaleSalariesPerYear"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            required
                        >
                            <option :value="12">{{ t('salary_division_12') }}</option>
                            <option :value="14">{{ t('salary_division_14') }}</option>
                        </select>
                    </div>
                    <div class="w-36">
                        <label class="block text-xs text-gray-500 mb-1">{{ t('state') }}</label>
                        <input
                            v-model="scaleState"
                            type="text"
                            :placeholder="t('optional')"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                        />
                    </div>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition">
                        {{ t('save') }}
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ t('tax_brackets') }}</h2>
                <table class="w-full text-sm mb-4">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2 text-gray-600">{{ t('min_amount_eur') }}</th>
                            <th class="text-left py-2 text-gray-600">{{ t('max_amount_eur') }}</th>
                            <th class="text-left py-2 text-gray-600">{{ t('rate') }} (%)</th>
                            <th class="text-right py-2 text-gray-600">{{ t('actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="bracket in scale.brackets" :key="bracket.id">
                            <tr class="border-b border-gray-100">
                                <template v-if="editingBracket?.id === bracket.id">
                                    <td class="py-2 pr-2">
                                        <input v-model.number="editingBracket.min_amount" type="number" min="0" step="0.01" class="w-full px-2 py-1 border rounded text-sm" />
                                    </td>
                                    <td class="py-2 pr-2">
                                        <input v-model.number="editingBracket.max_amount" type="number" min="0" step="0.01" :placeholder="t('unlimited')" class="w-full px-2 py-1 border rounded text-sm" />
                                    </td>
                                    <td class="py-2 pr-2">
                                        <input v-model.number="editingBracket.rate_pct" type="number" min="0" max="100" step="0.01" class="w-full px-2 py-1 border rounded text-sm" />
                                    </td>
                                    <td class="py-2 text-right space-x-1">
                                        <button @click="saveBracket" class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded hover:bg-green-200">{{ t('save') }}</button>
                                        <button @click="editingBracket = null" class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded hover:bg-gray-200">{{ t('cancel') }}</button>
                                    </td>
                                </template>
                                <template v-else>
                                    <td class="py-2">&euro;{{ Number(bracket.min_amount).toLocaleString(locale) }}</td>
                                    <td class="py-2">{{ bracket.max_amount ? '&euro;' + Number(bracket.max_amount).toLocaleString(locale) : t('unlimited') }}</td>
                                    <td class="py-2">{{ (bracket.rate * 100).toFixed(2) }}%</td>
                                    <td class="py-2 text-right space-x-1">
                                        <button
                                            @click="toggleOverrides(bracket.id)"
                                            class="px-2 py-1 text-xs rounded hover:bg-purple-200 transition"
                                            :class="expandedBracket === bracket.id ? 'bg-purple-200 text-purple-800' : 'bg-purple-100 text-purple-700'"
                                        >
                                            {{ t('overrides') }} ({{ (bracket.overrides || []).length }})
                                        </button>
                                        <button @click="startEditBracket(bracket)" class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200">{{ t('edit') }}</button>
                                        <button @click="deleteBracket(bracket)" class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200">{{ t('delete') }}</button>
                                    </td>
                                </template>
                            </tr>

                            <tr v-if="expandedBracket === bracket.id">
                                <td colspan="4" class="bg-purple-50 p-4">
                                    <div class="text-xs font-semibold text-purple-700 uppercase tracking-wide mb-3">
                                        {{ t('rate_overrides_for') }}
                                        &euro;{{ Number(bracket.min_amount).toLocaleString(locale) }}
                                        &ndash;
                                        {{ bracket.max_amount ? '&euro;' + Number(bracket.max_amount).toLocaleString(locale) : '&infin;' }}
                                    </div>

                                    <table v-if="(bracket.overrides || []).length" class="w-full text-xs mb-3">
                                        <thead>
                                            <tr class="border-b border-purple-200">
                                                <th class="text-left py-1 text-purple-600">{{ t('min_age') }}</th>
                                                <th class="text-left py-1 text-purple-600">{{ t('max_age') }}</th>
                                                <th class="text-left py-1 text-purple-600">{{ t('min_children') }}</th>
                                                <th class="text-left py-1 text-purple-600">{{ t('max_children') }}</th>
                                                <th class="text-left py-1 text-purple-600">{{ t('rate') }} (%)</th>
                                                <th class="text-right py-1 text-purple-600">{{ t('actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="ov in bracket.overrides" :key="ov.id" class="border-b border-purple-100">
                                                <template v-if="editingOverride?.id === ov.id">
                                                    <td class="py-1 pr-1"><input v-model.number="editingOverride.min_age" type="number" min="0" max="120" :placeholder="t('any')" class="w-full px-1 py-0.5 border rounded text-xs" /></td>
                                                    <td class="py-1 pr-1"><input v-model.number="editingOverride.max_age" type="number" min="0" max="120" :placeholder="t('any')" class="w-full px-1 py-0.5 border rounded text-xs" /></td>
                                                    <td class="py-1 pr-1"><input v-model.number="editingOverride.min_children" type="number" min="0" max="20" class="w-full px-1 py-0.5 border rounded text-xs" /></td>
                                                    <td class="py-1 pr-1"><input v-model.number="editingOverride.max_children" type="number" min="0" max="20" :placeholder="t('any')" class="w-full px-1 py-0.5 border rounded text-xs" /></td>
                                                    <td class="py-1 pr-1"><input v-model.number="editingOverride.rate_pct" type="number" min="0" max="100" step="0.01" class="w-full px-1 py-0.5 border rounded text-xs" /></td>
                                                    <td class="py-1 text-right space-x-1">
                                                        <button @click="saveOverride(bracket)" class="px-1.5 py-0.5 text-xs bg-green-100 text-green-700 rounded hover:bg-green-200">{{ t('save') }}</button>
                                                        <button @click="editingOverride = null" class="px-1.5 py-0.5 text-xs bg-gray-100 text-gray-600 rounded hover:bg-gray-200">{{ t('cancel') }}</button>
                                                    </td>
                                                </template>
                                                <template v-else>
                                                    <td class="py-1">{{ ov.min_age ?? t('any') }}</td>
                                                    <td class="py-1">{{ ov.max_age ?? t('any') }}</td>
                                                    <td class="py-1">{{ ov.min_children }}</td>
                                                    <td class="py-1">{{ ov.max_children ?? t('any') }}</td>
                                                    <td class="py-1">{{ (ov.rate * 100).toFixed(2) }}%</td>
                                                    <td class="py-1 text-right space-x-1">
                                                        <button @click="startEditOverride(ov)" class="px-1.5 py-0.5 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200">{{ t('edit') }}</button>
                                                        <button @click="deleteOverride(bracket, ov)" class="px-1.5 py-0.5 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200">{{ t('delete') }}</button>
                                                    </td>
                                                </template>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <form @submit.prevent="addOverride(bracket)" class="flex gap-1 items-end">
                                        <div class="flex-1">
                                            <label class="block text-xs text-purple-500 mb-0.5">{{ t('min_age') }}</label>
                                            <input v-model.number="newOverride.min_age" type="number" min="0" max="120" :placeholder="t('any')" class="w-full px-1 py-1 border rounded text-xs" />
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-xs text-purple-500 mb-0.5">{{ t('max_age') }}</label>
                                            <input v-model.number="newOverride.max_age" type="number" min="0" max="120" :placeholder="t('any')" class="w-full px-1 py-1 border rounded text-xs" />
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-xs text-purple-500 mb-0.5">{{ t('min_children') }}</label>
                                            <input v-model.number="newOverride.min_children" type="number" min="0" max="20" class="w-full px-1 py-1 border rounded text-xs" />
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-xs text-purple-500 mb-0.5">{{ t('max_children') }}</label>
                                            <input v-model.number="newOverride.max_children" type="number" min="0" max="20" :placeholder="t('any')" class="w-full px-1 py-1 border rounded text-xs" />
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-xs text-purple-500 mb-0.5">{{ t('rate') }} (%)</label>
                                            <input v-model.number="newOverride.rate_pct" type="number" min="0" max="100" step="0.01" class="w-full px-1 py-1 border rounded text-xs" required />
                                        </div>
                                        <button type="submit" class="px-3 py-1 bg-purple-600 text-white text-xs font-medium rounded hover:bg-purple-700 transition">
                                            {{ t('add') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <form @submit.prevent="addBracket" class="flex gap-2 items-end">
                    <div class="flex-1">
                        <label class="block text-xs text-gray-500 mb-1">{{ t('min_eur') }}</label>
                        <input v-model.number="newBracket.min_amount" type="number" min="0" step="0.01" class="w-full px-2 py-1.5 border rounded text-sm" required />
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs text-gray-500 mb-1">{{ t('max_eur') }}</label>
                        <input v-model.number="newBracket.max_amount" type="number" min="0" step="0.01" :placeholder="t('unlimited')" class="w-full px-2 py-1.5 border rounded text-sm" />
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs text-gray-500 mb-1">{{ t('rate') }} (%)</label>
                        <input v-model.number="newBracket.rate_pct" type="number" min="0" max="100" step="0.01" class="w-full px-2 py-1.5 border rounded text-sm" required />
                    </div>
                    <button type="submit" class="px-4 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded hover:bg-indigo-700 transition">
                        {{ t('add') }}
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ t('deductions') }}</h2>
                <table class="w-full text-sm mb-4">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2 text-gray-600">{{ t('name') }}</th>
                            <th class="text-left py-2 text-gray-600">{{ t('rate') }} (%)</th>
                            <th class="text-right py-2 text-gray-600">{{ t('actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="deduction in scale.deductions" :key="deduction.id" class="border-b border-gray-100">
                            <template v-if="editingDeduction?.id === deduction.id">
                                <td class="py-2 pr-2">
                                    <input v-model="editingDeduction.name" type="text" class="w-full px-2 py-1 border rounded text-sm" />
                                </td>
                                <td class="py-2 pr-2">
                                    <input v-model.number="editingDeduction.rate_pct" type="number" min="0" max="100" step="0.01" class="w-full px-2 py-1 border rounded text-sm" />
                                </td>
                                <td class="py-2 text-right space-x-1">
                                    <button @click="saveDeduction" class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded hover:bg-green-200">{{ t('save') }}</button>
                                    <button @click="editingDeduction = null" class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded hover:bg-gray-200">{{ t('cancel') }}</button>
                                </td>
                            </template>
                            <template v-else>
                                <td class="py-2">{{ deduction.name }}</td>
                                <td class="py-2">{{ (deduction.rate * 100).toFixed(2) }}%</td>
                                <td class="py-2 text-right space-x-1">
                                    <button @click="startEditDeduction(deduction)" class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200">{{ t('edit') }}</button>
                                    <button @click="deleteDeduction(deduction)" class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200">{{ t('delete') }}</button>
                                </td>
                            </template>
                        </tr>
                    </tbody>
                </table>

                <form @submit.prevent="addDeduction" class="flex gap-2 items-end">
                    <div class="flex-1">
                        <label class="block text-xs text-gray-500 mb-1">{{ t('name') }}</label>
                        <input v-model="newDeduction.name" type="text" :placeholder="t('name_example_social_security')" class="w-full px-2 py-1.5 border rounded text-sm" required />
                    </div>
                    <div class="w-32">
                        <label class="block text-xs text-gray-500 mb-1">{{ t('rate') }} (%)</label>
                        <input v-model.number="newDeduction.rate_pct" type="number" min="0" max="100" step="0.01" class="w-full px-2 py-1.5 border rounded text-sm" required />
                    </div>
                    <button type="submit" class="px-4 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded hover:bg-indigo-700 transition">
                        {{ t('add') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from '../../../composables/useI18n';

const props = defineProps({
    scale: Object,
    availableCountries: Object,
});

const { locale, supportedLocales, t, switchLocale } = useI18n();

const scaleName = ref(props.scale.name);
const scaleCountry = ref(props.scale.country_code);
const scaleSalariesPerYear = ref(Number(props.scale.salaries_per_year ?? 12));
const scaleState = ref(props.scale.state || '');

const editingBracket = ref(null);
const newBracket = ref({ min_amount: null, max_amount: null, rate_pct: null });

const expandedBracket = ref(null);
const editingOverride = ref(null);
const newOverride = ref({ min_age: null, max_age: null, min_children: 0, max_children: null, rate_pct: null });

function updateDetails() {
    router.put(`/admin/scales/${props.scale.id}`, {
        name: scaleName.value,
        country_code: scaleCountry.value,
        salaries_per_year: scaleSalariesPerYear.value,
        state: scaleState.value || null,
    });
}

function defaultSalariesForCountry(countryCode) {
    return countryCode === 'GR' ? 14 : 12;
}

function onEditCountryChange() {
    scaleSalariesPerYear.value = defaultSalariesForCountry(scaleCountry.value);
}

function addBracket() {
    router.post(`/admin/scales/${props.scale.id}/brackets`, {
        min_amount: newBracket.value.min_amount,
        max_amount: newBracket.value.max_amount || null,
        rate: newBracket.value.rate_pct / 100,
    }, {
        onSuccess: () => { newBracket.value = { min_amount: null, max_amount: null, rate_pct: null }; },
    });
}

function startEditBracket(bracket) {
    editingBracket.value = {
        id: bracket.id,
        min_amount: Number(bracket.min_amount),
        max_amount: bracket.max_amount ? Number(bracket.max_amount) : null,
        rate_pct: Number(bracket.rate) * 100,
    };
}

function saveBracket() {
    router.put(`/admin/scales/${props.scale.id}/brackets/${editingBracket.value.id}`, {
        min_amount: editingBracket.value.min_amount,
        max_amount: editingBracket.value.max_amount || null,
        rate: editingBracket.value.rate_pct / 100,
    }, {
        onSuccess: () => { editingBracket.value = null; },
    });
}

function deleteBracket(bracket) {
    if (confirm(t('delete_bracket_confirm'))) {
        router.delete(`/admin/scales/${props.scale.id}/brackets/${bracket.id}`);
    }
}

function toggleOverrides(bracketId) {
    expandedBracket.value = expandedBracket.value === bracketId ? null : bracketId;
    editingOverride.value = null;
    newOverride.value = { min_age: null, max_age: null, min_children: 0, max_children: null, rate_pct: null };
}

function addOverride(bracket) {
    router.post(`/admin/scales/${props.scale.id}/brackets/${bracket.id}/overrides`, {
        min_age: newOverride.value.min_age || null,
        max_age: newOverride.value.max_age || null,
        min_children: newOverride.value.min_children ?? 0,
        max_children: newOverride.value.max_children || null,
        rate: (newOverride.value.rate_pct ?? 0) / 100,
    }, {
        onSuccess: () => { newOverride.value = { min_age: null, max_age: null, min_children: 0, max_children: null, rate_pct: null }; },
    });
}

function startEditOverride(ov) {
    editingOverride.value = {
        id: ov.id,
        min_age: ov.min_age,
        max_age: ov.max_age,
        min_children: ov.min_children,
        max_children: ov.max_children,
        rate_pct: Number(ov.rate) * 100,
    };
}

function saveOverride(bracket) {
    router.put(`/admin/scales/${props.scale.id}/brackets/${bracket.id}/overrides/${editingOverride.value.id}`, {
        min_age: editingOverride.value.min_age || null,
        max_age: editingOverride.value.max_age || null,
        min_children: editingOverride.value.min_children ?? 0,
        max_children: editingOverride.value.max_children || null,
        rate: editingOverride.value.rate_pct / 100,
    }, {
        onSuccess: () => { editingOverride.value = null; },
    });
}

function deleteOverride(bracket, ov) {
    if (confirm(t('delete_override_confirm'))) {
        router.delete(`/admin/scales/${props.scale.id}/brackets/${bracket.id}/overrides/${ov.id}`);
    }
}

const editingDeduction = ref(null);
const newDeduction = ref({ name: '', rate_pct: null });

function addDeduction() {
    router.post(`/admin/scales/${props.scale.id}/deductions`, {
        name: newDeduction.value.name,
        rate: newDeduction.value.rate_pct / 100,
    }, {
        onSuccess: () => { newDeduction.value = { name: '', rate_pct: null }; },
    });
}

function startEditDeduction(deduction) {
    editingDeduction.value = {
        id: deduction.id,
        name: deduction.name,
        rate_pct: Number(deduction.rate) * 100,
    };
}

function saveDeduction() {
    router.put(`/admin/scales/${props.scale.id}/deductions/${editingDeduction.value.id}`, {
        name: editingDeduction.value.name,
        rate: editingDeduction.value.rate_pct / 100,
    }, {
        onSuccess: () => { editingDeduction.value = null; },
    });
}

function deleteDeduction(deduction) {
    if (confirm(t('delete_deduction_confirm'))) {
        router.delete(`/admin/scales/${props.scale.id}/deductions/${deduction.id}`);
    }
}
</script>
