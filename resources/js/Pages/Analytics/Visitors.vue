<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import SitePicker from '@/Components/SitePicker.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    site: Object,
    range: Number,
    sessions: Object,
})

const setRange = (d) => router.get(`/sites/${props.site.tracking_id}/visitors`, { days: d }, { preserveState: false })

const fmtDur = (secs) => {
    secs = parseInt(secs) || 0
    if (secs >= 60) return `${Math.floor(secs/60)}m ${secs%60}s`
    if (secs > 0)   return `${secs}s`
    return '—'
}

const deviceIcons = { Mobile: '📱', Tablet: '💻', Desktop: '🖥️' }
</script>

<template>
    <Head :title="`${site.name} — Visitors`" />

    <AppLayout>
        <div class="flex flex-col min-h-screen">

            <header class="flex items-center justify-between px-4 py-4 md:px-8 md:py-5 bg-white border-b border-gray-100 gap-3 flex-wrap">
                <div class="flex items-center gap-2 text-sm font-medium text-gray-500">
                    <Link href="/" class="hover:text-gray-800 transition-colors">Dashboard</Link>
                    <span class="text-gray-300">/</span>
                    <SitePicker :site="site" route-base="visitors" />
                    <span class="text-gray-300">/</span>
                    <span class="text-gray-800 font-semibold">Visitors</span>
                </div>
                <div class="flex items-center bg-gray-100 rounded-xl p-1 gap-0.5">
                    <button v-for="d in [7,14,30,60,90]" :key="d" @click="setRange(d)"
                        :class="['px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors', range == d ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700']">
                        {{ d }}d
                    </button>
                </div>
            </header>

            <div class="flex-1 p-4 md:p-8">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm text-gray-500"><span class="font-semibold text-gray-900">{{ sessions.total }}</span> unique sessions in the last {{ range }}d</p>
                </div>

                <div v-if="!sessions.data.length" class="flex flex-col items-center justify-center py-24 text-center bg-white rounded-2xl border border-gray-100">
                    <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center mb-4">
                        <svg class="w-7 h-7 text-orange-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    </div>
                    <p class="text-gray-500 font-medium">No visitors yet.</p>
                </div>

                <template v-else>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm min-w-[700px]">
                                <thead>
                                    <tr class="text-xs font-semibold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                                        <th class="text-left px-5 py-3 font-medium">Session</th>
                                        <th class="text-left px-4 py-3 font-medium">Country</th>
                                        <th class="text-left px-4 py-3 font-medium">Browser</th>
                                        <th class="text-left px-4 py-3 font-medium">Device</th>
                                        <th class="text-left px-4 py-3 font-medium">OS</th>
                                        <th class="text-left px-4 py-3 font-medium">Pages</th>
                                        <th class="text-left px-4 py-3 font-medium">Duration</th>
                                        <th class="text-left px-4 py-3 font-medium">Screen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="s in sessions.data" :key="s.session_id" class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                                        <td class="py-3 px-5">
                                            <p class="font-mono text-xs text-gray-500">{{ s.session_id.substring(0,12) }}…</p>
                                            <p class="text-xs text-gray-400 mt-0.5">{{ s.first_seen_human }}</p>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-1.5">
                                                <img v-if="s.country_code" :src="`https://flagcdn.com/16x12/${s.country_code.toLowerCase()}.png`" alt="" class="rounded-sm shrink-0" width="14" height="10">
                                                <span class="text-xs text-gray-600">{{ s.country || '—' }}</span>
                                            </div>
                                            <p v-if="s.city" class="text-xs text-gray-400 mt-0.5">{{ s.city }}</p>
                                        </td>
                                        <td class="py-3 px-4 text-xs text-gray-600">{{ s.browser || '—' }}</td>
                                        <td class="py-3 px-4 text-xs text-gray-600">{{ (deviceIcons[s.device] ?? '🖥️') }} {{ s.device || '—' }}</td>
                                        <td class="py-3 px-4 text-xs text-gray-600">{{ s.os || '—' }}</td>
                                        <td class="py-3 px-4 text-xs font-semibold text-gray-800">{{ s.page_count }}</td>
                                        <td class="py-3 px-4 text-xs text-gray-600">{{ fmtDur(s.total_duration) }}</td>
                                        <td class="py-3 px-4 text-xs text-gray-400 font-mono">{{ s.screen_width ? `${s.screen_width}×${s.screen_height}` : '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <Pagination :links="sessions.meta?.links ?? sessions.links" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
