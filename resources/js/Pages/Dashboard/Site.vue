<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, onMounted, ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import SitePicker from '@/Components/SitePicker.vue'
import Chart from 'chart.js/auto'

const props = defineProps({
    site: Object,
    range: Number,
    totalViews: Number,
    uniqueVisitors: Number,
    totalClicks: Number,
    avgDuration: Number,
    viewsChange: Number,
    visitorsChange: Number,
    durationChange: Number,
    clicksChange: Number,
    bounceRate: Number,
    bounceChange: Number,
    totalSessions: Number,
    liveVisitors: Number,
    topPages: Array,
    topCountries: Array,
    browsers: Array,
    devices: Array,
    topClicks: Array,
    chartData: Object,
    referrers: Array,
    recentViews: Array,
})

const durFmt = computed(() => {
    const s = Math.round(props.avgDuration ?? 0)
    if (s >= 60) return `${Math.floor(s / 60)}m ${s % 60}s`
    if (s > 0)   return `${s}s`
    return '—'
})

const badge = (pct, invertBad = false) => {
    if (pct === null || pct === undefined) return null
    const up = invertBad ? pct <= 0 : pct >= 0
    return { pct: Math.abs(pct), up, arrow: pct >= 0 ? '↗' : '↘', label: pct >= 0 ? 'Reach up' : 'Reach down' }
}

const viewsBadge    = computed(() => badge(props.viewsChange))
const visitorsBadge = computed(() => badge(props.visitorsChange))
const durationBadge = computed(() => badge(props.durationChange))
const clicksBadge   = computed(() => badge(props.clicksChange))
const bounceBadge   = computed(() => badge(props.bounceChange, true))

const viewsChart   = ref(null)
const browserChart = ref(null)
const deviceChart  = ref(null)

const embedModal = ref(false)

const setRange = (d) => router.get(`/sites/${props.site.tracking_id}`, { days: d }, { preserveState: false })

const deleteSite = () => {
    if (!confirm('Delete this site and all its data?')) return
    router.delete(`/sites/${props.site.id}`)
}

onMounted(() => {
    const { labels, data } = props.chartData
    const lastNonZero = data.reduce((acc, v, i) => v > 0 ? i : acc, data.length - 1)
    const bgColors    = data.map((_, i) => i === lastNonZero ? '#f97316' : 'rgba(209,196,182,0.45)')
    const hoverColors = data.map((_, i) => i === lastNonZero ? '#ea580c' : 'rgba(209,196,182,0.7)')

    new Chart(viewsChart.value, {
        type: 'bar',
        data: {
            labels,
            datasets: [{ label: 'Page Views', data, backgroundColor: bgColors, hoverBackgroundColor: hoverColors, borderRadius: { topLeft: 8, topRight: 8 }, borderSkipped: false, borderWidth: 0 }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { backgroundColor: '#fff', titleColor: '#111827', bodyColor: '#6b7280', borderColor: '#e5e7eb', borderWidth: 1, cornerRadius: 10, padding: 12, displayColors: false, callbacks: { label: ctx => ctx.parsed.y + ' Visits' } },
            },
            scales: {
                x: { grid: { display: false }, border: { display: false }, ticks: { color: '#9ca3af', font: { size: 11 }, maxTicksLimit: 12 } },
                y: { grid: { color: '#f9fafb' }, border: { display: false, dash: [4,4] }, ticks: { color: '#9ca3af', font: { size: 11 } }, beginAtZero: true },
            },
        },
    })

    const donut = (canvas, lbls, vals) => new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: lbls,
            datasets: [{ data: vals, backgroundColor: ['#f97316','#fb923c','#fdba74','#fcd34d','#86efac','#67e8f9','#a78bfa','#94a3b8'], borderWidth: 2, borderColor: '#fff' }],
        },
        options: { plugins: { legend: { position: 'right', labels: { color: '#6b7280', font: { size: 11 }, padding: 12, boxWidth: 10, usePointStyle: true } } }, cutout: '68%' },
    })

    donut(browserChart.value, props.browsers.map(b => b.browser), props.browsers.map(b => b.cnt))
    donut(deviceChart.value,  props.devices.map(d => d.device),   props.devices.map(d => d.cnt))
})
</script>

