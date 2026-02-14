<template>
    <div class="min-h-screen bg-gray-100 py-12">
        <div class="max-w-3xl mx-auto px-4">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Net Salary Calculator</h1>
                <a href="/admin/scales" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                    Admin Panel &rarr;
                </a>
            </div>

            <!-- Region Selector -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">Region</h2>
                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-xs text-gray-500 mb-1">Country</label>
                        <select
                            v-model="selectedCountry"
                            @change="onCountryChange"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                        >
                            <option v-for="cc in availableCountries" :key="cc" :value="cc">
                                {{ cc }}
                            </option>
                        </select>
                    </div>
                    <div v-if="availableStates.length" class="flex-1">
                        <label class="block text-xs text-gray-500 mb-1">State / Region</label>
                        <select
                            v-model="selectedState"
                            @change="onStateChange"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                        >
                            <option :value="null">All / National</option>
                            <option v-for="s in availableStates" :key="s" :value="s">{{ s }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Personal Info -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">Personal Info</h2>
                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-xs text-gray-500 mb-1">Age</label>
                        <input
                            v-model.number="age"
                            type="number"
                            min="16"
                            max="100"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                        />
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs text-gray-500 mb-1">Dependent Children</label>
                        <input
                            v-model.number="children"
                            type="number"
                            min="0"
                            max="20"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                        />
                    </div>
                </div>
            </div>

            <!-- Active Scale Info -->
            <div v-if="activeScale" class="mb-4 text-sm text-gray-500">
                Using tax scale: <span class="font-semibold text-gray-700">{{ activeScale.name }}</span>
            </div>
            <div v-else class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded text-yellow-800 text-sm">
                No active tax scale configured for this region. Please set one up in the admin panel.
            </div>

            <!-- Input Card -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Annual Gross Salary</label>
                <div class="flex gap-3">
                    <div class="relative flex-1">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">&euro;</span>
                        <input
                            v-model.number="gross"
                            type="number"
                            min="0"
                            step="100"
                            placeholder="e.g. 30000"
                            class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-lg"
                            @keyup.enter="calculate"
                        />
                    </div>
                    <button
                        @click="calculate"
                        :disabled="loading || !gross"
                        class="px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    >
                        {{ loading ? 'Calculating...' : 'Calculate' }}
                    </button>
                </div>
            </div>

            <!-- Results -->
            <div v-if="result" class="space-y-4">
                <!-- Summary Card -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Summary</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="text-sm text-gray-500">Gross Salary</div>
                            <div class="text-2xl font-bold text-gray-800">&euro;{{ formatNumber(result.gross) }}</div>
                        </div>
                        <div class="p-4 bg-green-50 rounded-lg">
                            <div class="text-sm text-green-600">Net Salary</div>
                            <div class="text-2xl font-bold text-green-700">&euro;{{ formatNumber(result.net) }}</div>
                        </div>
                        <div class="p-4 bg-orange-50 rounded-lg">
                            <div class="text-sm text-orange-600">Total Deductions</div>
                            <div class="text-xl font-semibold text-orange-700">&euro;{{ formatNumber(result.total_deductions) }}</div>
                        </div>
                        <div class="p-4 bg-red-50 rounded-lg">
                            <div class="text-sm text-red-600">Income Tax</div>
                            <div class="text-xl font-semibold text-red-700">&euro;{{ formatNumber(result.tax) }}</div>
                        </div>
                    </div>
                    <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                        <div class="text-sm text-blue-600">Taxable Income (after deductions)</div>
                        <div class="text-xl font-semibold text-blue-700">&euro;{{ formatNumber(result.taxable_income) }}</div>
                    </div>
                </div>

                <!-- Deductions Breakdown -->
                <div v-if="result.deductions_breakdown.length" class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Deductions Breakdown</h2>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2 text-gray-600">Deduction</th>
                                <th class="text-right py-2 text-gray-600">Rate</th>
                                <th class="text-right py-2 text-gray-600">Amount</th>
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

                <!-- Tax Brackets Breakdown -->
                <div v-if="result.tax_breakdown.length" class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Tax Brackets Breakdown</h2>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2 text-gray-600">Bracket</th>
                                <th class="text-right py-2 text-gray-600">Base Rate</th>
                                <th class="text-right py-2 text-gray-600">Effective Rate</th>
                                <th class="text-right py-2 text-gray-600">Taxable Amount</th>
                                <th class="text-right py-2 text-gray-600">Tax</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(b, i) in result.tax_breakdown" :key="i" class="border-b border-gray-100">
                                <td class="py-2">
                                    &euro;{{ formatNumber(b.min) }}
                                    &ndash;
                                    {{ b.max ? '&euro;' + formatNumber(b.max) : '&infin;' }}
                                </td>
                                <td class="text-right py-2 text-gray-400">{{ (b.base_rate * 100).toFixed(0) }}%</td>
                                <td class="text-right py-2" :class="b.rate !== b.base_rate ? 'text-green-600 font-semibold' : ''">
                                    {{ (b.rate * 100).toFixed(0) }}%
                                    <span v-if="b.rate !== b.base_rate" class="text-xs ml-1">(override)</span>
                                </td>
                                <td class="text-right py-2">&euro;{{ formatNumber(b.taxable_amount) }}</td>
                                <td class="text-right py-2 font-medium">&euro;{{ formatNumber(b.tax) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Monthly Breakdown -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Monthly Breakdown</h2>
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <div class="text-sm text-gray-500">Monthly Gross</div>
                            <div class="text-lg font-semibold">&euro;{{ formatNumber(result.gross / 12) }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Monthly Deductions + Tax</div>
                            <div class="text-lg font-semibold text-red-600">&euro;{{ formatNumber((result.total_deductions + result.tax) / 12) }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-green-600">Monthly Net</div>
                            <div class="text-lg font-bold text-green-700">&euro;{{ formatNumber(result.net / 12) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    activeScale: Object,
    selectedCountry: String,
    selectedState: String,
    availableCountries: Array,
    availableStates: Array,
});

const selectedCountry = ref(props.selectedCountry || 'GR');
const selectedState = ref(props.selectedState || null);
const age = ref(31);
const children = ref(0);
const gross = ref(null);
const result = ref(null);
const loading = ref(false);

function formatNumber(n) {
    return Number(n).toLocaleString('en', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function onCountryChange() {
    selectedState.value = null;
    result.value = null;
    router.get('/', { country_code: selectedCountry.value }, { preserveState: false });
}

function onStateChange() {
    result.value = null;
    const params = { country_code: selectedCountry.value };
    if (selectedState.value) params.state = selectedState.value;
    router.get('/', params, { preserveState: false });
}

async function calculate() {
    if (!gross.value || gross.value <= 0) return;
    loading.value = true;
    try {
        const res = await fetch('/api/calculate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({
                gross: gross.value,
                country_code: selectedCountry.value,
                state: selectedState.value,
                age: age.value,
                children: children.value,
            }),
        });
        result.value = await res.json();
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}
</script>
