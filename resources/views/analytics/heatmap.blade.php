@extends('layouts.app')
@section('title', $site->name . ' — Heatmap — Alyse')

@php $allSites = \App\Models\AnalyticsSite::orderBy('name')->get(); @endphp

@section('content')
<div class="flex flex-col min-h-screen">

{{-- Top bar --}}
<header class="flex items-center justify-between px-8 py-5 bg-white border-b border-gray-100 gap-4 flex-wrap">
    <div class="flex items-center gap-2 text-sm font-medium text-gray-500">
        <a href="{{ route('dashboard.index') }}" class="hover:text-gray-800 transition-colors">Dashboard</a>
        <span class="text-gray-300">/</span>
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open"
                class="flex items-center gap-1.5 border border-gray-200 rounded-full px-3 py-1.5 text-gray-800 font-semibold hover:border-orange-300 hover:bg-orange-50 transition-colors text-sm">
                {{ $site->domain ?: $site->name }}
                <svg class="w-3.5 h-3.5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
            </button>
            <div x-show="open" x-cloak @click.outside="open = false"
                class="absolute left-0 top-full mt-1.5 bg-white border border-gray-100 rounded-xl shadow-lg z-20 min-w-44 py-1">
                @foreach($allSites as $s)
                <a href="{{ route('analytics.heatmap', $s->tracking_id) }}"
                    class="block px-4 py-2 text-sm hover:bg-gray-50 {{ $s->id === $site->id ? 'text-orange-600 font-semibold' : 'text-gray-700' }}">
                    {{ $s->domain ?: $s->name }}
                </a>
                @endforeach
            </div>
        </div>
        <span class="text-gray-300">/</span>
        <span class="text-gray-800 font-semibold">Heatmap</span>
    </div>
</header>

