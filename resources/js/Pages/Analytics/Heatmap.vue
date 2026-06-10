<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { onMounted, ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import SitePicker from '@/Components/SitePicker.vue'

const props = defineProps({
    site: Object,
    pages: Array,
    selectedPage: String,
    clicks: Array,
    topElements: Array,
})

const heatmapCanvas = ref(null)

const selectPage = (url) =>
    router.get(`/sites/${props.site.tracking_id}/heatmap`, { page: url }, { preserveState: false })

const pathOf = (url) => {
    try { return new URL(url).pathname || '/' } catch { return url }
}

onMounted(() => {
    if (!heatmapCanvas.value || !props.clicks?.length) return
    drawHeatmap()
    window.addEventListener('resize', drawHeatmap)
})

function drawHeatmap() {
    const canvas = heatmapCanvas.value
    const points = props.clicks
    if (!canvas || !points.length) return

    const w = canvas.offsetWidth
    const h = canvas.offsetHeight
    canvas.width  = w
    canvas.height = h
    const ctx = canvas.getContext('2d')
    ctx.fillStyle = '#1a1a2e'
    ctx.fillRect(0, 0, w, h)

    const maxX   = Math.max(...points.map(p => p.x_pos), 1920)
    const maxY   = Math.max(...points.map(p => p.y_pos), 1080)
    const maxCnt = Math.max(...points.map(p => p.cnt), 1)

    const temp = document.createElement('canvas')
    temp.width  = w
    temp.height = h
    const tc = temp.getContext('2d')
    tc.globalCompositeOperation = 'lighter'

    points.forEach(({ x_pos, y_pos, cnt }) => {
        const px = (x_pos / maxX) * w
        const py = (y_pos / maxY) * h
        const intensity = cnt / maxCnt
        const radius    = 24 + intensity * 32
        const grad = tc.createRadialGradient(px, py, 0, px, py, radius)
        grad.addColorStop(0,   `rgba(255,255,255,${0.3 + intensity * 0.5})`)
        grad.addColorStop(0.4, `rgba(255,255,255,${0.1 + intensity * 0.2})`)
        grad.addColorStop(1,   'rgba(255,255,255,0)')
        tc.beginPath()
        tc.arc(px, py, radius, 0, Math.PI * 2)
        tc.fillStyle = grad
        tc.fill()
    })

    const colorMap = [[0,0,0,0],[0,0,255,50],[0,128,255,120],[0,255,128,180],[128,255,0,220],[255,200,0,245],[255,80,0,265],[255,0,0,280]]
    const mapColor = (v) => {
        if (v <= 0) return [0,0,0,0]
        const n = v / 255
        for (let i = 0; i < colorMap.length - 1; i++) {
            const a = colorMap[i], b = colorMap[i+1]
            const t0 = a[3] / 280, t1 = b[3] / 280
            if (n >= t0 && n <= t1) {
                const f = (n - t0) / (t1 - t0)
                return [Math.round(a[0]+(b[0]-a[0])*f), Math.round(a[1]+(b[1]-a[1])*f), Math.round(a[2]+(b[2]-a[2])*f), Math.round((a[3]+(b[3]-a[3])*f)/280*220)]
            }
        }
        return [255,0,0,220]
    }

    const imgData = tc.getImageData(0, 0, w, h)
    const outData = ctx.createImageData(w, h)
    for (let i = 0; i < imgData.data.length; i += 4) {
        const [r,g,b,a] = mapColor(imgData.data[i])
        outData.data[i] = r; outData.data[i+1] = g; outData.data[i+2] = b; outData.data[i+3] = a
    }
    ctx.putImageData(outData, 0, 0)
}

const totalClicks = () => props.clicks.reduce((a, p) => a + p.cnt, 0)
</script>

<template>
    <Head :title="`${site.name} — Heatmap`" />

    <AppLayout>
        <div class="flex flex-col min-h-screen">

            <header class="flex items-center justify-between px-8 py-5 bg-white border-b border-gray-100 gap-4 flex-wrap">
                <div class="flex items-center gap-2 text-sm font-medium text-gray-500">
                    <Link href="/" class="hover:text-gray-800 transition-colors">Dashboard</Link>
                    <span class="text-gray-300">/</span>
                    <SitePicker :site="site" route-base="heatmap" />
                    <span class="text-gray-300">/</span>
                    <span class="text-gray-800 font-semibold">Heatmap</span>
                </div>
            </header>

            <div class="flex-1 p-8 space-y-6">

                <div v-if="!pages.length" class="flex flex-col items-center justify-center py-24 text-center bg-white rounded-2xl border border-gray-100">
                    <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center mb-4">
                        <svg class="w-7 h-7 text-orange-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 2C9 5 7 8 7 11a5 5 0 0010 0c0-3-2-6-5-9z"/></svg>
                    </div>
                    <p class="text-gray-500 font-medium">No click data yet.</p>
                    <p class="text-sm text-gray-400 mt-1">Start tracking clicks by embedding the tracker on your site.</p>
                </div>

                <template v-else>
                    <!-- Page selector -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <div class="flex items-center gap-3 flex-wrap">
                            <span class="text-sm font-semibold text-gray-700">Select Page:</span>
                            <div class="flex flex-wrap gap-2">
                                <button v-for="pg in pages.slice(0,10)" :key="pg.page_url"
                                    @click="selectPage(pg.page_url)"
                                    :class="['px-3 py-1.5 rounded-xl text-xs font-medium transition-colors', selectedPage === pg.page_url ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200']">
                                    {{ pathOf(pg.page_url).substring(0, 40) || '/' }}
                                    <span class="opacity-60 ml-1">{{ pg.total }}</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <template v-if="selectedPage">
                        <!-- Heatmap canvas -->
                        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="font-semibold text-gray-900 text-sm">Click Heatmap</h2>
                                <div class="flex items-center gap-2 text-xs text-gray-400">
                                    <div class="w-8 h-2 rounded-full" style="background: linear-gradient(to right, rgba(59,130,246,0.3), rgba(249,115,22,0.6), rgba(220,38,38,0.9))"></div>
                                    <span>Low → High</span>
                                </div>
                            </div>
                            <p class="text-xs text-gray-400 mb-4">Viewport-relative click positions for: <span class="font-mono text-gray-600">{{ selectedPage.substring(0, 80) }}</span></p>

                            <div v-if="clicks.length" class="relative rounded-xl overflow-hidden bg-gray-900" style="height: 480px">
                                <canvas ref="heatmapCanvas" class="absolute inset-0 w-full h-full"></canvas>
                                <div class="absolute bottom-3 right-3 bg-black/50 text-white text-xs px-2 py-1 rounded-lg">{{ totalClicks() }} total clicks</div>
                            </div>
                            <div v-else class="flex items-center justify-center h-48 bg-gray-50 rounded-xl">
                                <p class="text-sm text-gray-400">No position data for this page.</p>
                            </div>
                        </div>

                        <!-- Top elements -->
                        <div v-if="topElements.length" class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                            <h2 class="font-semibold text-gray-900 mb-4 text-sm">Most Clicked Elements on This Page</h2>
                            <div v-for="(el, i) in topElements" :key="i" :class="['flex items-center justify-between py-2.5', i < topElements.length-1 ? 'border-b border-gray-50' : '']">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="text-xs text-gray-300 font-mono w-5 text-right shrink-0">{{ i+1 }}</span>
                                    <span class="text-xs font-mono bg-orange-50 text-orange-600 px-1.5 py-0.5 rounded-md shrink-0">{{ (el.element_tag || '?').toLowerCase() }}</span>
                                    <span class="text-sm text-gray-600 truncate">{{ (el.element_text || el.element_href || '(no text)').substring(0, 60) }}</span>
                                </div>
                                <span class="text-sm font-semibold text-gray-900 ml-3 shrink-0">{{ el.cnt.toLocaleString() }}</span>
                            </div>
                        </div>
                    </template>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
