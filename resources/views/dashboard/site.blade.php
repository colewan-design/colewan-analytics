@extends('layouts.app')
@section('title', $site->name . ' — Alyse')

@php
    $allSites = \App\Models\AnalyticsSite::orderBy('name')->get();

    $secs    = (int) round($avgDuration ?? 0);
    $durFmt  = $secs >= 60 ? floor($secs / 60) . 'm ' . ($secs % 60) . 's' : ($secs > 0 ? $secs . 's' : '—');

    $reachBadge = function(?int $pct, bool $invertBad = false): string {
        if ($pct === null) return '<span class="text-xs text-gray-400">No prior data</span>';
        $up    = $invertBad ? $pct <= 0 : $pct >= 0;
        $arrow = $pct >= 0 ? '↗' : '↘';
        $color = $up ? 'text-green-700 bg-green-100' : 'text-red-600 bg-red-100';
        $label = $pct >= 0 ? 'Reach up' : 'Reach down';
        return '<span class="text-xs text-gray-500 mr-1">' . $label . '</span>'
             . '<span class="inline-flex items-center gap-0.5 text-xs font-semibold px-2 py-0.5 rounded-full ' . $color . '">'
             . $arrow . ' ' . abs($pct) . '%</span>';
    };
@endphp

@section('content')
<div class="flex flex-col min-h-screen">

{{-- ── Top bar ──────────────────────────────────────────────── --}}
<header class="flex items-center justify-between px-8 py-5 bg-white border-b border-gray-100 gap-4 flex-wrap">

    {{-- Breadcrumb + site picker --}}
    <div class="flex items-center gap-2 text-sm font-medium text-gray-500">
        <a href="{{ route('dashboard.index') }}" class="hover:text-gray-800 transition-colors">Dashboard</a>
        <span class="text-gray-300">/</span>

        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open"
                class="flex items-center gap-1.5 border border-gray-200 rounded-full px-3 py-1.5 text-gray-800 font-semibold hover:border-orange-300 hover:bg-orange-50 transition-colors text-sm">
                {{ $site->domain ?: $site->name }}
                <svg class="w-3.5 h-3.5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
                </svg>
            </button>
            <div x-show="open" x-cloak @click.outside="open = false"
                class="absolute left-0 top-full mt-1.5 bg-white border border-gray-100 rounded-xl shadow-lg z-20 min-w-44 py-1">
                @foreach($allSites as $s)
                <a href="{{ route('dashboard.site', $s->tracking_id) }}"
                    class="block px-4 py-2 text-sm hover:bg-gray-50 {{ $s->id === $site->id ? 'text-orange-600 font-semibold' : 'text-gray-700' }}">
                    {{ $s->domain ?: $s->name }}
                </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Controls --}}
    <div class="flex items-center gap-2">
        {{-- Range pills --}}
        <div class="flex items-center bg-gray-100 rounded-xl p-1 gap-0.5">
            @foreach([7 => '7d', 14 => '14d', 30 => '30d', 60 => '60d', 90 => '90d'] as $d => $label)
            <a href="?days={{ $d }}"
                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $range == $d ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>

        {{-- Filter --}}
        <button class="flex items-center gap-1.5 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-600 hover:border-gray-300 transition-colors bg-white">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                <path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/>
            </svg>
            Filter
        </button>

        {{-- Embed --}}
        <button onclick="document.getElementById('embed-modal').classList.remove('hidden')"
            class="border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-600 hover:border-gray-300 transition-colors bg-white font-mono">
            &lt;/&gt;
        </button>

        {{-- Delete --}}
        <form action="{{ route('sites.destroy', $site) }}" method="POST"
              onsubmit="return confirm('Delete this site and all its data?')">
            @csrf @method('DELETE')
            <button class="border border-red-200 rounded-xl px-3 py-2 text-sm text-red-500 hover:bg-red-50 transition-colors bg-white">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/>
                </svg>
            </button>
        </form>
    </div>
</header>

