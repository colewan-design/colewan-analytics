<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    sites: Array,
})

const page    = usePage()
const flash   = computed(() => page.props.flash)
const modal   = ref(false)

const form = useForm({ name: '', domain: '' })
const submit = () => form.post('/sites', {
    onSuccess: () => { modal.value = false; form.reset() },
})
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <div class="p-8">

            <!-- Flash (inline fallback) -->
            <div v-if="flash?.success" class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
                {{ flash.success }}
            </div>

            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <p class="text-sm text-gray-400 font-medium mb-1">Overview</p>
                    <h1 class="text-2xl font-bold text-gray-900">Your Sites</h1>
                </div>
                <button
                    @click="modal = true"
                    class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors shadow-sm"
                >
                    <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                    </svg>
                    Add Site
                </button>
            </div>

            <!-- Empty state -->
            <div v-if="!sites.length" class="flex flex-col items-center justify-center py-24 text-center">
                <div class="w-16 h-16 bg-orange-100 rounded-2xl flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-800 mb-1">No sites yet</h2>
                <p class="text-sm text-gray-500 mb-6">Add your first site to start tracking visitor analytics.</p>
                <button @click="modal = true" class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                    </svg>
                    Add Your First Site
                </button>
            </div>

            <!-- Sites grid -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <Link
                    v-for="site in sites"
                    :key="site.id"
                    :href="`/sites/${site.tracking_id}`"
                    class="bg-white border border-gray-100 rounded-2xl p-5 hover:border-orange-200 hover:shadow-md transition-all group shadow-sm"
                >
                    <div class="flex items-start justify-between mb-5">
                        <div class="min-w-0">
                            <h2 class="font-semibold text-gray-900 group-hover:text-orange-600 transition-colors truncate">{{ site.name }}</h2>
                            <p class="text-xs text-gray-400 mt-0.5 truncate">{{ site.domain || 'No domain set' }}</p>
                        </div>
                        <span class="text-xs bg-gray-100 text-gray-500 px-2 py-1 rounded-lg shrink-0 ml-3">{{ site.created_at_human }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-orange-50 rounded-xl p-3">
                            <p class="text-xs text-orange-600/70 font-medium">Page Views</p>
                            <p class="text-2xl font-bold text-orange-600 mt-0.5">{{ site.pageviews_count.toLocaleString() }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-3">
                            <p class="text-xs text-gray-500 font-medium">Clicks</p>
                            <p class="text-2xl font-bold text-gray-800 mt-0.5">{{ site.clicks_count.toLocaleString() }}</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-300 mt-4 truncate font-mono">{{ site.tracking_id }}</p>
                </Link>
            </div>
        </div>

        <!-- Add Site Modal -->
        <Teleport to="body">
            <div
                v-if="modal"
                class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4"
                @click.self="modal = false"
            >
                <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl border border-gray-100">
                    <h2 class="text-lg font-bold mb-1">Add New Site</h2>
                    <p class="text-sm text-gray-500 mb-5">Start tracking visitors and events on your website.</p>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Site Name</label>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="My Website"
                                required
                                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent transition text-sm"
                            >
                            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Domain <span class="text-gray-400 font-normal">(optional)</span></label>
                            <input
                                v-model="form.domain"
                                type="text"
                                placeholder="example.com"
                                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent transition text-sm"
                            >
                        </div>
                        <div class="flex gap-3 pt-1">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex-1 bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2.5 rounded-xl transition-colors text-sm disabled:opacity-60"
                            >
                                Create Site
                            </button>
                            <button
                                type="button"
                                @click="modal = false"
                                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2.5 rounded-xl transition-colors text-sm"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