<div class="flex-1 p-8 space-y-6">

    @if($pages->isEmpty())
    <div class="flex flex-col items-center justify-center py-24 text-center bg-white rounded-2xl border border-gray-100">
        <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center mb-4">
            <svg class="w-7 h-7 text-orange-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 2C9 5 7 8 7 11a5 5 0 0010 0c0-3-2-6-5-9z"/></svg>
        </div>
        <p class="text-gray-500 font-medium">No click data yet.</p>
        <p class="text-sm text-gray-400 mt-1">Start tracking clicks by embedding the tracker on your site.</p>
    </div>
    @else

    {{-- Page selector --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center gap-3 flex-wrap">
            <span class="text-sm font-semibold text-gray-700">Select Page:</span>
            <div class="flex flex-wrap gap-2">
                @foreach($pages->take(10) as $page)
                <a href="?page={{ urlencode($page->page_url) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-medium transition-colors {{ $selectedPage === $page->page_url ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    {{ Str::limit(parse_url($page->page_url, PHP_URL_PATH) ?: '/', 40) }}
                    <span class="opacity-60 ml-1">{{ $page->total }}</span>
                </a>
                @endforeach
            </div>
        </div>
    </div>

    @if($selectedPage)
    {{-- Heatmap canvas --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-900 text-sm">Click Heatmap</h2>
            <div class="flex items-center gap-2 text-xs text-gray-400">
                <div class="flex items-center gap-1">
                    <div class="w-8 h-2 rounded-full" style="background: linear-gradient(to right, rgba(59,130,246,0.3), rgba(249,115,22,0.6), rgba(220,38,38,0.9))"></div>
                </div>
                <span>Low → High</span>
            </div>
        </div>
        <p class="text-xs text-gray-400 mb-4">
            Showing viewport-relative click positions for: <span class="font-mono text-gray-600">{{ Str::limit($selectedPage, 80) }}</span>
        </p>

        @if(count($clicks) > 0)
        <div class="relative rounded-xl overflow-hidden bg-gray-900" style="height: 480px;">
            <canvas id="heatmapCanvas" class="absolute inset-0 w-full h-full"></canvas>
            <div class="absolute bottom-3 right-3 bg-black/50 text-white text-xs px-2 py-1 rounded-lg">
                {{ array_sum(array_column($clicks, 'cnt')) }} total clicks
            </div>
        </div>
        @else
        <div class="flex items-center justify-center h-48 bg-gray-50 rounded-xl">
            <p class="text-sm text-gray-400">No position data for this page.</p>
        </div>
        @endif
    </div>

    {{-- Top clicked elements --}}
    @if($topElements->isNotEmpty())
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <h2 class="font-semibold text-gray-900 mb-4 text-sm">Most Clicked Elements on This Page</h2>
        @foreach($topElements as $i => $el)
        <div class="flex items-center justify-between py-2.5 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
            <div class="flex items-center gap-3 min-w-0">
                <span class="text-xs text-gray-300 font-mono w-5 text-right shrink-0">{{ $i + 1 }}</span>
                <span class="text-xs font-mono bg-orange-50 text-orange-600 px-1.5 py-0.5 rounded-md shrink-0">{{ strtolower($el->element_tag ?? '?') }}</span>
                <span class="text-sm text-gray-600 truncate">{{ Str::limit($el->element_text ?: $el->element_href ?: '(no text)', 60) }}</span>
            </div>
            <span class="text-sm font-semibold text-gray-900 ml-3 shrink-0">{{ number_format($el->cnt) }}</span>
        </div>
        @endforeach
    </div>
    @endif
    @endif
    @endif

</div>
</div>

@push('scripts')
@if(count($clicks) > 0)
<script type="application/json" id="heatmap-data">{!! json_encode($clicks) !!}</script>
<script>
(function () {
    const points = JSON.parse(document.getElementById('heatmap-data').textContent);
    const canvas = document.getElementById('heatmapCanvas');
    if (!canvas || !points.length) return;

    function draw() {
        const w = canvas.offsetWidth;
        const h = canvas.offsetHeight;
        canvas.width  = w;
        canvas.height = h;
        const ctx = canvas.getContext('2d');

        ctx.fillStyle = '#1a1a2e';
        ctx.fillRect(0, 0, w, h);

        if (!points.length) return;

        // Find max coords to normalize
        const maxX   = Math.max(...points.map(p => p.x_pos), 1920);
        const maxY   = Math.max(...points.map(p => p.y_pos), 1080);
        const maxCnt = Math.max(...points.map(p => p.cnt), 1);

        // Build density canvas
        const temp = document.createElement('canvas');
        temp.width  = w;
        temp.height = h;
        const tc = temp.getContext('2d');
        tc.globalCompositeOperation = 'lighter';

        points.forEach(({ x_pos, y_pos, cnt }) => {
            const px       = (x_pos / maxX) * w;
            const py       = (y_pos / maxY) * h;
            const intensity = cnt / maxCnt;
            const radius   = 24 + intensity * 32;

            const grad = tc.createRadialGradient(px, py, 0, px, py, radius);
            grad.addColorStop(0,   `rgba(255,255,255,${0.3 + intensity * 0.5})`);
            grad.addColorStop(0.4, `rgba(255,255,255,${0.1 + intensity * 0.2})`);
            grad.addColorStop(1,   'rgba(255,255,255,0)');

            tc.beginPath();
            tc.arc(px, py, radius, 0, Math.PI * 2);
            tc.fillStyle = grad;
            tc.fill();
        });

        // Apply color map on main canvas
        const imgData   = tc.getImageData(0, 0, w, h);
        const outData   = ctx.createImageData(w, h);
        const colorMap  = [
            [0,   0,   0,   0  ],
            [0,   0,   255, 50 ],
            [0,   128, 255, 120],
            [0,   255, 128, 180],
            [128, 255, 0,   220],
            [255, 200, 0,   245],
            [255, 80,  0,   265],
            [255, 0,   0,   280],
        ];

        function mapColor(v) {
            if (v <= 0) return [0, 0, 0, 0];
            const n = v / 255;
            for (let i = 0; i < colorMap.length - 1; i++) {
                const a = colorMap[i], b = colorMap[i + 1];
                const t0 = a[3] / 280, t1 = b[3] / 280;
                if (n >= t0 && n <= t1) {
                    const f = (n - t0) / (t1 - t0);
                    return [
                        Math.round(a[0] + (b[0] - a[0]) * f),
                        Math.round(a[1] + (b[1] - a[1]) * f),
                        Math.round(a[2] + (b[2] - a[2]) * f),
                        Math.round((a[3] + (b[3] - a[3]) * f) / 280 * 220),
                    ];
                }
            }
            return [255, 0, 0, 220];
        }

        for (let i = 0; i < imgData.data.length; i += 4) {
            const v = imgData.data[i];
            const [r, g, b, a] = mapColor(v);
            outData.data[i]     = r;
            outData.data[i + 1] = g;
            outData.data[i + 2] = b;
            outData.data[i + 3] = a;
        }

        ctx.putImageData(outData, 0, 0);
    }

    draw();
    window.addEventListener('resize', draw);
})();
</script>
@endif
@endpush
@endsection