{{-- ── Body ─────────────────────────────────────────────────── --}}
<div class="flex-1 p-8 space-y-6">

    {{-- Live traffic banner --}}
    <div class="flex items-center gap-3">
        <span class="inline-flex items-center gap-2 bg-white border border-gray-100 rounded-full px-4 py-2 text-sm font-medium shadow-sm">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
            </span>
            Live Traffic: <strong class="ml-0.5">{{ $liveVisitors }}</strong>
        </span>
        <span class="inline-flex items-center gap-2 bg-white border border-gray-100 rounded-full px-4 py-2 text-sm font-medium shadow-sm">
            <span class="inline-flex rounded-full h-2 w-2 bg-orange-400"></span>
            Bots Excluded: <strong class="ml-0.5">0</strong>
        </span>
    </div>

    {{-- ── Stat cards ──────────────────────────────────────── --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">

        {{-- Site Traffic --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 bg-orange-100 rounded-full flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-gray-800">Site Traffic</span>
                </div>
                <svg class="w-4 h-4 text-gray-300 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
            </div>
            <div class="flex items-center gap-1 mb-3 text-xs">
                {!! $reachBadge($viewsChange) !!}
            </div>
            <div class="flex items-baseline gap-4">
                <div><span class="text-2xl font-bold text-gray-900">{{ number_format($totalViews) }}</span><span class="text-xs text-gray-400 ml-1">Views</span></div>
                <div><span class="text-2xl font-bold text-gray-900">{{ number_format($uniqueVisitors) }}</span><span class="text-xs text-gray-400 ml-1">Users</span></div>
            </div>
        </div>

        {{-- Activity Duration --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 bg-orange-100 rounded-full flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-gray-800">Avg Duration</span>
                </div>
                <svg class="w-4 h-4 text-gray-300 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
            </div>
            <div class="flex items-center gap-1 mb-3 text-xs">
                {!! $reachBadge($durationChange) !!}
            </div>
            <div class="flex items-baseline gap-4">
                <div><span class="text-2xl font-bold text-gray-900">{{ $durFmt }}</span><span class="text-xs text-gray-400 ml-1">Avg</span></div>
            </div>
        </div>

        {{-- Bounce Rate --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 bg-orange-100 rounded-full flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/>
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-gray-800">Bounce Rate</span>
                </div>
                <svg class="w-4 h-4 text-gray-300 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
            </div>
            <div class="flex items-center gap-1 mb-3 text-xs">
                {{-- Higher bounce = worse, so invert --}}
                {!! $reachBadge($bounceChange, true) !!}
            </div>
            <div class="flex items-baseline gap-4">
                <div><span class="text-2xl font-bold text-gray-900">{{ $bounceRate }}%</span><span class="text-xs text-gray-400 ml-1">Rate</span></div>
                <div><span class="text-2xl font-bold text-gray-900">{{ number_format($totalSessions) }}</span><span class="text-xs text-gray-400 ml-1">Sessions</span></div>
            </div>
        </div>

        {{-- Clicks --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 bg-orange-100 rounded-full flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 3l14 9-14 9V3z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-gray-800">Clicks</span>
                </div>
                <svg class="w-4 h-4 text-gray-300 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
            </div>
            <div class="flex items-center gap-1 mb-3 text-xs">
                {!! $reachBadge($clicksChange) !!}
            </div>
            <div class="flex items-baseline gap-4">
                <div><span class="text-2xl font-bold text-gray-900">{{ number_format($totalClicks) }}</span><span class="text-xs text-gray-400 ml-1">Total</span></div>
            </div>
        </div>
    </div>

    {{-- ── Visits Over Time chart ───────────────────────────── --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-5">
            <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                Visits Over Time
            </h2>
        </div>
        <canvas id="viewsChart" height="90"></canvas>
    </div>

    {{-- ── Bottom grid ─────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Top Pages --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <h2 class="font-semibold text-gray-900 mb-4 text-sm">Top Pages</h2>
            @forelse($topPages as $i => $page)
            <div class="flex items-center justify-between py-2.5 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="text-xs text-gray-300 font-mono w-5 text-right shrink-0">{{ $i + 1 }}</span>
                    <span class="text-sm text-gray-600 truncate" title="{{ $page->url }}">{{ $page->url }}</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 ml-3 shrink-0">{{ number_format($page->views) }}</span>
            </div>
            @empty
            <p class="text-sm text-gray-400 py-4 text-center">No data yet.</p>
            @endforelse
        </div>

        {{-- Top Countries --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <h2 class="font-semibold text-gray-900 mb-4 text-sm">Top Countries</h2>
            @forelse($topCountries as $i => $c)
            <div class="flex items-center justify-between py-2.5 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="text-xs text-gray-300 font-mono w-5 text-right shrink-0">{{ $i + 1 }}</span>
                    @if($c->country_code)
                        <img src="https://flagcdn.com/16x12/{{ strtolower($c->country_code) }}.png"
                             alt="{{ $c->country_code }}" class="rounded-sm shrink-0" width="16" height="12">
                    @endif
                    <span class="text-sm text-gray-600 truncate">{{ $c->country }}</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 ml-3 shrink-0">{{ number_format($c->views) }}</span>
            </div>
            @empty
            <p class="text-sm text-gray-400 py-4 text-center">No data yet.</p>
            @endforelse
        </div>

        {{-- Browsers --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <h2 class="font-semibold text-gray-900 mb-4 text-sm">Browsers</h2>
            <canvas id="browserChart" height="180"></canvas>
        </div>

        {{-- Devices --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <h2 class="font-semibold text-gray-900 mb-4 text-sm">Devices</h2>
            <canvas id="deviceChart" height="180"></canvas>
        </div>

        {{-- Referrers --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <h2 class="font-semibold text-gray-900 mb-4 text-sm">Top Referrers</h2>
            @forelse($referrers as $i => $ref)
            <div class="flex items-center justify-between py-2.5 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="text-xs text-gray-300 font-mono w-5 text-right shrink-0">{{ $i + 1 }}</span>
                    <span class="text-sm text-gray-600 truncate" title="{{ $ref->referrer }}">{{ $ref->referrer }}</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 ml-3 shrink-0">{{ number_format($ref->cnt) }}</span>
            </div>
            @empty
            <p class="text-sm text-gray-400 py-4 text-center">No referrers yet.</p>
            @endforelse
        </div>

        {{-- Top Clicks --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <h2 class="font-semibold text-gray-900 mb-4 text-sm">Most Clicked Elements</h2>
            @forelse($topClicks as $i => $click)
            <div class="flex items-center justify-between py-2.5 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="text-xs text-gray-300 font-mono w-5 text-right shrink-0">{{ $i + 1 }}</span>
                    <span class="text-xs font-mono bg-orange-50 text-orange-600 px-1.5 py-0.5 rounded-md shrink-0">{{ strtolower($click->element_tag ?? '?') }}</span>
                    <span class="text-sm text-gray-600 truncate">{{ Str::limit($click->element_text ?: $click->element_href ?: '(no text)', 40) }}</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 ml-3 shrink-0">{{ number_format($click->cnt) }}</span>
            </div>
            @empty
            <p class="text-sm text-gray-400 py-4 text-center">No clicks tracked yet.</p>
            @endforelse
        </div>
    </div>

    {{-- ── Recent Views table ───────────────────────────────── --}}
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
                    @forelse($recentViews as $view)
                    <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                        <td class="py-3 pr-4 text-gray-400 whitespace-nowrap text-xs">{{ $view->created_at->diffForHumans() }}</td>
                        <td class="py-3 pr-4 max-w-xs truncate text-gray-700 text-xs" title="{{ $view->url }}">{{ $view->url }}</td>
                        <td class="py-3 pr-4 text-gray-500 text-xs">{{ $view->country ?: '—' }}</td>
                        <td class="py-3 pr-4 text-gray-500 text-xs">{{ $view->browser ?: '—' }}</td>
                        <td class="py-3 text-gray-500 text-xs">{{ $view->device ?: '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-8 text-gray-400 text-center text-xs">No views yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>{{-- /body --}}
</div>{{-- /flex col --}}

{{-- ── Embed Modal ──────────────────────────────────────────── --}}
<div id="embed-modal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50"
     onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="flex items-center justify-center min-h-full p-4">
    <div class="bg-white rounded-2xl p-6 w-full max-w-2xl shadow-2xl border border-gray-100">
        <h2 class="text-lg font-bold mb-1">Embed Tracking Script</h2>
        <p class="text-sm text-gray-500 mb-4">Paste this snippet into the <code class="text-orange-500 bg-orange-50 px-1 py-0.5 rounded text-xs">&lt;head&gt;</code> of every page you want to track.</p>
        <pre class="bg-gray-950 rounded-xl p-4 text-xs text-emerald-400 overflow-x-auto whitespace-pre-wrap font-mono leading-relaxed"><code>&lt;!-- Alyse Analytics --&gt;
&lt;script&gt;
window._analyticsId = '{{ $site->tracking_id }}';
window._analyticsUrl = '{{ url('/') }}';
&lt;/script&gt;
&lt;script src="{{ url('/tracker.js') }}" defer&gt;&lt;/script&gt;</code></pre>
        <div class="flex gap-3 mt-4">
            <button onclick="
                navigator.clipboard.writeText(document.querySelector('#embed-modal pre code').innerText);
                this.textContent='Copied!';
                setTimeout(()=>this.textContent='Copy to Clipboard',2000)
            " class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
                Copy to Clipboard
            </button>
            <button onclick="document.getElementById('embed-modal').classList.add('hidden')"
                class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
                Close
            </button>
        </div>
    </div>
    </div>
</div>

@push('scripts')
{{-- Data island: PHP encodes here so the JS block below stays pure JavaScript --}}
<script type="application/json" id="Alyse-data">{!! json_encode([
    'labels'        => $chartData['labels'],
    'views'         => $chartData['data'],
    'browserLabels' => $browsers->pluck('browser'),
    'browserData'   => $browsers->pluck('cnt'),
    'deviceLabels'  => $devices->pluck('device'),
    'deviceData'    => $devices->pluck('cnt'),
]) !!}</script>

<script>
(function () {
    const d = JSON.parse(document.getElementById('Alyse-data').textContent);
    const labels = d.labels;
    const data   = d.views;

    const lastNonZero = data.reduce((acc, v, i) => v > 0 ? i : acc, data.length - 1);
    const bgColors    = data.map((_, i) => i === lastNonZero ? '#f97316' : 'rgba(209, 196, 182, 0.45)');
    const hoverColors = data.map((_, i) => i === lastNonZero ? '#ea580c' : 'rgba(209, 196, 182, 0.7)');

    new Chart(document.getElementById('viewsChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Page Views',
                data,
                backgroundColor: bgColors,
                hoverBackgroundColor: hoverColors,
                borderRadius: { topLeft: 8, topRight: 8 },
                borderSkipped: false,
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#fff',
                    titleColor: '#111827',
                    bodyColor: '#6b7280',
                    borderColor: '#e5e7eb',
                    borderWidth: 1,
                    cornerRadius: 10,
                    padding: 12,
                    displayColors: false,
                    callbacks: { label: ctx => ctx.parsed.y + ' Visits' }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { color: '#9ca3af', font: { size: 11 }, maxTicksLimit: 12 }
                },
                y: {
                    grid: { color: '#f9fafb', lineWidth: 1 },
                    border: { display: false, dash: [4, 4] },
                    ticks: { color: '#9ca3af', font: { size: 11 } },
                    beginAtZero: true,
                }
            }
        }
    });

    const donut = (id, lbls, vals) => new Chart(document.getElementById(id), {
        type: 'doughnut',
        data: {
            labels: lbls,
            datasets: [{
                data: vals,
                backgroundColor: ['#f97316','#fb923c','#fdba74','#fcd34d','#86efac','#67e8f9','#a78bfa','#94a3b8'],
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            plugins: {
                legend: {
                    position: 'right',
                    labels: { color: '#6b7280', font: { size: 11 }, padding: 12, boxWidth: 10, usePointStyle: true }
                }
            },
            cutout: '68%',
        }
    });

    donut('browserChart', d.browserLabels, d.browserData);
    donut('deviceChart',  d.deviceLabels,  d.deviceData);
})();
</script>
@endpush
@endsection