<template>
    <Head :title="site.name" />

    <AppLayout>
        <div class="flex flex-col min-h-screen">

            <!-- Top bar -->
            <header class="flex items-center justify-between px-4 py-4 md:px-8 md:py-5 bg-white border-b border-gray-100 gap-3 flex-wrap">
                <div class="flex items-center gap-2 text-sm font-medium text-gray-500 min-w-0">
                    <Link href="/" class="hover:text-gray-800 transition-colors shrink-0">Dashboard</Link>
                    <span class="text-gray-300">/</span>
                    <SitePicker :site="site" route-base="" />
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <div class="flex items-center bg-gray-100 rounded-xl p-1 gap-0.5">
                        <button v-for="d in [7,14,30,60,90]" :key="d" @click="setRange(d)"
                            :class="['px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-colors', range == d ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700']">
                            {{ d }}d
                        </button>
                    </div>

                    <button
                        @click="embedModal = true"
                        class="border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-600 hover:border-gray-300 transition-colors bg-white font-mono"
                    >&lt;/&gt;</button>

                    <button @click="deleteSite" class="border border-red-200 rounded-xl px-3 py-2 text-sm text-red-500 hover:bg-red-50 transition-colors bg-white">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/>
                        </svg>
                    </button>
                </div>
            </header>

            <!-- Body -->
            <div class="flex-1 p-4 md:p-8 space-y-6">

                <!-- Live banner -->
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-2 bg-white border border-gray-100 rounded-full px-4 py-2 text-sm font-medium shadow-sm">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        Live Traffic: <strong class="ml-0.5">{{ liveVisitors }}</strong>
                    </span>
                    <span class="inline-flex items-center gap-2 bg-white border border-gray-100 rounded-full px-4 py-2 text-sm font-medium shadow-sm">
                        <span class="inline-flex rounded-full h-2 w-2 bg-orange-400"></span>
                        Bots Excluded: <strong class="ml-0.5">0</strong>
                    </span>
                </div>

                <!-- Stat cards -->
                <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">

                    <!-- Traffic -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 bg-orange-100 rounded-full flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </div>
                                <span class="text-sm font-semibold text-gray-800">Site Traffic</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1 mb-3 text-xs">
                            <template v-if="viewsBadge">
                                <span class="text-gray-500 mr-1">{{ viewsBadge.label }}</span>
                                <span :class="['inline-flex items-center gap-0.5 text-xs font-semibold px-2 py-0.5 rounded-full', viewsBadge.up ? 'text-green-700 bg-green-100' : 'text-red-600 bg-red-100']">{{ viewsBadge.arrow }} {{ viewsBadge.pct }}%</span>
                            </template>
                            <span v-else class="text-xs text-gray-400">No prior data</span>
                        </div>
                        <div class="flex items-baseline gap-4">
                            <div><span class="text-2xl font-bold text-gray-900">{{ totalViews.toLocaleString() }}</span><span class="text-xs text-gray-400 ml-1">Views</span></div>
                            <div><span class="text-2xl font-bold text-gray-900">{{ uniqueVisitors.toLocaleString() }}</span><span class="text-xs text-gray-400 ml-1">Users</span></div>
                        </div>
                    </div>

                    <!-- Duration -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 bg-orange-100 rounded-full flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                </div>
                                <span class="text-sm font-semibold text-gray-800">Avg Duration</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1 mb-3 text-xs">
                            <template v-if="durationBadge">
                                <span class="text-gray-500 mr-1">{{ durationBadge.label }}</span>
                                <span :class="['inline-flex items-center gap-0.5 text-xs font-semibold px-2 py-0.5 rounded-full', durationBadge.up ? 'text-green-700 bg-green-100' : 'text-red-600 bg-red-100']">{{ durationBadge.arrow }} {{ durationBadge.pct }}%</span>
                            </template>
                            <span v-else class="text-xs text-gray-400">No prior data</span>
                        </div>
                        <div><span class="text-2xl font-bold text-gray-900">{{ durFmt }}</span><span class="text-xs text-gray-400 ml-1">Avg</span></div>
                    </div>

                    <!-- Bounce -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 bg-orange-100 rounded-full flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
                                </div>
                                <span class="text-sm font-semibold text-gray-800">Bounce Rate</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1 mb-3 text-xs">
                            <template v-if="bounceBadge">
                                <span class="text-gray-500 mr-1">{{ bounceBadge.label }}</span>
                                <span :class="['inline-flex items-center gap-0.5 text-xs font-semibold px-2 py-0.5 rounded-full', bounceBadge.up ? 'text-green-700 bg-green-100' : 'text-red-600 bg-red-100']">{{ bounceBadge.arrow }} {{ bounceBadge.pct }}%</span>
                            </template>
                            <span v-else class="text-xs text-gray-400">No prior data</span>
                        </div>
                        <div class="flex items-baseline gap-4">
                            <div><span class="text-2xl font-bold text-gray-900">{{ bounceRate }}%</span><span class="text-xs text-gray-400 ml-1">Rate</span></div>
                            <div><span class="text-2xl font-bold text-gray-900">{{ totalSessions.toLocaleString() }}</span><span class="text-xs text-gray-400 ml-1">Sessions</span></div>
                        </div>
                    </div>

                    <!-- Clicks -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 bg-orange-100 rounded-full flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 3l14 9-14 9V3z"/></svg>
                                </div>
                                <span class="text-sm font-semibold text-gray-800">Clicks</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1 mb-3 text-xs">
                            <template v-if="clicksBadge">
                                <span class="text-gray-500 mr-1">{{ clicksBadge.label }}</span>
                                <span :class="['inline-flex items-center gap-0.5 text-xs font-semibold px-2 py-0.5 rounded-full', clicksBadge.up ? 'text-green-700 bg-green-100' : 'text-red-600 bg-red-100']">{{ clicksBadge.arrow }} {{ clicksBadge.pct }}%</span>
                            </template>
                            <span v-else class="text-xs text-gray-400">No prior data</span>
                        </div>
                        <div><span class="text-2xl font-bold text-gray-900">{{ totalClicks.toLocaleString() }}</span><span class="text-xs text-gray-400 ml-1">Total</span></div>
                    </div>
                </div>

                <!-- Views chart -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h2 class="font-semibold text-gray-900 flex items-center gap-2 mb-5">
                        <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        Visits Over Time
                    </h2>
                    <canvas ref="viewsChart" height="90"></canvas>
                </div>

                <!-- Bottom grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                    <!-- Top Pages -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <h2 class="font-semibold text-gray-900 mb-4 text-sm">Top Pages</h2>
                        <div v-if="!topPages.length" class="text-sm text-gray-400 py-4 text-center">No data yet.</div>
                        <div v-for="(p, i) in topPages" :key="i" :class="['flex items-center justify-between py-2.5', i < topPages.length-1 ? 'border-b border-gray-50' : '']">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="text-xs text-gray-300 font-mono w-5 text-right shrink-0">{{ i+1 }}</span>
                                <span class="text-sm text-gray-600 truncate" :title="p.url">{{ p.url }}</span>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 ml-3 shrink-0">{{ p.views.toLocaleString() }}</span>
                        </div>
                    </div>

                    <!-- Top Countries -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <h2 class="font-semibold text-gray-900 mb-4 text-sm">Top Countries</h2>
                        <div v-if="!topCountries.length" class="text-sm text-gray-400 py-4 text-center">No data yet.</div>
                        <div v-for="(c, i) in topCountries" :key="i" :class="['flex items-center justify-between py-2.5', i < topCountries.length-1 ? 'border-b border-gray-50' : '']">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="text-xs text-gray-300 font-mono w-5 text-right shrink-0">{{ i+1 }}</span>
                                <img v-if="c.country_code" :src="`https://flagcdn.com/16x12/${c.country_code.toLowerCase()}.png`" :alt="c.country_code" class="rounded-sm shrink-0" width="16" height="12">
                                <span class="text-sm text-gray-600 truncate">{{ c.country }}</span>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 ml-3 shrink-0">{{ c.views.toLocaleString() }}</span>
                        </div>
                    </div>

                    <!-- Browsers -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <h2 class="font-semibold text-gray-900 mb-4 text-sm">Browsers</h2>
                        <canvas ref="browserChart" height="180"></canvas>
                    </div>

                    <!-- Devices -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <h2 class="font-semibold text-gray-900 mb-4 text-sm">Devices</h2>
                        <canvas ref="deviceChart" height="180"></canvas>
                    </div>

                    <!-- Referrers -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <h2 class="font-semibold text-gray-900 mb-4 text-sm">Top Referrers</h2>
                        <div v-if="!referrers.length" class="text-sm text-gray-400 py-4 text-center">No referrers yet.</div>
                        <div v-for="(r, i) in referrers" :key="i" :class="['flex items-center justify-between py-2.5', i < referrers.length-1 ? 'border-b border-gray-50' : '']">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="text-xs text-gray-300 font-mono w-5 text-right shrink-0">{{ i+1 }}</span>
                                <span class="text-sm text-gray-600 truncate" :title="r.referrer">{{ r.referrer }}</span>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 ml-3 shrink-0">{{ r.cnt.toLocaleString() }}</span>
                        </div>
                    </div>

                    <!-- Top Clicks -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <h2 class="font-semibold text-gray-900 mb-4 text-sm">Most Clicked Elements</h2>
                        <div v-if="!topClicks.length" class="text-sm text-gray-400 py-4 text-center">No clicks tracked yet.</div>
                        <div v-for="(c, i) in topClicks" :key="i" :class="['flex items-center justify-between py-2.5', i < topClicks.length-1 ? 'border-b border-gray-50' : '']">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="text-xs text-gray-300 font-mono w-5 text-right shrink-0">{{ i+1 }}</span>
                                <span class="text-xs font-mono bg-orange-50 text-orange-600 px-1.5 py-0.5 rounded-md shrink-0">{{ (c.element_tag || '?').toLowerCase() }}</span>
                                <span class="text-sm text-gray-600 truncate">{{ (c.element_text || c.element_href || '(no text)').substring(0, 40) }}</span>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 ml-3 shrink-0">{{ c.cnt.toLocaleString() }}</span>
                        </div>
                    </div>
                </div>

                <!-- Recent Views -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <h2 class="font-semibold text-gray-900 mb-4 text-sm">Recent Page Views</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-xs font-semibold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                                    <th class="text-left pb-3 pr-4 font-medium">Time</th>
                                    <th class="text-left pb-3 pr-4 font-medium">URL</th>
                                    <th class="text-left pb-3 pr-4 font-medium">Country</th>
                                    <th class="text-left pb-3 pr-4 font-medium">Browser</th>
                                    <th class="text-left pb-3 font-medium">Device</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!recentViews.length">
                                    <td colspan="5" class="py-8 text-gray-400 text-center text-xs">No views yet.</td>
                                </tr>
                                <tr v-for="v in recentViews" :key="v.id" class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                                    <td class="py-3 pr-4 text-gray-400 whitespace-nowrap text-xs">{{ v.created_at_human }}</td>
                                    <td class="py-3 pr-4 max-w-xs truncate text-gray-700 text-xs" :title="v.url">{{ v.url }}</td>
                                    <td class="py-3 pr-4 text-gray-500 text-xs">{{ v.country || '—' }}</td>
                                    <td class="py-3 pr-4 text-gray-500 text-xs">{{ v.browser || '—' }}</td>
                                    <td class="py-3 text-gray-500 text-xs">{{ v.device || '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Embed Modal -->
        <Teleport to="body">
            <div v-if="embedModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="embedModal = false">
                <div class="bg-white rounded-2xl p-6 w-full max-w-2xl shadow-2xl border border-gray-100">
                    <h2 class="text-lg font-bold mb-1">Embed Tracking Script</h2>
                    <p class="text-sm text-gray-500 mb-4">Paste this snippet into the <code class="text-orange-500 bg-orange-50 px-1 py-0.5 rounded text-xs">&lt;head&gt;</code> of every page you want to track.</p>
                    <pre class="bg-gray-950 rounded-xl p-4 text-xs text-emerald-400 overflow-x-auto whitespace-pre-wrap font-mono leading-relaxed"><code id="embed-code">&lt;!-- Alyse Analytics --&gt;
&lt;script&gt;
window._analyticsId = '{{ site.tracking_id }}';
window._analyticsUrl = '{{ $page.props.appUrl }}';
&lt;/script&gt;
&lt;script src="{{ $page.props.appUrl }}/tracker.js" defer&gt;&lt;/script&gt;</code></pre>
                    <div class="flex gap-3 mt-4">
                        <button @click="() => { navigator.clipboard.writeText(document.getElementById('embed-code').innerText); }" class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">Copy to Clipboard</button>
                        <button @click="embedModal = false" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">Close</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
