<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import SitePicker from '@/Components/SitePicker.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    site: Object,
    range: Number,
    sessions: Object,
    sessionPages: Object,
})

const expanded = ref({})
const toggle   = (sid) => expanded.value[sid] = !expanded.value[sid]

const fmtDur = (secs) => {
    secs = parseInt(secs) || 0
    if (secs >= 60) return `${Math.floor(secs/60)}m ${secs%60}s`
    if (secs > 0)   return `${secs}s`
    return '—'
}

const setRange = (d) => router.get(`/sites/${props.site.tracking_id}/replay`, { days: d }, { preserveState: false })
</script>

<template>
    <Head :title="`${site.name} — Replay`" />

    <AppLayout>
        <div class="flex flex-col min-h-screen">

            <header class="flex items-center justify-between px-8 py-5 bg-white border-b border-gray-100 gap-4 flex-wrap">
                <div class="flex items-center gap-2 text-sm font-medium text-gray-500">
                    <Link href="/" class="hover:text-gray-800 transition-colors">Dashboard</Link>
                    <span class="text-gray-300">/</span>
                    <SitePicker :site="site" route-base="replay" />
                    <span class="text-gray-300">/</span>
                    <span class="text-gray-800 font-semibold">Replay</span>
                </div>
                <div class="flex items-center bg-gray-100 rounded-xl p-1 gap-0.5">
                    <button v-for="[d,l] in [[1,'1d'],[3,'3d'],[7,'7d'],[14,'14d'],[30,'30d']]" :key="d" @click="setRange(d)"
                        :class="['px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors', range == d ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700']">
                        {{ l }}
                    </button>
                </div>
            </header>

            <div class="flex-1 p-8">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm text-gray-500"><span class="font-semibold text-gray-900">{{ sessions.total }}</span> sessions in the last {{ range }}d</p>
                    <p class="text-xs text-gray-400">Click a session to expand its page journey</p>
                </div>

                <div v-if="!sessions.data.length" class="flex flex-col items-center justify-center py-24 text-center bg-white rounded-2xl border border-gray-100">
                    <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center mb-4">
                        <svg class="w-7 h-7 text-orange-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M5 3l14 9-14 9V3z"/></svg>
                    </div>
                    <p class="text-gray-500 font-medium">No sessions recorded yet.</p>
                </div>

                <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <!-- Header row -->
                    <div class="grid text-xs font-semibold text-gray-400 uppercase tracking-wider px-5 py-3 border-b border-gray-100" style="grid-template-columns: 2fr 1fr 1fr 1fr 1fr 1fr 0.5fr">
                        <span>Session</span><span>Country</span><span>Browser</span><span>Device</span><span>Pages</span><span>Duration</span><span></span>
                    </div>

                    <div v-for="s in sessions.data" :key="s.session_id" class="border-b border-gray-50 last:border-b-0">
                        <!-- Session row -->
                        <button @click="toggle(s.session_id)"
                            class="w-full grid items-center px-5 py-3.5 hover:bg-gray-50/60 transition-colors text-left"
                            style="grid-template-columns: 2fr 1fr 1fr 1fr 1fr 1fr 0.5fr">
                            <div class="min-w-0">
                                <p class="text-xs font-mono text-gray-500">{{ s.session_id.substring(0,12) }}…</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ s.first_seen_human }}</p>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <img v-if="s.country_code" :src="`https://flagcdn.com/16x12/${s.country_code.toLowerCase()}.png`" alt="" class="rounded-sm shrink-0" width="14" height="10">
                                <span class="text-xs text-gray-600 truncate">{{ s.country || '—' }}</span>
                            </div>
                            <span class="text-xs text-gray-600 truncate">{{ s.browser || '—' }}</span>
                            <span class="text-xs text-gray-600">{{ s.device || '—' }}</span>
                            <span class="text-xs font-semibold text-gray-800">{{ s.page_count }}</span>
                            <span class="text-xs text-gray-600">{{ fmtDur(s.total_duration) }}</span>
                            <span class="text-xs text-gray-400 text-right">
                                <svg class="w-4 h-4 inline transition-transform" :class="expanded[s.session_id] ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                            </span>
                        </button>

                        <!-- Journey -->
                        <div v-if="expanded[s.session_id]" class="px-5 pb-4 bg-gray-50/50">
                            <div class="pt-3 pl-4 border-l-2 border-orange-200 space-y-2">
                                <div v-if="!sessionPages[s.session_id]?.length" class="text-xs text-gray-400">No page data.</div>
                                <div v-for="(pg, i) in sessionPages[s.session_id]" :key="i" class="flex items-start gap-3">
                                    <div class="w-5 h-5 rounded-full bg-orange-100 border-2 border-white flex items-center justify-center shrink-0 mt-0.5">
                                        <span class="text-[9px] font-bold text-orange-500">{{ i+1 }}</span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs text-gray-700 truncate font-medium" :title="pg.url">{{ pg.url }}</p>
                                        <div class="flex items-center gap-3 mt-0.5 text-xs text-gray-400">
                                            <span>{{ pg.time }}</span>
                                            <span v-if="pg.duration">{{ pg.duration }}s on page</span>
                                            <span v-if="pg.title" class="truncate italic">{{ pg.title }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <Pagination :links="sessions.meta?.links ?? sessions.links" />
            </div>
        </div>
    </AppLayout>
</template>
