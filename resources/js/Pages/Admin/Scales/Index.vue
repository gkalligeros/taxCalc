<template>
    <div class="min-h-screen bg-gray-100 py-12">
        <div class="max-w-4xl mx-auto px-4">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Tax Scales</h1>
                <a href="/" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                    &larr; Calculator
                </a>
            </div>

            <!-- Flash Messages -->
            <div v-if="$page.props.flash?.success" class="mb-4 p-3 bg-green-50 border border-green-200 rounded text-green-800 text-sm">
                {{ $page.props.flash.success }}
            </div>

            <!-- Create New Scale -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-3">Create New Scale</h2>
                <form @submit.prevent="createScale" class="space-y-3">
                    <div class="flex gap-3">
                        <input
                            v-model="newScale.name"
                            type="text"
                            placeholder="Scale name (e.g. 2026 Tax Scale)"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            required
                        />
                        <select
                            v-model="newScale.country_code"
                            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            required
                        >
                            <option v-for="(name, code) in availableCountries" :key="code" :value="code">
                                {{ code }} - {{ name }}
                            </option>
                        </select>
                        <input
                            v-model="newScale.state"
                            type="text"
                            placeholder="State (optional)"
                            class="w-40 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                        />
                        <button
                            type="submit"
                            :disabled="!newScale.name || !newScale.country_code"
                            class="px-5 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition"
                        >
                            Create
                        </button>
                    </div>
                </form>
            </div>

            <!-- Scales List -->
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
                            <span v-if="scale.is_active" class="px-2 py-0.5 text-xs font-medium bg-indigo-100 text-indigo-700 rounded-full">
                                Active
                            </span>
                        </div>
                        <div class="text-sm text-gray-500 mt-1">
                            {{ scale.brackets_count }} brackets &middot; {{ scale.deductions_count }} deductions
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            v-if="!scale.is_active"
                            @click="activateScale(scale)"
                            class="px-3 py-1.5 text-sm bg-green-100 text-green-700 rounded hover:bg-green-200 transition"
                        >
                            Activate
                        </button>
                        <a
                            :href="`/admin/scales/${scale.id}`"
                            class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition"
                        >
                            Edit
                        </a>
                        <button
                            @click="deleteScale(scale)"
                            class="px-3 py-1.5 text-sm bg-red-100 text-red-700 rounded hover:bg-red-200 transition"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <div v-if="!scales.length" class="text-center py-12 text-gray-500">
                    No tax scales yet. Create one above.
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

defineProps({
    scales: Array,
    availableCountries: Object,
});

const newScale = ref({ name: '', country_code: 'GR', state: '' });

function createScale() {
    router.post('/admin/scales', {
        name: newScale.value.name,
        country_code: newScale.value.country_code,
        state: newScale.value.state || null,
    }, {
        onSuccess: () => { newScale.value = { name: '', country_code: 'GR', state: '' }; },
    });
}

function activateScale(scale) {
    router.post(`/admin/scales/${scale.id}/activate`);
}

function deleteScale(scale) {
    if (confirm(`Delete "${scale.name}"? This cannot be undone.`)) {
        router.delete(`/admin/scales/${scale.id}`);
    }
}
</script>
