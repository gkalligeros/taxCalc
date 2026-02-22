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
                        @click="handleLocaleSwitch(lang)"
                        class="px-2 py-1 text-xs rounded border transition"
                        :class="lang === locale ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-100'"
                    >
                        {{ lang === 'el' ? t('greek') : t('english') }}
                    </button>
                </div>
            </header>

            <section class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6">
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
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">{{ currencySymbol }}</span>
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
            </section>

            <div v-if="result" class="space-y-4">
                <section class="bg-white rounded-lg shadow p-4 sm:p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ t('summary') }}</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="text-sm text-gray-500">{{ t('gross_salary') }}</div>
                            <div class="text-2xl font-bold text-gray-800">{{ formatCurrency(result.gross) }}</div>
                        </div>
                        <div class="p-4 bg-green-50 rounded-lg">
                            <div class="text-sm text-green-600">{{ t('net_salary') }}</div>
                            <div class="text-2xl font-bold text-green-700">{{ formatCurrency(result.net) }}</div>
                        </div>
                        <div class="p-4 bg-orange-50 rounded-lg">
                            <div class="text-sm text-orange-600">{{ t('total_deductions') }}</div>
                            <div class="text-xl font-semibold text-orange-700">{{ formatCurrency(result.total_deductions) }}</div>
                        </div>
                        <div class="p-4 bg-red-50 rounded-lg">
                            <div class="text-sm text-red-600">{{ t('income_tax') }}</div>
                            <div class="text-xl font-semibold text-red-700">{{ formatCurrency(result.tax) }}</div>
                        </div>
                    </div>
                    <div v-if="result.tax_exemption_amount > 0" class="mt-4 p-4 bg-purple-50 rounded-lg">
                        <div class="text-sm text-purple-600">{{ t('tax_exemption_amount') }}</div>
                        <div class="text-xl font-semibold text-purple-700">{{ formatCurrency(result.tax_exemption_amount) }}</div>
                    </div>
                    <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                        <div class="text-sm text-blue-600">{{ t('taxable_income_after_deductions') }}</div>
                        <div class="text-xl font-semibold text-blue-700">{{ formatCurrency(result.taxable_income) }}</div>
                    </div>
                </section>

                <section class="bg-white rounded-lg shadow p-4 sm:p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ t('salary_breakdown_chart') }}</h2>
                    <div class="max-w-sm mx-auto">
                        <Doughnut :data="chartData" :options="chartOptions" />
                    </div>
                </section>

                <section v-if="result.world_percentile !== undefined" class="bg-white rounded-lg shadow p-4 sm:p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-5">{{ t('income_percentile') }}</h2>

                    <!-- Country percentile -->
                    <div v-if="result.country_percentile !== null" class="mb-5">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">
                                {{ t('in_country', { country: countries[selectedCountry] || selectedCountry }) }}
                            </span>
                            <span class="text-sm font-bold text-indigo-600">
                                {{ t('top_percent', { pct: (100 - result.country_percentile).toFixed(1) }) }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mb-2">
                            {{ t('earn_more_than_country', { pct: result.country_percentile.toFixed(1), country: countries[selectedCountry] || selectedCountry }) }}
                        </p>
                        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                            <div
                                class="bg-indigo-500 h-3 rounded-full transition-all duration-700"
                                :style="{ width: Math.min(result.country_percentile, 100) + '%' }"
                            ></div>
                        </div>
                    </div>
                    <div v-else-if="result.country_available === false" class="mb-5 text-xs text-gray-400 italic">
                        {{ t('country_data_unavailable') }}
                    </div>

                    <!-- World percentile -->
                    <div class="mb-5">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">{{ t('worldwide') }}</span>
                            <span class="text-sm font-bold text-emerald-600">
                                {{ t('top_percent', { pct: (100 - result.world_percentile).toFixed(1) }) }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mb-2">
                            {{ t('earn_more_than_world', { pct: result.world_percentile.toFixed(1) }) }}
                        </p>
                        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                            <div
                                class="bg-emerald-500 h-3 rounded-full transition-all duration-700"
                                :style="{ width: Math.min(result.world_percentile, 100) + '%' }"
                            ></div>
                        </div>
                    </div>

                    <!-- Global income distribution bar chart -->
                    <div v-if="distributionChartData" class="mt-6">
                        <h3 class="text-sm font-medium text-gray-600 mb-3">{{ t('global_income_distribution') }}</h3>
                        <Bar :data="distributionChartData" :options="distributionChartOptions" />
                    </div>

                    <p class="mt-4 text-xs text-gray-400">* {{ t('percentile_disclaimer') }}</p>
                </section>

                <section v-if="result.live_total_count > 0" class="bg-white rounded-lg shadow p-4 sm:p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-1">{{ t('calculator_users_stats') }}</h2>
                    <p class="text-xs text-gray-400 mb-5">{{ t('based_on_calculations', { count: result.live_total_count.toLocaleString() }) }}</p>

                    <!-- Country rank -->
                    <div v-if="result.live_country_count > 0" class="mb-5">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">
                                {{ t('your_rank_in_country', { country: countries[selectedCountry] || selectedCountry, count: result.live_country_count.toLocaleString() }) }}
                            </span>
                            <span class="text-sm font-bold text-indigo-600">
                                {{ t('top_percent', { pct: (100 - result.live_country_percentile).toFixed(1) }) }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mb-2">
                            {{ t('earn_more_than_users_country', { pct: result.live_country_percentile.toFixed(1), country: countries[selectedCountry] || selectedCountry }) }}
                        </p>
                        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                            <div
                                class="bg-indigo-400 h-3 rounded-full transition-all duration-700"
                                :style="{ width: Math.min(result.live_country_percentile, 100) + '%' }"
                            ></div>
                        </div>
                        <div v-if="result.live_country_avg_gross" class="mt-2 flex gap-4 text-xs text-gray-500">
                            <span>{{ t('avg_gross') }}: <strong class="text-gray-700">{{ formatCurrency(result.live_country_avg_gross) }}</strong></span>
                            <span v-if="result.live_country_avg_net">{{ t('avg_net') }}: <strong class="text-gray-700">{{ formatCurrency(result.live_country_avg_net) }}</strong></span>
                        </div>
                    </div>

                    <!-- Global rank -->
                    <div v-if="result.live_total_percentile !== null" class="mb-1">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">
                                {{ t('your_rank_globally', { count: result.live_total_count.toLocaleString() }) }}
                            </span>
                            <span class="text-sm font-bold text-emerald-600">
                                {{ t('top_percent', { pct: (100 - result.live_total_percentile).toFixed(1) }) }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mb-2">
                            {{ t('earn_more_than_users_all', { pct: result.live_total_percentile.toFixed(1) }) }}
                        </p>
                        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                            <div
                                class="bg-emerald-400 h-3 rounded-full transition-all duration-700"
                                :style="{ width: Math.min(result.live_total_percentile, 100) + '%' }"
                            ></div>
                        </div>
                        <div v-if="result.live_total_avg_gross" class="mt-2 flex gap-4 text-xs text-gray-500">
                            <span>{{ t('avg_gross') }}: <strong class="text-gray-700">{{ formatCurrency(result.live_total_avg_gross) }}</strong></span>
                            <span v-if="result.live_total_avg_net">{{ t('avg_net') }}: <strong class="text-gray-700">{{ formatCurrency(result.live_total_avg_net) }}</strong></span>
                        </div>
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
                            <div class="text-lg font-semibold">{{ formatCurrency(result.gross / salariesPerYear) }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">{{ t('monthly_deductions_tax') }}</div>
                            <div class="text-lg font-semibold text-red-600">{{ formatCurrency((result.total_deductions + result.tax) / salariesPerYear) }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-green-600">{{ t('monthly_net') }}</div>
                            <div class="text-lg font-bold text-green-700">{{ formatCurrency(result.net / salariesPerYear) }}</div>
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
                                <span class="font-semibold text-gray-800">{{ formatCurrency(d.amount) }}</span>
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
                                    <td class="text-right py-2 font-medium">{{ formatCurrency(d.amount) }}</td>
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
                                {{ formatCurrency(b.min) }}
                                &ndash;
                                <span v-if="b.max">{{ formatCurrency(b.max) }}</span>
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
                                <span>{{ formatCurrency(b.taxable_amount) }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>{{ t('tax') }}</span>
                                <span class="font-semibold text-gray-800">{{ formatCurrency(b.tax) }}</span>
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
                                        {{ formatCurrency(b.min) }}
                                        &ndash;
                                        <span v-if="b.max">{{ formatCurrency(b.max) }}</span>
                                        <span v-else>&infin;</span>
                                    </td>
                                    <td class="text-right py-2 text-gray-400">{{ (b.base_rate * 100).toFixed(0) }}%</td>
                                    <td class="text-right py-2" :class="b.rate !== b.base_rate ? 'text-green-600 font-semibold' : ''">
                                        {{ (b.rate * 100).toFixed(0) }}%
                                        <span v-if="b.rate !== b.base_rate" class="text-xs ml-1">({{ t('override') }})</span>
                                    </td>
                                    <td class="text-right py-2">{{ formatCurrency(b.taxable_amount) }}</td>
                                    <td class="text-right py-2 font-medium">{{ formatCurrency(b.tax) }}</td>
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
import { COUNTRY_CURRENCIES, getCurrencySymbol, formatCurrencyValue } from '../utils/currencies';
import CookieBanner from '../Components/CookieBanner.vue';
import { Chart as ChartJS, ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement, Title } from 'chart.js';
import { Doughnut, Bar } from 'vue-chartjs';

ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement, Title);

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
const currentCurrency = computed(() =>
    props.activeScale?.currency ?? COUNTRY_CURRENCIES[selectedCountry.value] ?? 'EUR'
);
const currencySymbol = computed(() => getCurrencySymbol(currentCurrency.value, locale.value));
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
                    return `${ctx.label}: ${formatCurrencyValue(val, currentCurrency.value, locale.value)} (${pct}%)`;
                },
            },
        },
    },
};

// Income buckets for the world distribution bar chart (annual gross in EUR)
const WORLD_BUCKETS = [
    { label: '< €2k',      min: 0,      max: 2000 },
    { label: '€2k–5k',     min: 2000,   max: 5000 },
    { label: '€5k–15k',    min: 5000,   max: 15000 },
    { label: '€15k–30k',   min: 15000,  max: 30000 },
    { label: '€30k–50k',   min: 30000,  max: 50000 },
    { label: '€50k–80k',   min: 50000,  max: 80000 },
    { label: '> €80k',     min: 80000,  max: Infinity },
];

function interpolatePercentileFromDist(income, distribution) {
    if (!distribution || distribution.length === 0) return 0;
    if (income <= distribution[0].income) return 0;
    if (income >= distribution[distribution.length - 1].income) return 100;
    for (let i = 1; i < distribution.length; i++) {
        if (income <= distribution[i].income) {
            const ratio = (income - distribution[i - 1].income) / (distribution[i].income - distribution[i - 1].income);
            return distribution[i - 1].percentile + ratio * (distribution[i].percentile - distribution[i - 1].percentile);
        }
    }
    return 100;
}

const distributionChartData = computed(() => {
    if (!result.value?.world_distribution) return null;

    const dist = result.value.world_distribution;
    const gross = result.value.gross;

    // Find which bucket the user falls in
    const userBucketIdx = WORLD_BUCKETS.findIndex((b) =>
        gross >= b.min && (b.max === Infinity ? true : gross < b.max)
    );

    const populations = WORLD_BUCKETS.map((b) => {
        const lowerPct = interpolatePercentileFromDist(b.min, dist);
        const upperPct = b.max === Infinity ? 100 : interpolatePercentileFromDist(b.max, dist);
        return Math.round((upperPct - lowerPct) * 10) / 10;
    });

    return {
        labels: WORLD_BUCKETS.map((b) => b.label),
        datasets: [
            {
                data: populations,
                backgroundColor: WORLD_BUCKETS.map((_, i) =>
                    i === userBucketIdx ? '#10b981' : 'rgba(99, 102, 241, 0.2)'
                ),
                borderColor: WORLD_BUCKETS.map((_, i) =>
                    i === userBucketIdx ? '#059669' : 'rgba(99, 102, 241, 0.5)'
                ),
                borderWidth: 1,
                borderRadius: 4,
            },
        ],
    };
});

const distributionChartOptions = computed(() => ({
    responsive: true,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                title: (items) => {
                    const idx = items[0].dataIndex;
                    const isUser = result.value?.gross >= WORLD_BUCKETS[idx].min &&
                        (WORLD_BUCKETS[idx].max === Infinity || result.value?.gross < WORLD_BUCKETS[idx].max);
                    return isUser
                        ? `${WORLD_BUCKETS[idx].label} ← ${t('your_income_bracket')}`
                        : WORLD_BUCKETS[idx].label;
                },
                label: (ctx) => `${ctx.parsed.y.toFixed(1)}% ${t('world_population_pct')}`,
            },
        },
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: { font: { size: 11 } },
        },
        y: {
            title: {
                display: true,
                text: t('world_population_pct'),
                font: { size: 11 },
            },
            ticks: {
                callback: (v) => `${v}%`,
                font: { size: 11 },
            },
        },
    },
}));

