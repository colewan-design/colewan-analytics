<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    sites: Array,
    stats: Object,
})

const totalViews    = () => props.sites.reduce((a, s) => a + (props.stats[s.id]?.views    ?? 0), 0)
const totalVisitors = () => props.sites.reduce((a, s) => a + (props.stats[s.id]?.visitors ?? 0), 0)
const totalClicks   = () => props.sites.reduce((a, s) => a + (props.stats[s.id]?.clicks   ?? 0), 0)

const sparkPath = (sparkline) => {
    if (!sparkline?.length) return ''
    const max = Math.max(...sparkline, 1)
    return sparkline.map((v, i) => {
        const x = (i / (sparkline.length - 1)) * 100
        const y = 30 - (v / max) * 28
        return `${x},${y}`
    }).join(' ')
}
</script>

<template>
    <Head title="Boards" />

    <AppLayout>
        <div class="flex flex-col min-h-screen">

            <header class="flex items-center justify-between px-8 py-5 bg-white border-b border-gray-100">
                <div>
                    <h1 class="text-lg font-bold text-gray-900">Boards</h1>
                    <p class="text-sm text-gray-400 mt-0.5">7-day overview across all your sites</p>
                </div>
                <Link href="/" class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
                    All Sites
                </Link>
            </header>

            <div class="flex-1 p-8">
                <div v-if="!sites.length" class="flex flex-col items-center justify-center py-24 text-center">
                    <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center mb-4">
                        <svg class="w-7 h-7 text-orange-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M3 3h18v14H3z"/><path d="M7 17v2M17 17v2"/></svg>
                    </div>
                    <p class="text-gray-500 font-medium">No sites tracked yet.</p>
                    <Link href="/" class="mt-3 text-sm text-orange-500 hover:underline">Add your first site →</Link>
                </div>

                <template v-else>
                    <!-- Summary row -->
                    <div class="grid grid-cols-3 gap-4 mb-8">
                        <div v-for="[label, val, path] in [
                            ['Total Views',    totalViews().toLocaleString(),    'M3 3h18v14H3z M7 9l3 3 3-3 4 4'],
                            ['Total Visitors', totalVisitors().toLocaleString(), 'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2 M9 7a4 4 0 100 8 4 4 0 000-8z'],
                            ['Total Clicks',   totalClicks().toLocaleString(),   'M15 15l-7-7 M8.5 8.5v7h7'],
                        ]" :key="label" class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
                            <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path :d="path"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium">{{ label }} <span class="font-normal">(7d)</span></p>
                                <p class="text-xl font-bold text-gray-900">{{ val }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Site cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                        <Link v-for="site in sites" :key="site.id" :href="`/sites/${site.tracking_id}`"
                            class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:border-orange-200 hover:shadow-md transition-all block group">

                            <div class="flex items-start justify-between mb-4">
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-900 truncate group-hover:text-orange-600 transition-colors">{{ site.domain || site.name }}</p>
                                    <p v-if="site.domain && site.domain !== site.name" class="text-xs text-gray-400 truncate mt-0.5">{{ site.name }}</p>
                                </div>
                                <template v-if="stats[site.id]?.change !== null && stats[site.id]?.change !== undefined">
                                    <span :class="['inline-flex items-center gap-0.5 text-xs font-semibold px-2 py-0.5 rounded-full shrink-0 ml-2', stats[site.id].change >= 0 ? 'text-green-700 bg-green-100' : 'text-red-600 bg-red-100']">
                                        {{ stats[site.id].change >= 0 ? '↗' : '↘' }} {{ Math.abs(stats[site.id].change) }}%
                                    </span>
                                </template>
                                <span v-else class="text-xs text-gray-300 shrink-0 ml-2">No prior data</span>
                            </div>

                            <!-- Sparkline -->
                            <div class="mb-4">
                                <svg viewBox="0 0 100 30" class="w-full h-8" preserveAspectRatio="none">
                                    <polyline :points="sparkPath(stats[site.id]?.sparkline)" fill="none" stroke="#f97316" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke"/>
                                </svg>
                            </div>

                            <div class="grid grid-cols-3 gap-2 pt-3 border-t border-gray-50">
                                <div class="text-center">
                                    <p class="text-base font-bold text-gray-900">{{ (stats[site.id]?.views ?? 0).toLocaleString() }}</p>
                                    <p class="text-xs text-gray-400">Views</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-base font-bold text-gray-900">{{ (stats[site.id]?.visitors ?? 0).toLocaleString() }}</p>
                                    <p class="text-xs text-gray-400">Visitors</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-base font-bold text-gray-900">{{ (stats[site.id]?.clicks ?? 0).toLocaleString() }}</p>
                                    <p class="text-xs text-gray-400">Clicks</p>
                                </div>
                            </div>
                        </Link>
                    </div>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
