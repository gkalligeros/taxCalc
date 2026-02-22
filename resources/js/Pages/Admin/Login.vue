<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center px-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800">taxCalc</h1>
                <p class="text-gray-500 mt-1">Admin Dashboard</p>
            </div>

            <div class="bg-white rounded-xl shadow-md p-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Sign in</h2>

                <div v-if="$page.props.errors?.username" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                    {{ $page.props.errors.username }}
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input
                            v-model="form.username"
                            type="text"
                            autocomplete="username"
                            required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input
                            v-model="form.password"
                            type="password"
                            autocomplete="current-password"
                            required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                        />
                    </div>

                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full py-2.5 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-60 transition"
                    >
                        {{ loading ? 'Signing in…' : 'Sign in' }}
                    </button>
                </form>
            </div>

            <p class="text-center mt-6 text-sm text-gray-400">
                <a href="/" class="hover:text-indigo-600 transition">&larr; Back to calculator</a>
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const form = ref({ username: '', password: '' });
const loading = ref(false);

function submit() {
    loading.value = true;
    router.post('/admin/login', form.value, {
        onFinish: () => { loading.value = false; },
    });
}
</script>