const jsonLd = computed(() => JSON.stringify({
    '@context': 'https://schema.org',
    '@type': 'WebApplication',
    name: t('seo_title'),
    description: t('seo_description'),
    applicationCategory: 'FinanceApplication',
    offers: {
        '@type': 'Offer',
        price: '0',
        priceCurrency: currentCurrency.value,
    },
}));

let autoCalculateTimer = null;
let latestRequestId = 0;

function formatCurrency(n) {
    return formatCurrencyValue(n, currentCurrency.value, locale.value);
}

const CALC_STATE_KEY = 'calc_form_state';

function handleLocaleSwitch(lang) {
    sessionStorage.setItem(CALC_STATE_KEY, JSON.stringify({
        amount: amount.value,
        mode: mode.value,
        inputPeriod: inputPeriod.value,
        age: age.value,
        children: children.value,
        selectedSalariesPerYear: selectedSalariesPerYear.value,
    }));
    switchLocale(lang);
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
    // Restore form state after locale switch
    const saved = sessionStorage.getItem(CALC_STATE_KEY);
    if (saved) {
        try {
            const s = JSON.parse(saved);
            if (s.amount != null) amount.value = s.amount;
            if (s.mode) mode.value = s.mode;
            if (s.inputPeriod) inputPeriod.value = s.inputPeriod;
            if (s.age != null) age.value = s.age;
            if (s.children != null) children.value = s.children;
            if (s.selectedSalariesPerYear != null) selectedSalariesPerYear.value = s.selectedSalariesPerYear;
        } catch {}
        sessionStorage.removeItem(CALC_STATE_KEY);
    }

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
