<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import SitePicker from '@/Components/SitePicker.vue'

const props = defineProps({
    site: Object,
    totalPv: Number,
    totalCl: Number,
    totalFb: Number,
})

const form = useForm({
    name:   props.site.name,
    domain: props.site.domain ?? '',
})

const submit = () =>
    form.put(`/sites/${props.site.tracking_id}/workspace`)

const deleteSite = () => {
    if (!confirm(`Delete ${props.site.name} and ALL its data? This cannot be undone.`)) return
    form.delete(`/sites/${props.site.id}`)
}
</script>

<template>
    <Head :title="`${site.name} — Workspace`" />

    <AppLayout>
        <div class="flex flex-col min-h-screen">

            <header class="flex items-center justify-between px-8 py-5 bg-white border-b border-gray-100 gap-4 flex-wrap">
                <div class="flex items-center gap-2 text-sm font-medium text-gray-500">
                    <Link href="/" class="hover:text-gray-800 transition-colors">Dashboard</Link>
                    <span class="text-gray-300">/</span>
                    <SitePicker :site="site" route-base="workspace" />
                    <span class="text-gray-300">/</span>
                    <span class="text-gray-800 font-semibold">Workspace</span>
                </div>
            </header>

            <div class="flex-1 p-8 space-y-6 max-w-2xl">

                <!-- Settings form -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h2 class="font-semibold text-gray-900 mb-5 text-base">Site Settings</h2>
                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-1.5">Site Name</label>
                            <input id="name" v-model="form.name" type="text" placeholder="My Website"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent transition-all">
                            <p v-if="form.errors.name" class="mt-1.5 text-xs text-red-500">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label for="domain" class="block text-sm font-semibold text-gray-700 mb-1.5">Domain <span class="text-gray-400 font-normal">(optional)</span></label>
                            <input id="domain" v-model="form.domain" type="text" placeholder="example.com"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent transition-all">
                            <p class="mt-1.5 text-xs text-gray-400">Used as the display label in the dashboard.</p>
                            <p v-if="form.errors.domain" class="mt-1.5 text-xs text-red-500">{{ form.errors.domain }}</p>
                        </div>
                        <button type="submit" :disabled="form.processing"
                            class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors disabled:opacity-60">
                            Save Changes
                        </button>
                    </form>
                </div>

                <!-- Tracking ID -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h2 class="font-semibold text-gray-900 mb-1 text-base">Tracking ID</h2>
                    <p class="text-sm text-gray-500 mb-4">Use this ID in your embed script. Treat it like a password.</p>
                    <div class="flex items-center gap-3">
                        <code id="tid-display" class="flex-1 block font-mono text-sm bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-gray-700 select-all">{{ site.tracking_id }}</code>
                        <button @click="navigator.clipboard.writeText(site.tracking_id)" class="shrink-0 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold px-3 py-2 rounded-xl transition-colors">Copy</button>
                    </div>
                    <div class="mt-4 flex items-center gap-4 pt-4 border-t border-gray-100">
                        <Link :href="`/sites/${site.tracking_id}/integration`" class="text-sm text-orange-500 hover:text-orange-600 font-medium transition-colors">View integration guide →</Link>
                        <Link :href="`/sites/${site.tracking_id}`" class="text-sm text-gray-500 hover:text-gray-700 font-medium transition-colors">Open dashboard →</Link>
                    </div>
                </div>

                <!-- Data overview -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h2 class="font-semibold text-gray-900 mb-4 text-base">Data Overview</h2>
                    <div class="grid grid-cols-3 gap-4">
                        <div v-for="[val, label] in [[totalPv,'Page Views'],[totalCl,'Click Events'],[totalFb,'Feedback']]" :key="label" class="text-center p-4 bg-gray-50 rounded-xl">
                            <p class="text-xl font-bold text-gray-900">{{ (val ?? 0).toLocaleString() }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ label }}</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-3">All time totals. Created {{ site.created_at_human }}.</p>
                </div>

                <!-- Danger zone -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-red-100">
                    <h2 class="font-semibold text-red-600 mb-1 text-base">Danger Zone</h2>
                    <p class="text-sm text-gray-500 mb-4">Deleting this site will permanently remove all data. This action cannot be undone.</p>
                    <button @click="deleteSite" class="border border-red-200 bg-white hover:bg-red-50 text-red-500 text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
                        Delete Site & All Data
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
