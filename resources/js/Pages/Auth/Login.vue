<script setup>
import { Head, useForm } from '@inertiajs/vue3'

const form = useForm({ password: '' })

const submit = () => form.post('/login')
</script>

<template>
    <Head title="Login" />

    <div class="bg-gray-950 text-gray-100 flex items-center justify-center min-h-screen">
        <div class="w-full max-w-sm bg-gray-900 rounded-xl border border-gray-800 p-8 shadow-2xl">
            <h1 class="text-2xl font-bold text-orange-400 mb-6 text-center">● Analytics</h1>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Admin Password</label>
                    <input
                        v-model="form.password"
                        type="password"
                        autofocus
                        required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-orange-500"
                    >
                    <p v-if="form.errors.password" class="text-red-400 text-sm mt-1">{{ form.errors.password }}</p>
                </div>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2.5 rounded-lg transition disabled:opacity-60"
                >
                    Sign In
                </button>
            </form>
        </div>
    </div>
</template>
