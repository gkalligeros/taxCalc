<template>
    <div class="min-h-screen bg-gray-100 py-12">
        <div class="max-w-4xl mx-auto px-4">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <a href="/admin/scales" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">&larr; Back to Scales</a>
                    <h1 class="text-3xl font-bold text-gray-800 mt-2">Edit Scale</h1>
                </div>
            </div>

            <!-- Flash Messages -->
            <div v-if="$page.props.flash?.success" class="mb-4 p-3 bg-green-50 border border-green-200 rounded text-green-800 text-sm">
                {{ $page.props.flash.success }}
            </div>

            <!-- Scale Details -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-3">Scale Details</h2>
                <form @submit.prevent="updateDetails" class="flex gap-3 items-end">
                    <div class="flex-1">
                        <label class="block text-xs text-gray-500 mb-1">Name</label>
                        <input
                            v-model="scaleName"
                            type="text"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            required
                        />
                    </div>
                    <div class="w-36">
                        <label class="block text-xs text-gray-500 mb-1">Country</label>
                        <select
                            v-model="scaleCountry"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            required
                        >
                            <option v-for="(name, code) in availableCountries" :key="code" :value="code">
                                {{ code }} - {{ name }}
                            </option>
                        </select>
                    </div>
                    <div class="w-36">
                        <label class="block text-xs text-gray-500 mb-1">State</label>
                        <input
                            v-model="scaleState"
                            type="text"
                            placeholder="(optional)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                        />
                    </div>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition">
                        Save
                    </button>
                </form>
            </div>

            <!-- Tax Brackets -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Tax Brackets</h2>
                <table class="w-full text-sm mb-4">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2 text-gray-600">Min Amount (&euro;)</th>
                            <th class="text-left py-2 text-gray-600">Max Amount (&euro;)</th>
                            <th class="text-left py-2 text-gray-600">Rate (%)</th>
                            <th class="text-right py-2 text-gray-600">Actions</th>
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
                                        <input v-model.number="editingBracket.max_amount" type="number" min="0" step="0.01" placeholder="Unlimited" class="w-full px-2 py-1 border rounded text-sm" />
                                    </td>
                                    <td class="py-2 pr-2">
                                        <input v-model.number="editingBracket.rate_pct" type="number" min="0" max="100" step="0.01" class="w-full px-2 py-1 border rounded text-sm" />
                                    </td>
                                    <td class="py-2 text-right space-x-1">
                                        <button @click="saveBracket" class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded hover:bg-green-200">Save</button>
                                        <button @click="editingBracket = null" class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded hover:bg-gray-200">Cancel</button>
                                    </td>
                                </template>
                                <template v-else>
                                    <td class="py-2">&euro;{{ Number(bracket.min_amount).toLocaleString() }}</td>
                                    <td class="py-2">{{ bracket.max_amount ? '&euro;' + Number(bracket.max_amount).toLocaleString() : 'Unlimited' }}</td>
                                    <td class="py-2">{{ (bracket.rate * 100).toFixed(2) }}%</td>
                                    <td class="py-2 text-right space-x-1">
                                        <button @click="toggleOverrides(bracket.id)" class="px-2 py-1 text-xs rounded hover:bg-purple-200 transition"
                                            :class="expandedBracket === bracket.id ? 'bg-purple-200 text-purple-800' : 'bg-purple-100 text-purple-700'">
                                            Overrides ({{ (bracket.overrides || []).length }})
                                        </button>
                                        <button @click="startEditBracket(bracket)" class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200">Edit</button>
                                        <button @click="deleteBracket(bracket)" class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200">Delete</button>
                                    </td>
                                </template>
                            </tr>

                            <!-- Overrides sub-section -->
                            <tr v-if="expandedBracket === bracket.id">
                                <td colspan="4" class="bg-purple-50 p-4">
                                    <div class="text-xs font-semibold text-purple-700 uppercase tracking-wide mb-3">
                                        Rate Overrides for &euro;{{ Number(bracket.min_amount).toLocaleString() }} &ndash; {{ bracket.max_amount ? '&euro;' + Number(bracket.max_amount).toLocaleString() : '&infin;' }}
                                    </div>

                                    <!-- Existing overrides -->
                                    <table v-if="(bracket.overrides || []).length" class="w-full text-xs mb-3">
                                        <thead>
                                            <tr class="border-b border-purple-200">
                                                <th class="text-left py-1 text-purple-600">Min Age</th>
                                                <th class="text-left py-1 text-purple-600">Max Age</th>
                                                <th class="text-left py-1 text-purple-600">Min Children</th>
                                                <th class="text-left py-1 text-purple-600">Max Children</th>
                                                <th class="text-left py-1 text-purple-600">Rate (%)</th>
                                                <th class="text-right py-1 text-purple-600">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="ov in bracket.overrides" :key="ov.id" class="border-b border-purple-100">
                                                <template v-if="editingOverride?.id === ov.id">
                                                    <td class="py-1 pr-1"><input v-model.number="editingOverride.min_age" type="number" min="0" max="120" placeholder="any" class="w-full px-1 py-0.5 border rounded text-xs" /></td>
                                                    <td class="py-1 pr-1"><input v-model.number="editingOverride.max_age" type="number" min="0" max="120" placeholder="any" class="w-full px-1 py-0.5 border rounded text-xs" /></td>
                                                    <td class="py-1 pr-1"><input v-model.number="editingOverride.min_children" type="number" min="0" max="20" class="w-full px-1 py-0.5 border rounded text-xs" /></td>
                                                    <td class="py-1 pr-1"><input v-model.number="editingOverride.max_children" type="number" min="0" max="20" placeholder="any" class="w-full px-1 py-0.5 border rounded text-xs" /></td>
                                                    <td class="py-1 pr-1"><input v-model.number="editingOverride.rate_pct" type="number" min="0" max="100" step="0.01" class="w-full px-1 py-0.5 border rounded text-xs" /></td>
                                                    <td class="py-1 text-right space-x-1">
                                                        <button @click="saveOverride(bracket)" class="px-1.5 py-0.5 text-xs bg-green-100 text-green-700 rounded hover:bg-green-200">Save</button>
                                                        <button @click="editingOverride = null" class="px-1.5 py-0.5 text-xs bg-gray-100 text-gray-600 rounded hover:bg-gray-200">Cancel</button>
                                                    </td>
                                                </template>
                                                <template v-else>
                                                    <td class="py-1">{{ ov.min_age ?? 'any' }}</td>
                                                    <td class="py-1">{{ ov.max_age ?? 'any' }}</td>
                                                    <td class="py-1">{{ ov.min_children }}</td>
                                                    <td class="py-1">{{ ov.max_children ?? 'any' }}</td>
                                                    <td class="py-1">{{ (ov.rate * 100).toFixed(2) }}%</td>
                                                    <td class="py-1 text-right space-x-1">
                                                        <button @click="startEditOverride(ov)" class="px-1.5 py-0.5 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200">Edit</button>
                                                        <button @click="deleteOverride(bracket, ov)" class="px-1.5 py-0.5 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200">Delete</button>
                                                    </td>
                                                </template>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <!-- Add override -->
                                    <form @submit.prevent="addOverride(bracket)" class="flex gap-1 items-end">
                                        <div class="flex-1">
                                            <label class="block text-xs text-purple-500 mb-0.5">Min Age</label>
                                            <input v-model.number="newOverride.min_age" type="number" min="0" max="120" placeholder="any" class="w-full px-1 py-1 border rounded text-xs" />
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-xs text-purple-500 mb-0.5">Max Age</label>
                                            <input v-model.number="newOverride.max_age" type="number" min="0" max="120" placeholder="any" class="w-full px-1 py-1 border rounded text-xs" />
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-xs text-purple-500 mb-0.5">Min Children</label>
                                            <input v-model.number="newOverride.min_children" type="number" min="0" max="20" class="w-full px-1 py-1 border rounded text-xs" />
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-xs text-purple-500 mb-0.5">Max Children</label>
                                            <input v-model.number="newOverride.max_children" type="number" min="0" max="20" placeholder="any" class="w-full px-1 py-1 border rounded text-xs" />
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-xs text-purple-500 mb-0.5">Rate (%)</label>
                                            <input v-model.number="newOverride.rate_pct" type="number" min="0" max="100" step="0.01" class="w-full px-1 py-1 border rounded text-xs" required />
                                        </div>
                                        <button type="submit" class="px-3 py-1 bg-purple-600 text-white text-xs font-medium rounded hover:bg-purple-700 transition">
                                            Add
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <!-- Add Bracket -->
                <form @submit.prevent="addBracket" class="flex gap-2 items-end">
                    <div class="flex-1">
                        <label class="block text-xs text-gray-500 mb-1">Min (&euro;)</label>
                        <input v-model.number="newBracket.min_amount" type="number" min="0" step="0.01" class="w-full px-2 py-1.5 border rounded text-sm" required />
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs text-gray-500 mb-1">Max (&euro;)</label>
                        <input v-model.number="newBracket.max_amount" type="number" min="0" step="0.01" placeholder="Unlimited" class="w-full px-2 py-1.5 border rounded text-sm" />
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs text-gray-500 mb-1">Rate (%)</label>
                        <input v-model.number="newBracket.rate_pct" type="number" min="0" max="100" step="0.01" class="w-full px-2 py-1.5 border rounded text-sm" required />
                    </div>
                    <button type="submit" class="px-4 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded hover:bg-indigo-700 transition">
                        Add
                    </button>
                </form>
            </div>

            <!-- Deductions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Deductions</h2>
                <table class="w-full text-sm mb-4">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2 text-gray-600">Name</th>
                            <th class="text-left py-2 text-gray-600">Rate (%)</th>
                            <th class="text-right py-2 text-gray-600">Actions</th>
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
                                    <button @click="saveDeduction" class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded hover:bg-green-200">Save</button>
                                    <button @click="editingDeduction = null" class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded hover:bg-gray-200">Cancel</button>
                                </td>
                            </template>
                            <template v-else>
                                <td class="py-2">{{ deduction.name }}</td>
                                <td class="py-2">{{ (deduction.rate * 100).toFixed(2) }}%</td>
                                <td class="py-2 text-right space-x-1">
                                    <button @click="startEditDeduction(deduction)" class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200">Edit</button>
                                    <button @click="deleteDeduction(deduction)" class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200">Delete</button>
                                </td>
                            </template>
                        </tr>
                    </tbody>
                </table>

                <!-- Add Deduction -->
                <form @submit.prevent="addDeduction" class="flex gap-2 items-end">
                    <div class="flex-1">
                        <label class="block text-xs text-gray-500 mb-1">Name</label>
                        <input v-model="newDeduction.name" type="text" placeholder="e.g. Social Security" class="w-full px-2 py-1.5 border rounded text-sm" required />
                    </div>
                    <div class="w-32">
                        <label class="block text-xs text-gray-500 mb-1">Rate (%)</label>
                        <input v-model.number="newDeduction.rate_pct" type="number" min="0" max="100" step="0.01" class="w-full px-2 py-1.5 border rounded text-sm" required />
                    </div>
                    <button type="submit" class="px-4 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded hover:bg-indigo-700 transition">
                        Add
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    scale: Object,
    availableCountries: Object,
});

const scaleName = ref(props.scale.name);
const scaleCountry = ref(props.scale.country_code);
const scaleState = ref(props.scale.state || '');

// Brackets
const editingBracket = ref(null);
const newBracket = ref({ min_amount: null, max_amount: null, rate_pct: null });

// Overrides
const expandedBracket = ref(null);
const editingOverride = ref(null);
const newOverride = ref({ min_age: null, max_age: null, min_children: 0, max_children: null, rate_pct: null });

function updateDetails() {
    router.put(`/admin/scales/${props.scale.id}`, {
        name: scaleName.value,
        country_code: scaleCountry.value,
        state: scaleState.value || null,
    });
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
    if (confirm('Delete this bracket?')) {
        router.delete(`/admin/scales/${props.scale.id}/brackets/${bracket.id}`);
    }
}

// Overrides
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
    if (confirm('Delete this override?')) {
        router.delete(`/admin/scales/${props.scale.id}/brackets/${bracket.id}/overrides/${ov.id}`);
    }
}

// Deductions
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
    if (confirm('Delete this deduction?')) {
        router.delete(`/admin/scales/${props.scale.id}/deductions/${deduction.id}`);
    }
}
</script>
