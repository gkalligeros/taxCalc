<template>
    <Head>
        <title>{{ t('seo_title') }}</title>
        <meta name="description" :content="t('seo_description')" />
        <link rel="canonical" :href="canonicalUrl" />
        <meta property="og:title" :content="t('seo_title')" />
        <meta property="og:description" :content="t('seo_description')" />
        <meta property="og:type" content="website" />
        <meta property="og:url" :content="canonicalUrl" />
        <meta property="og:locale" :content="locale === 'el' ? 'el_GR' : 'en_US'" />
        <meta property="og:site_name" :content="t('net_salary_calculator')" />
        <meta name="twitter:card" content="summary" />
        <meta name="twitter:title" :content="t('seo_title')" />
        <meta name="twitter:description" :content="t('seo_description')" />
    </Head>
    <main class="min-h-screen bg-gray-100 py-12">
        <div class="max-w-3xl mx-auto px-4">
            <header class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 sm:mb-8 gap-3">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">{{ t('net_salary_calculator') }}</h1>
                <div class="flex items-center gap-2 flex-wrap">
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
            </header>

            <section class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6">
                <h2 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">{{ t('region') }} & {{ t('personal_info') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ t('country') }}</label>
                        <select
                            v-model="selectedCountry"
                            @change="onCountryChange"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                        >
                            <option v-for="cc in availableCountries" :key="cc" :value="cc">
                                {{ cc }} - {{ countries[cc] || cc }}
                            </option>
                        </select>
                    </div>
                    <div v-if="availableStates.length">
                        <label class="block text-xs text-gray-500 mb-1">{{ t('state_region') }}</label>
                        <select
                            v-model="selectedState"
                            @change="onStateChange"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                        >
                            <option :value="null">{{ t('all_national') }}</option>
                            <option v-for="s in availableStates" :key="s" :value="s">{{ s }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ t('age') }}</label>
                        <input
                            v-model.number="age"
                            type="number"
                            min="16"
                            max="100"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ t('dependent_children') }}</label>
                        <input
                            v-model.number="children"
                            type="number"
                            min="0"
                            max="20"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                        />
                    </div>
                </div>
                <div v-if="activeScale?.tax_exemptions?.length" class="mt-4 space-y-2">
                    <label
                        v-for="exemption in activeScale.tax_exemptions"
                        :key="exemption.id"
                        class="flex items-start gap-2 cursor-pointer"
                    >
                        <input
                            type="checkbox"
                            :checked="selectedExemptionIds.has(exemption.id)"
                            @change="toggleExemption(exemption.id)"
                            class="mt-0.5 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        />
                        <span class="text-sm">
                            <span class="font-medium text-gray-800">{{ exemption.name }} ({{ (exemption.rate * 100).toFixed(0) }}%)</span>
                            <br v-if="exemption.description" />
                            <span v-if="exemption.description" class="text-gray-500 text-xs">{{ exemption.description }}</span>
                        </span>
                    </label>
                </div>
                <div v-if="activeScale" class="mt-4 text-sm text-gray-500">
                    {{ t('using_tax_scale', { name: activeScale.name }) }}
                </div>
                <div v-else class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded text-yellow-800 text-sm">
                    {{ t('no_active_scale') }}
                </div>
                <div class="flex flex-wrap gap-2 mb-4">
                    <button
                        @click="mode = 'gross_to_net'; result = null"
                        class="px-3 py-1.5 text-sm rounded border transition"
                        :class="mode === 'gross_to_net' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-100'"
                    >
                        {{ t('gross_to_net') }}
                    </button>
                    <button
                        @click="mode = 'net_to_gross'; result = null"
                        class="px-3 py-1.5 text-sm rounded border transition"
                        :class="mode === 'net_to_gross' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-100'"
                    >
                        {{ t('net_to_gross') }}
                    </button>
                </div>
                <div class="mb-4 w-full sm:max-w-xs">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ t('salaries_per_year') }}</label>
                    <select
                        v-model.number="selectedSalariesPerYear"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                    >
                        <option :value="12">{{ t('salary_division_12') }}</option>
                        <option :value="14">{{ t('salary_division_14') }}</option>
                    </select>
                </div>
                <div class="flex flex-wrap gap-2 mb-4">
                    <button
                        @click="inputPeriod = 'annual'; result = null"
                        class="px-3 py-1.5 text-sm rounded border transition"
                        :class="inputPeriod === 'annual' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-100'"
                    >
                        {{ t('annual') }}
                    </button>
                    <button
                        @click="inputPeriod = 'monthly'; result = null"
                        class="px-3 py-1.5 text-sm rounded border transition"
                        :class="inputPeriod === 'monthly' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-100'"
                    >
                        {{ t('monthly') }}
                    </button>
                </div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ inputLabel }}</label>
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">&euro;</span>
                        <input
                            v-model.number="amount"
                            type="number"
                            min="0"
                            :step="inputPeriod === 'monthly' ? 50 : 100"
                            :placeholder="inputPeriod === 'monthly' ? t('monthly_salary_example_placeholder') : t('salary_example_placeholder')"
                            class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-lg"
                            @keyup.enter="calculate"
                        />
                    </div>
                    <button
                        @click="calculate"
                        :disabled="loading || !amount"
                        class="w-full sm:w-auto px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    >
                        {{ loading ? t('calculating') : t('calculate') }}
                    </button>
                </div>
                <div v-if="apiError" class="mt-3 text-sm text-red-600">
                    {{ apiError }}
                </div>
            </section>

            <div v-if="result" class="space-y-4">
                <section class="bg-white rounded-lg shadow p-4 sm:p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ t('summary') }}</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="text-sm text-gray-500">{{ t('gross_salary') }}</div>
                            <div class="text-2xl font-bold text-gray-800">&euro;{{ formatNumber(result.gross) }}</div>
                        </div>
                        <div class="p-4 bg-green-50 rounded-lg">
                            <div class="text-sm text-green-600">{{ t('net_salary') }}</div>
                            <div class="text-2xl font-bold text-green-700">&euro;{{ formatNumber(result.net) }}</div>
                        </div>
                        <div class="p-4 bg-orange-50 rounded-lg">
                            <div class="text-sm text-orange-600">{{ t('total_deductions') }}</div>
                            <div class="text-xl font-semibold text-orange-700">&euro;{{ formatNumber(result.total_deductions) }}</div>
                        </div>
                        <div class="p-4 bg-red-50 rounded-lg">
                            <div class="text-sm text-red-600">{{ t('income_tax') }}</div>
                            <div class="text-xl font-semibold text-red-700">&euro;{{ formatNumber(result.tax) }}</div>
                        </div>
                    </div>
                    <div v-if="result.tax_exemption_amount > 0" class="mt-4 p-4 bg-purple-50 rounded-lg">
                        <div class="text-sm text-purple-600">{{ t('tax_exemption_amount') }}</div>
                        <div class="text-xl font-semibold text-purple-700">&euro;{{ formatNumber(result.tax_exemption_amount) }}</div>
                    </div>
                    <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                        <div class="text-sm text-blue-600">{{ t('taxable_income_after_deductions') }}</div>
                        <div class="text-xl font-semibold text-blue-700">&euro;{{ formatNumber(result.taxable_income) }}</div>
                    </div>
                </section>

                <section class="bg-white rounded-lg shadow p-4 sm:p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ t('salary_breakdown_chart') }}</h2>
                    <div class="max-w-sm mx-auto">
                        <Doughnut :data="chartData" :options="chartOptions" />
                    </div>
                </section>

                <section class="bg-white rounded-lg shadow p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-1">
                        <h2 class="text-lg font-semibold text-gray-800">{{ t('monthly_breakdown') }}</h2>
                        <span class="text-xs text-gray-500">{{ t('salaries_per_year') }}: {{ salariesPerYear }}</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center">
                        <div>
                            <div class="text-sm text-gray-500">{{ t('monthly_gross') }}</div>
                            <div class="text-lg font-semibold">&euro;{{ formatNumber(result.gross / salariesPerYear) }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">{{ t('monthly_deductions_tax') }}</div>
                            <div class="text-lg font-semibold text-red-600">&euro;{{ formatNumber((result.total_deductions + result.tax) / salariesPerYear) }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-green-600">{{ t('monthly_net') }}</div>
                            <div class="text-lg font-bold text-green-700">&euro;{{ formatNumber(result.net / salariesPerYear) }}</div>
                        </div>
                    </div>
                </section>

                <section v-if="result?.deductions_breakdown?.length" class="bg-white rounded-lg shadow p-4 sm:p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ t('deductions_breakdown') }}</h2>
                    <div class="space-y-2 md:hidden">
                        <div
                            v-for="d in result.deductions_breakdown"
                            :key="`mobile-${d.name}`"
                            class="border border-gray-200 rounded-lg p-3"
                        >
                            <div class="font-medium text-gray-800">{{ d.name }}</div>
                            <div class="mt-1 flex justify-between text-sm text-gray-600">
                                <span>{{ t('rate') }}</span>
                                <span>{{ (d.rate * 100).toFixed(2) }}%</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>{{ t('amount') }}</span>
                                <span class="font-semibold text-gray-800">&euro;{{ formatNumber(d.amount) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-sm min-w-[420px]">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2 text-gray-600">{{ t('deduction') }}</th>
                                    <th class="text-right py-2 text-gray-600">{{ t('rate') }}</th>
                                    <th class="text-right py-2 text-gray-600">{{ t('amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="d in result.deductions_breakdown" :key="d.name" class="border-b border-gray-100">
                                    <td class="py-2">{{ d.name }}</td>
                                    <td class="text-right py-2">{{ (d.rate * 100).toFixed(2) }}%</td>
                                    <td class="text-right py-2 font-medium">&euro;{{ formatNumber(d.amount) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section v-if="result?.tax_breakdown?.length" class="bg-white rounded-lg shadow p-4 sm:p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ t('tax_brackets_breakdown') }}</h2>
                    <div class="space-y-2 md:hidden">
                        <div
                            v-for="(b, i) in result.tax_breakdown"
                            :key="`mobile-tax-${i}`"
                            class="border border-gray-200 rounded-lg p-3"
                        >
                            <div class="font-medium text-gray-800">
                                &euro;{{ formatNumber(b.min) }}
                                &ndash;
                                <span v-if="b.max">&euro;{{ formatNumber(b.max) }}</span>
                                <span v-else>&infin;</span>
                            </div>
                            <div class="mt-1 flex justify-between text-sm text-gray-600">
                                <span>{{ t('base_rate') }}</span>
                                <span>{{ (b.base_rate * 100).toFixed(0) }}%</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>{{ t('effective_rate') }}</span>
                                <span :class="b.rate !== b.base_rate ? 'text-green-600 font-semibold' : ''">
                                    {{ (b.rate * 100).toFixed(0) }}%
                                </span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>{{ t('taxable_amount') }}</span>
                                <span>&euro;{{ formatNumber(b.taxable_amount) }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>{{ t('tax') }}</span>
                                <span class="font-semibold text-gray-800">&euro;{{ formatNumber(b.tax) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-sm min-w-[720px]">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2 text-gray-600">{{ t('bracket') }}</th>
                                    <th class="text-right py-2 text-gray-600">{{ t('base_rate') }}</th>
                                    <th class="text-right py-2 text-gray-600">{{ t('effective_rate') }}</th>
                                    <th class="text-right py-2 text-gray-600">{{ t('taxable_amount') }}</th>
                                    <th class="text-right py-2 text-gray-600">{{ t('tax') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(b, i) in result.tax_breakdown" :key="i" class="border-b border-gray-100">
                                    <td class="py-2">
                                        &euro;{{ formatNumber(b.min) }}
                                        &ndash;
                                        <span v-if="b.max">&euro;{{ formatNumber(b.max) }}</span>
                                        <span v-else>&infin;</span>
                                    </td>
                                    <td class="text-right py-2 text-gray-400">{{ (b.base_rate * 100).toFixed(0) }}%</td>
                                    <td class="text-right py-2" :class="b.rate !== b.base_rate ? 'text-green-600 font-semibold' : ''">
                                        {{ (b.rate * 100).toFixed(0) }}%
                                        <span v-if="b.rate !== b.base_rate" class="text-xs ml-1">({{ t('override') }})</span>
                                    </td>
                                    <td class="text-right py-2">&euro;{{ formatNumber(b.taxable_amount) }}</td>
                                    <td class="text-right py-2 font-medium">&euro;{{ formatNumber(b.tax) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </main>
    <footer class="py-6 text-center text-sm text-gray-500">
        <span>{{ t('built_by') }}</span>
        <a href="https://www.linkedin.com/in/george-kalligeros-a2a1b586/" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:text-indigo-800 underline ml-1">George Kalligeros</a>
        <p class="mt-2 text-xs text-gray-400">{{ t('privacy_disclaimer') }}</p>
    </footer>
    <CookieBanner />
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch, watchEffect } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from '../composables/useI18n';
import CookieBanner from '../Components/CookieBanner.vue';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';
import { Doughnut } from 'vue-chartjs';

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps({
    activeScale: Object,
    selectedCountry: String,
    selectedState: String,
    availableCountries: Array,
    availableStates: Array,
});

const { locale, supportedLocales, countries, t, switchLocale } = useI18n();

const canonicalUrl = typeof window !== 'undefined' ? window.location.origin : '';

const selectedCountry = ref(props.selectedCountry || 'GR');
const selectedState = ref(props.selectedState || null);
const age = ref(31);
const children = ref(0);
const mode = ref('gross_to_net');
const inputPeriod = ref('annual');
const amount = ref(null);
const result = ref(null);
const loading = ref(false);
const apiError = ref('');
const selectedExemptionIds = ref(new Set());
const taxExemptionRate = computed(() => {
    if (!props.activeScale?.tax_exemptions?.length) return 0;
    let total = 0;
    for (const ex of props.activeScale.tax_exemptions) {
        if (selectedExemptionIds.value.has(ex.id)) {
            total += Number(ex.rate);
        }
    }
    return Math.min(total, 1.0);
});

function toggleExemption(id) {
    const next = new Set(selectedExemptionIds.value);
    if (next.has(id)) {
        next.delete(id);
    } else {
        next.add(id);
    }
    selectedExemptionIds.value = next;
}
const selectedSalariesPerYear = ref(Number(props.activeScale?.salaries_per_year ?? 12));
const salariesPerYear = computed(() => selectedSalariesPerYear.value);
const inputLabel = computed(() => {
    if (inputPeriod.value === 'monthly') {
        return mode.value === 'net_to_gross' ? t('target_monthly_net_salary') : t('monthly_gross_salary');
    }
    return mode.value === 'net_to_gross' ? t('target_annual_net_salary') : t('annual_gross_salary');
});
const chartData = computed(() => {
    if (!result.value) return { labels: [], datasets: [] };

    const labels = [t('net_salary'), t('total_deductions'), t('income_tax')];
    const data = [result.value.net, result.value.total_deductions, result.value.tax];
    const colors = ['#16a34a', '#ea580c', '#dc2626'];

    if (result.value.tax_exemption_amount > 0) {
        labels.push(t('tax_exemption_amount'));
        data.push(result.value.tax_exemption_amount);
        colors.push('#9333ea');
    }

    return {
        labels,
        datasets: [{
            data,
            backgroundColor: colors,
            borderWidth: 1,
            borderColor: '#fff',
        }],
    };
});

const chartOptions = {
    responsive: true,
    plugins: {
        legend: { position: 'bottom' },
        tooltip: {
            callbacks: {
                label: (ctx) => {
                    const val = ctx.parsed;
                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                    const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                    return `${ctx.label}: €${Number(val).toLocaleString(undefined, { minimumFractionDigits: 2 })} (${pct}%)`;
                },
            },
        },
    },
};

const jsonLd = computed(() => JSON.stringify({
    '@context': 'https://schema.org',
    '@type': 'WebApplication',
    name: t('seo_title'),
    description: t('seo_description'),
    applicationCategory: 'FinanceApplication',
    offers: {
        '@type': 'Offer',
        price: '0',
        priceCurrency: 'EUR',
    },
}));

let autoCalculateTimer = null;
let latestRequestId = 0;

function formatNumber(n) {
    return Number(n).toLocaleString(locale.value, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function onCountryChange() {
    selectedState.value = null;
    result.value = null;
    selectedExemptionIds.value = new Set();
    router.get('/', { country_code: selectedCountry.value }, { preserveState: false });
}

function onStateChange() {
    result.value = null;
    selectedExemptionIds.value = new Set();
    const params = { country_code: selectedCountry.value };
    if (selectedState.value) params.state = selectedState.value;
    router.get('/', params, { preserveState: false });
}

async function calculate() {
    if (!amount.value || amount.value <= 0) return;
    const requestId = ++latestRequestId;
    loading.value = true;
    try {
        const annualAmount = inputPeriod.value === 'monthly'
            ? amount.value * salariesPerYear.value
            : amount.value;

        const payload = {
            mode: mode.value,
            amount: annualAmount,
            country_code: selectedCountry.value,
            state: selectedState.value,
            age: age.value,
            children: children.value,
            tax_exemption_rate: taxExemptionRate.value,
        };

        if (mode.value === 'net_to_gross') {
            payload.net = annualAmount;
        } else {
            payload.gross = annualAmount;
        }

        const res = await fetch('/api/calculate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await res.json();

        if (!res.ok) {
            if (requestId === latestRequestId) {
                result.value = null;
                apiError.value = data?.message || 'Calculation failed.';
            }
            return;
        }

        if (requestId === latestRequestId) {
            result.value = data;
            apiError.value = '';
        }
    } catch (e) {
        console.error(e);
        if (requestId === latestRequestId) {
            result.value = null;
            apiError.value = 'Calculation failed.';
        }
    } finally {
        if (requestId === latestRequestId) {
            loading.value = false;
        }
    }
}

function scheduleAutoCalculate() {
    if (autoCalculateTimer) {
        clearTimeout(autoCalculateTimer);
    }

    if (!amount.value || amount.value <= 0) {
        result.value = null;
        apiError.value = '';
        loading.value = false;
        return;
    }

    autoCalculateTimer = setTimeout(() => {
        calculate();
    }, 250);
}

watch([amount, mode, inputPeriod, age, children, taxExemptionRate, selectedSalariesPerYear], () => {
    scheduleAutoCalculate();
});

let jsonLdScript = null;

onMounted(() => {
    jsonLdScript = document.createElement('script');
    jsonLdScript.type = 'application/ld+json';
    jsonLdScript.textContent = jsonLd.value;
    document.head.appendChild(jsonLdScript);
});

watchEffect(() => {
    if (jsonLdScript) {
        jsonLdScript.textContent = jsonLd.value;
    }
});

onBeforeUnmount(() => {
    if (autoCalculateTimer) {
        clearTimeout(autoCalculateTimer);
    }
    if (jsonLdScript) {
        jsonLdScript.remove();
    }
});
</script>
