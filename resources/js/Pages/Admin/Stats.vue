<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Header -->
        <div class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <span class="text-xl font-bold text-gray-800">taxCalc</span>
                    <span class="text-gray-300">/</span>
                    <span class="text-gray-600 font-medium">Admin</span>
                </div>
                <div class="flex items-center gap-3">
                    <a href="/admin/scales" class="text-sm text-gray-600 hover:text-indigo-600 transition font-medium">
                        Tax Scales
                    </a>
                    <a href="/" class="text-sm text-gray-600 hover:text-indigo-600 transition">
                        Calculator
                    </a>
                    <form @submit.prevent="logout">
                        <button type="submit" class="text-sm px-3 py-1.5 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition">
                            Sign out
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 py-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Statistics</h1>

            <!-- Stats cards -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
                <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                    <p class="text-xs uppercase tracking-wide text-gray-400 font-medium mb-1">Calculations</p>
                    <p class="text-3xl font-bold text-indigo-600">{{ stats.total_calculations.toLocaleString() }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                    <p class="text-xs uppercase tracking-wide text-gray-400 font-medium mb-1">Tax Scales</p>
                    <p class="text-3xl font-bold text-gray-800">{{ stats.total_scales }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                    <p class="text-xs uppercase tracking-wide text-gray-400 font-medium mb-1">Active Scales</p>
                    <p class="text-3xl font-bold text-green-600">{{ stats.active_scales }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                    <p class="text-xs uppercase tracking-wide text-gray-400 font-medium mb-1">Countries</p>
                    <p class="text-3xl font-bold text-gray-800">{{ stats.total_countries }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                    <p class="text-xs uppercase tracking-wide text-gray-400 font-medium mb-1">Tax Brackets</p>
                    <p class="text-3xl font-bold text-gray-800">{{ stats.total_brackets }}</p>
                </div>
            </div>

            <!-- By country breakdown -->
            <div v-if="by_country.length" class="bg-white rounded-xl shadow-sm border border-gray-100 mb-8 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-800">By Country</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-left">
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Country</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Currency</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-right">Calculations</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-right">Avg Gross</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-right">Avg Net</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-right">Avg Tax</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="row in by_country" :key="row.country_code" class="hover:bg-gray-50 transition">
                                <td class="px-6 py-3 font-medium text-gray-800">{{ row.country_code }}</td>
                                <td class="px-6 py-3 text-gray-500 font-mono text-xs">{{ row.currency ?? 'EUR' }}</td>
                                <td class="px-6 py-3 text-right text-gray-600">{{ row.count.toLocaleString() }}</td>
                                <td class="px-6 py-3 text-right text-gray-600">{{ fmt(row.avg_gross, row.currency) }}</td>
                                <td class="px-6 py-3 text-right text-gray-600">{{ fmt(row.avg_net, row.currency) }}</td>
                                <td class="px-6 py-3 text-right text-gray-600">{{ fmt(row.avg_tax, row.currency) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent calculations table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-800">Recent Calculations</h2>
                    <span class="text-xs text-gray-400">Last {{ recent_calculations.length }}</span>
                </div>

                <div v-if="!recent_calculations.length" class="px-6 py-12 text-center text-gray-400 text-sm">
                    No calculations yet. They will appear here once users start using the calculator.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-left">
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Country</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Currency</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Mode</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-right">Gross</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-right">Net</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-right">Tax</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-right">Deductions</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">Age</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">Children</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr
                                v-for="calc in recent_calculations"
                                :key="calc.id"
                                class="hover:bg-gray-50 transition"
                            >
                                <td class="px-4 py-2.5 text-gray-500 whitespace-nowrap text-xs">{{ fmtDate(calc.created_at) }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                        {{ calc.country_code }}{{ calc.state ? ' / ' + calc.state : '' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 font-mono text-xs text-gray-500">{{ calc.currency ?? 'EUR' }}</td>
                                <td class="px-4 py-2.5">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                        :class="calc.mode === 'gross_to_net' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700'"
                                    >
                                        {{ calc.mode === 'gross_to_net' ? 'Gross → Net' : 'Net → Gross' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-right font-mono text-gray-700">{{ fmt(calc.gross, calc.currency) }}</td>
                                <td class="px-4 py-2.5 text-right font-mono text-green-700 font-medium">{{ fmt(calc.net, calc.currency) }}</td>
                                <td class="px-4 py-2.5 text-right font-mono text-red-600">{{ fmt(calc.tax, calc.currency) }}</td>
                                <td class="px-4 py-2.5 text-right font-mono text-orange-600">{{ fmt(calc.total_deductions, calc.currency) }}</td>
                                <td class="px-4 py-2.5 text-center text-gray-600">{{ calc.age }}</td>
                                <td class="px-4 py-2.5 text-center text-gray-600">{{ calc.children }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3';

defineProps({
    stats: Object,
    by_country: Array,
    recent_calculations: Array,
});

function fmt(value, currency = 'EUR') {
    if (value == null) return '—';
    try {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency ?? 'EUR',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(value);
    } catch {
        return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);
    }
}

function fmtDate(iso) {
    if (!iso) return '—';
    return new Date(iso).toLocaleString('en-GB', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

function logout() {
    router.post('/admin/logout');
}
</script>
