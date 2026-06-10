<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import SitePicker from '@/Components/SitePicker.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    site: Object,
    range: Number,
    events: Object,
    summary: Array,
    tags: Array,
    currentTag: String,
})

const tagOpen = ref(false)
const tagWrapper = ref(null)

const setRange = (d) =>
    router.get(`/sites/${props.site.tracking_id}/events`, { days: d, tag: props.currentTag || undefined }, { preserveState: false })

const filterTag = (tag) =>
    router.get(`/sites/${props.site.tracking_id}/events`, { days: props.range, tag: tag || undefined }, { preserveState: false })

const totalSummary = computed(() => props.summary.reduce((a, r) => a + r.cnt, 0))

const pathOf = (url) => {
    try { return new URL(url).pathname || '/' } catch { return url }
}

import { onMounted, onBeforeUnmount } from 'vue'
const handleOutside = (e) => { if (tagWrapper.value && !tagWrapper.value.contains(e.target)) tagOpen.value = false }
onMounted(() => document.addEventListener('click', handleOutside))
onBeforeUnmount(() => document.removeEventListener('click', handleOutside))
</script>

<template>
    <Head :title="`${site.name} — Events Click`" />

    <AppLayout>
        <div class="flex flex-col min-h-screen">

            <header class="flex items-center justify-between px-8 py-5 bg-white border-b border-gray-100 gap-4 flex-wrap">
                <div class="flex items-center gap-2 text-sm font-medium text-gray-500">
                    <Link href="/" class="hover:text-gray-800 transition-colors">Dashboard</Link>
                    <span class="text-gray-300">/</span>
                    <SitePicker :site="site" route-base="events" />
                    <span class="text-gray-300">/</span>
                    <span class="text-gray-800 font-semibold">Events Click</span>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Tag filter -->
                    <div v-if="tags.length" ref="tagWrapper" class="relative">
                        <button @click.stop="tagOpen = !tagOpen"
                            class="flex items-center gap-1.5 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-600 hover:border-gray-300 bg-white transition-colors">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></svg>
                            {{ currentTag ? `Tag: ${currentTag}` : 'Filter by tag' }}
                        </button>
                        <div v-if="tagOpen" class="absolute right-0 top-full mt-1.5 bg-white border border-gray-100 rounded-xl shadow-lg z-20 min-w-36 py-1">
                            <button @click="filterTag(null); tagOpen=false" :class="['block w-full text-left px-4 py-2 text-sm hover:bg-gray-50', !currentTag ? 'text-orange-600 font-semibold' : 'text-gray-700']">All tags</button>
                            <button v-for="tag in tags" :key="tag" @click="filterTag(tag); tagOpen=false" :class="['block w-full text-left px-4 py-2 text-sm hover:bg-gray-50', currentTag === tag ? 'text-orange-600 font-semibold' : 'text-gray-700']">{{ tag || 'unknown' }}</button>
                        </div>
                    </div>

                    <div class="flex items-center bg-gray-100 rounded-xl p-1 gap-0.5">
                        <button v-for="d in [7,14,30,60,90]" :key="d" @click="setRange(d)"
                            :class="['px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors', range == d ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700']">
                            {{ d }}d
                        </button>
                    </div>
                </div>
            </header>

            <div class="flex-1 p-8 space-y-6">

                <!-- Summary chips -->
                <div v-if="summary.length" class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <h2 class="font-semibold text-gray-900 mb-4 text-sm">Clicks by Element Type</h2>
                    <div class="flex flex-wrap gap-3">
                        <button v-for="row in summary" :key="row.element_tag"
                            @click="filterTag(row.element_tag)"
                            :class="['flex items-center gap-2 px-3 py-2 rounded-xl border transition-colors', currentTag === row.element_tag ? 'border-orange-300 bg-orange-50' : 'border-gray-200 bg-white hover:border-gray-300']">
                            <span class="text-xs font-mono bg-orange-50 text-orange-600 px-1.5 py-0.5 rounded-md">{{ row.element_tag || '?' }}</span>
                            <span class="text-sm font-semibold text-gray-800">{{ row.cnt.toLocaleString() }}</span>
                            <span class="text-xs text-gray-400">{{ totalSummary > 0 ? Math.round(row.cnt / totalSummary * 100) : 0 }}%</span>
                        </button>
                    </div>
                </div>

                <!-- Events table -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100">
                        <p class="text-sm text-gray-500"><span class="font-semibold text-gray-900">{{ events.total }}</span> click events</p>
                    </div>

                    <div v-if="!events.data.length" class="py-16 text-center">
                        <p class="text-sm text-gray-400">No click events in this period.</p>
                    </div>

                    <template v-else>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm min-w-[800px]">
                                <thead>
                                    <tr class="text-xs font-semibold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                                        <th class="text-left px-5 py-3 font-medium">Time</th>
                                        <th class="text-left px-4 py-3 font-medium">Tag</th>
                                        <th class="text-left px-4 py-3 font-medium">Text / Href</th>
                                        <th class="text-left px-4 py-3 font-medium">Page</th>
                                        <th class="text-left px-4 py-3 font-medium">Position</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="ev in events.data" :key="ev.id" class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                                        <td class="py-3 px-5 text-xs text-gray-400 whitespace-nowrap">{{ ev.created_at_human }}</td>
                                        <td class="py-3 px-4">
                                            <span class="text-xs font-mono bg-orange-50 text-orange-600 px-1.5 py-0.5 rounded-md">{{ (ev.element_tag || '?').toLowerCase() }}</span>
                                        </td>
                                        <td class="py-3 px-4 max-w-xs">
                                            <p v-if="ev.element_text" class="text-xs text-gray-700 truncate">{{ ev.element_text.substring(0, 60) }}</p>
                                            <p v-if="ev.element_href" class="text-xs text-blue-500 truncate mt-0.5" :title="ev.element_href">{{ ev.element_href.substring(0, 60) }}</p>
                                            <span v-if="!ev.element_text && !ev.element_href" class="text-xs text-gray-400">—</span>
                                        </td>
                                        <td class="py-3 px-4 max-w-xs">
                                            <p class="text-xs text-gray-500 truncate" :title="ev.page_url">{{ pathOf(ev.page_url).substring(0, 40) }}</p>
                                        </td>
                                        <td class="py-3 px-4 text-xs text-gray-400 font-mono">
                                            {{ ev.x_pos !== null && ev.x_pos !== undefined ? `${ev.x_pos}, ${ev.y_pos}` : '—' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="px-5 py-4 border-t border-gray-50">
                            <Pagination :links="events.meta?.links ?? events.links" />
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
