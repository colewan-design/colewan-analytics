<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import SitePicker from '@/Components/SitePicker.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    site: Object,
    range: Number,
    stats: Object,
    byPage: Array,
    recentFeedback: Object,
    appUrl: String,
})

const setRange = (d) => router.get(`/sites/${props.site.tracking_id}/feedback`, { days: d }, { preserveState: false })

const pct = (n, total) => total > 0 ? Math.round(n / total * 100) : 0

const ratingEmoji = (r) => r === 3 ? '😊' : r === 2 ? '😐' : '😞'

const pathOf = (url) => {
    try { return new URL(url).pathname || '/' } catch { return url }
}
</script>

<template>
    <Head :title="`${site.name} — Feedback`" />

    <AppLayout>
        <div class="flex flex-col min-h-screen">

            <header class="flex items-center justify-between px-8 py-5 bg-white border-b border-gray-100 gap-4 flex-wrap">
                <div class="flex items-center gap-2 text-sm font-medium text-gray-500">
                    <Link href="/" class="hover:text-gray-800 transition-colors">Dashboard</Link>
                    <span class="text-gray-300">/</span>
                    <SitePicker :site="site" route-base="feedback" />
                    <span class="text-gray-300">/</span>
                    <span class="text-gray-800 font-semibold">Feedback</span>
                </div>
                <div class="flex items-center bg-gray-100 rounded-xl p-1 gap-0.5">
                    <button v-for="d in [7,14,30,60,90]" :key="d" @click="setRange(d)"
                        :class="['px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors', range == d ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700']">
                        {{ d }}d
                    </button>
                </div>
            </header>

            <div class="flex-1 p-8 space-y-6">

                <!-- Stats -->
                <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <p class="text-xs font-semibold text-gray-400 mb-1">Total Responses</p>
                        <p class="text-3xl font-bold text-gray-900">{{ (stats?.total ?? 0).toLocaleString() }}</p>
                    </div>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <p class="text-xs font-semibold text-gray-400 mb-1">Satisfaction Rate</p>
                        <p :class="['text-3xl font-bold', pct(stats?.positive??0, stats?.total??0) >= 70 ? 'text-green-600' : pct(stats?.positive??0, stats?.total??0) >= 40 ? 'text-yellow-600' : 'text-red-500']">
                            {{ pct(stats?.positive ?? 0, stats?.total ?? 0) }}%
                        </p>
                    </div>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <p class="text-xs font-semibold text-gray-400 mb-2">Rating Breakdown</p>
                        <div class="space-y-1.5">
                            <div v-for="[emoji, key, color] in [['😊','positive','bg-green-400'],['😐','neutral','bg-yellow-400'],['😞','negative','bg-red-400']]" :key="key" class="flex items-center gap-2 text-xs">
                                <span class="text-base">{{ emoji }}</span>
                                <div class="flex-1 h-1.5 bg-gray-100 rounded-full">
                                    <div :class="['h-full rounded-full', color]" :style="{ width: pct(stats?.[key]??0, stats?.total??0) + '%' }"></div>
                                </div>
                                <span class="text-gray-500 w-6 text-right">{{ stats?.[key] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <p class="text-xs font-semibold text-gray-400 mb-1">Avg Score</p>
                        <p class="text-3xl font-bold text-gray-900">{{ (stats?.avg_rating ?? 0).toFixed(1) }}<span class="text-sm text-gray-400 font-normal"> / 3</span></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                    <!-- By page -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <h2 class="font-semibold text-gray-900 mb-4 text-sm">Feedback by Page</h2>
                        <div v-if="!byPage.length" class="text-sm text-gray-400 py-4 text-center">No feedback yet.</div>
                        <div v-for="(row, i) in byPage" :key="i" :class="['flex items-center justify-between py-2.5', i < byPage.length-1 ? 'border-b border-gray-50' : '']">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="text-xs text-gray-300 font-mono w-5 text-right shrink-0">{{ i+1 }}</span>
                                <span class="text-sm text-gray-600 truncate" :title="row.page_url">{{ pathOf(row.page_url).substring(0, 45) }}</span>
                            </div>
                            <div class="flex items-center gap-2 ml-3 shrink-0">
                                <span class="text-xs text-gray-400">{{ row.cnt }}</span>
                                <span class="text-base">{{ row.avg >= 2.5 ? '😊' : row.avg >= 1.5 ? '😐' : '😞' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Collect snippet -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <h2 class="font-semibold text-gray-900 mb-3 text-sm">Collect Feedback on Your Site</h2>
                        <p class="text-xs text-gray-500 mb-4">Add this snippet after your tracking script to show a satisfaction widget on your pages.</p>
                        <pre id="feedback-snippet" class="bg-gray-950 rounded-xl p-4 text-xs text-emerald-400 overflow-x-auto whitespace-pre-wrap font-mono leading-relaxed"><code>&lt;script src="{{ $page.props.appUrl }}/feedback.js" defer&gt;&lt;/script&gt;</code></pre>
                        <button @click="() => { navigator.clipboard.writeText(document.querySelector('#feedback-snippet code').innerText) }" class="mt-3 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">Copy</button>
                        <div class="mt-4 p-3 bg-orange-50 rounded-xl text-xs text-orange-700">
                            <strong>Widget preview:</strong> Shows a floating 😊 😐 😞 prompt — visitors can rate your page in one click.
                        </div>
                    </div>
                </div>

                <!-- Recent feedback table -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <h2 class="font-semibold text-gray-900 mb-4 text-sm">Recent Feedback</h2>
                    <div v-if="!recentFeedback.data.length" class="text-sm text-gray-400 py-8 text-center">No feedback received yet in this period.</div>
                    <template v-else>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-xs font-semibold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                                        <th class="text-left pb-3 pr-4 font-medium">Time</th>
                                        <th class="text-left pb-3 pr-4 font-medium">Rating</th>
                                        <th class="text-left pb-3 pr-4 font-medium">Page</th>
                                        <th class="text-left pb-3 font-medium">Comment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="fb in recentFeedback.data" :key="fb.id" class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                                        <td class="py-3 pr-4 text-gray-400 whitespace-nowrap text-xs">{{ fb.created_at_human }}</td>
                                        <td class="py-3 pr-4 text-xl">{{ ratingEmoji(fb.rating) }}</td>
                                        <td class="py-3 pr-4 max-w-xs truncate text-gray-600 text-xs" :title="fb.page_url">{{ pathOf(fb.page_url).substring(0, 50) }}</td>
                                        <td class="py-3 text-gray-500 text-xs">{{ fb.comment || '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <Pagination :links="recentFeedback.meta?.links ?? recentFeedback.links" />
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
