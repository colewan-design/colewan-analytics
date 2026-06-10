<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { onMounted, ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import SitePicker from '@/Components/SitePicker.vue'
import Chart from 'chart.js/auto'

const props = defineProps({
    site: Object,
    range: Number,
    labels: Array,
    viewData: Array,
    sessionData: Array,
    hourLabels: Array,
    hourData: Array,
    dowLabels: Array,
    dowData: Array,
    osList: Array,
    languages: Array,
    screens: Array,
})

const overTimeChart = ref(null)
const hourlyChart   = ref(null)
const dowChart      = ref(null)

const setRange = (d) => router.get(`/sites/${props.site.tracking_id}/charts`, { days: d }, { preserveState: false })

const sumList = (list) => list.reduce((a, b) => a + b.cnt, 0)

onMounted(() => {
    const tick  = '#9ca3af'
    const grid  = '#f9fafb'
    const tip   = { backgroundColor: '#fff', titleColor: '#111827', bodyColor: '#6b7280', borderColor: '#e5e7eb', borderWidth: 1, cornerRadius: 10, padding: 12, displayColors: true }
    const axes  = {
        x: { grid: { display: false }, border: { display: false }, ticks: { color: tick, font: { size: 11 }, maxTicksLimit: 14 } },
        y: { grid: { color: grid }, border: { display: false }, ticks: { color: tick, font: { size: 11 } }, beginAtZero: true },
    }

    new Chart(overTimeChart.value, {
        type: 'line',
        data: {
            labels: props.labels,
            datasets: [
                { label: 'Page Views', data: props.viewData, borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,0.08)', tension: 0.3, fill: true, pointRadius: 3, pointHoverRadius: 5, pointBackgroundColor: '#f97316', borderWidth: 2 },
                { label: 'Sessions',   data: props.sessionData, borderColor: '#94a3b8', backgroundColor: 'transparent', tension: 0.3, fill: false, pointRadius: 2, pointHoverRadius: 4, pointBackgroundColor: '#94a3b8', borderWidth: 1.5, borderDash: [4,3] },
            ],
        },
        options: { responsive: true, plugins: { legend: { labels: { color: '#6b7280', font: { size: 11 }, padding: 16, usePointStyle: true } }, tooltip: tip }, scales: axes },
    })

    const barOpts = (data, labels, label, color) => ({
        type: 'bar',
        data: { labels, datasets: [{ label, data, backgroundColor: color, borderRadius: { topLeft: 5, topRight: 5 }, borderSkipped: false, borderWidth: 0 }] },
        options: { responsive: true, plugins: { legend: { display: false }, tooltip: tip }, scales: { x: { grid: { display: false }, border: { display: false }, ticks: { color: tick, font: { size: 10 } } }, y: { grid: { color: grid }, border: { display: false }, ticks: { color: tick, font: { size: 11 } }, beginAtZero: true } } },
    })

    new Chart(hourlyChart.value, barOpts(props.hourData, props.hourLabels, 'Views', 'rgba(249,115,22,0.7)'))
    new Chart(dowChart.value,    barOpts(props.dowData,  props.dowLabels,  'Views', 'rgba(249,115,22,0.7)'))
})
</script>

<template>
    <Head :title="`${site.name} — Charts`" />

    <AppLayout>
        <div class="flex flex-col min-h-screen">

            <header class="flex items-center justify-between px-8 py-5 bg-white border-b border-gray-100 gap-4 flex-wrap">
                <div class="flex items-center gap-2 text-sm font-medium text-gray-500">
                    <Link href="/" class="hover:text-gray-800 transition-colors">Dashboard</Link>
                    <span class="text-gray-300">/</span>
                    <SitePicker :site="site" route-base="charts" />
                    <span class="text-gray-300">/</span>
                    <span class="text-gray-800 font-semibold">Charts</span>
                </div>
                <div class="flex items-center bg-gray-100 rounded-xl p-1 gap-0.5">
                    <button v-for="d in [7,14,30,60,90]" :key="d" @click="setRange(d)"
                        :class="['px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors', range == d ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700']">
                        {{ d }}d
                    </button>
                </div>
            </header>

            <div class="flex-1 p-8 space-y-6">

                <!-- Over-time chart -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h2 class="font-semibold text-gray-900 mb-5 text-sm flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-orange-400 shrink-0"></span>
                        Views & Sessions Over Time
                    </h2>
                    <canvas ref="overTimeChart" height="90"></canvas>
                </div>

                <!-- Hourly + DoW -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <h2 class="font-semibold text-gray-900 mb-5 text-sm flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-orange-400 shrink-0"></span>
                            Traffic by Hour of Day
                        </h2>
                        <canvas ref="hourlyChart" height="160"></canvas>
                    </div>
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <h2 class="font-semibold text-gray-900 mb-5 text-sm flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-orange-400 shrink-0"></span>
                            Traffic by Day of Week
                        </h2>
                        <canvas ref="dowChart" height="160"></canvas>
                    </div>
                </div>

                <!-- OS + Languages + Screens -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                    <!-- OS -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <h2 class="font-semibold text-gray-900 mb-4 text-sm">Operating Systems</h2>
                        <div v-if="!osList.length" class="text-sm text-gray-400 py-4 text-center">No data yet.</div>
                        <div v-for="row in osList" :key="row.os" class="mb-3">
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-700 font-medium truncate">{{ row.os || 'Unknown' }}</span>
                                <span class="text-gray-400 shrink-0 ml-2">{{ row.cnt.toLocaleString() }} ({{ sumList(osList) > 0 ? Math.round(row.cnt / sumList(osList) * 100) : 0 }}%)</span>
                            </div>
                            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full bg-orange-400 transition-all" :style="{ width: (sumList(osList) > 0 ? Math.round(row.cnt / sumList(osList) * 100) : 0) + '%' }"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Languages -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <h2 class="font-semibold text-gray-900 mb-4 text-sm">Languages</h2>
                        <div v-if="!languages.length" class="text-sm text-gray-400 py-4 text-center">No data yet.</div>
                        <div v-for="row in languages" :key="row.language" class="mb-3">
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-700 font-medium">{{ row.language || 'Unknown' }}</span>
                                <span class="text-gray-400 shrink-0 ml-2">{{ row.cnt.toLocaleString() }} ({{ sumList(languages) > 0 ? Math.round(row.cnt / sumList(languages) * 100) : 0 }}%)</span>
                            </div>
                            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full bg-orange-300 transition-all" :style="{ width: (sumList(languages) > 0 ? Math.round(row.cnt / sumList(languages) * 100) : 0) + '%' }"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Screens -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <h2 class="font-semibold text-gray-900 mb-4 text-sm">Screen Resolutions</h2>
                        <div v-if="!screens.length" class="text-sm text-gray-400 py-4 text-center">No data yet.</div>
                        <div v-for="(row, i) in screens" :key="i" :class="['flex items-center justify-between py-2.5', i < screens.length-1 ? 'border-b border-gray-50' : '']">
                            <span class="text-xs text-gray-700 font-mono">{{ row.screen_width }}×{{ row.screen_height }}</span>
                            <span class="text-sm font-semibold text-gray-900 ml-3">{{ row.cnt.toLocaleString() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
