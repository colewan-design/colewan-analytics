@extends('layouts.app')
@section('title', $site->name . ' — Charts — Alyse')

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
                <a href="{{ route('analytics.charts', $s->tracking_id) }}"
                    class="block px-4 py-2 text-sm hover:bg-gray-50 {{ $s->id === $site->id ? 'text-orange-600 font-semibold' : 'text-gray-700' }}">
                    {{ $s->domain ?: $s->name }}
                </a>
                @endforeach
            </div>
        </div>
        <span class="text-gray-300">/</span>
        <span class="text-gray-800 font-semibold">Charts</span>
    </div>

    <div class="flex items-center bg-gray-100 rounded-xl p-1 gap-0.5">
        @foreach([7 => '7d', 14 => '14d', 30 => '30d', 60 => '60d', 90 => '90d'] as $d => $label)
        <a href="?days={{ $d }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $range == $d ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">{{ $label }}</a>
        @endforeach
    </div>
</header>

<div class="flex-1 p-8 space-y-6">

    {{-- Views & Sessions over time --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <h2 class="font-semibold text-gray-900 mb-5 text-sm flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-orange-400 shrink-0"></span>
            Views & Sessions Over Time
        </h2>
        <canvas id="overTimeChart" height="90"></canvas>
    </div>

    {{-- Hourly + DoW --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <h2 class="font-semibold text-gray-900 mb-5 text-sm flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-orange-400 shrink-0"></span>
                Traffic by Hour of Day
            </h2>
            <canvas id="hourlyChart" height="160"></canvas>
        </div>
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <h2 class="font-semibold text-gray-900 mb-5 text-sm flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-orange-400 shrink-0"></span>
                Traffic by Day of Week
            </h2>
            <canvas id="dowChart" height="160"></canvas>
        </div>
    </div>

    {{-- OS + Languages + Screens --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- OS --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <h2 class="font-semibold text-gray-900 mb-4 text-sm">Operating Systems</h2>
            @forelse($osList as $i => $row)
            @php $total = $osList->sum('cnt'); $pct = $total > 0 ? round($row->cnt / $total * 100) : 0; @endphp
            <div class="mb-3">
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-gray-700 font-medium truncate">{{ $row->os ?: 'Unknown' }}</span>
                    <span class="text-gray-400 shrink-0 ml-2">{{ number_format($row->cnt) }} ({{ $pct }}%)</span>
                </div>
                <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full bg-orange-400 transition-all" style="width: {{ $pct }}%"></div>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-400 py-4 text-center">No data yet.</p>
            @endforelse
        </div>

        {{-- Languages --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <h2 class="font-semibold text-gray-900 mb-4 text-sm">Languages</h2>
            @forelse($languages as $i => $row)
            @php $total = $languages->sum('cnt'); $pct = $total > 0 ? round($row->cnt / $total * 100) : 0; @endphp
            <div class="mb-3">
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-gray-700 font-medium">{{ $row->language ?: 'Unknown' }}</span>
                    <span class="text-gray-400 shrink-0 ml-2">{{ number_format($row->cnt) }} ({{ $pct }}%)</span>
                </div>
                <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full bg-orange-300 transition-all" style="width: {{ $pct }}%"></div>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-400 py-4 text-center">No data yet.</p>
            @endforelse
        </div>

        {{-- Screen Resolutions --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <h2 class="font-semibold text-gray-900 mb-4 text-sm">Screen Resolutions</h2>
            @forelse($screens as $i => $row)
            <div class="flex items-center justify-between py-2.5 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                <span class="text-sm text-gray-700 font-mono text-xs">{{ $row->screen_width }}×{{ $row->screen_height }}</span>
                <span class="text-sm font-semibold text-gray-900 ml-3">{{ number_format($row->cnt) }}</span>
            </div>
            @empty
            <p class="text-sm text-gray-400 py-4 text-center">No data yet.</p>
            @endforelse
        </div>
    </div>

</div>
</div>

@push('scripts')
<script type="application/json" id="charts-data">{!! json_encode([
    'labels'      => $labels,
    'viewData'    => $viewData,
    'sessionData' => $sessionData,
    'hourLabels'  => $hourLabels,
    'hourData'    => $hourData,
    'dowLabels'   => $dowLabels,
    'dowData'     => $dowData,
]) !!}</script>

<script>
(function () {
    const d = JSON.parse(document.getElementById('charts-data').textContent);

    const gridColor  = '#f9fafb';
    const tickColor  = '#9ca3af';
    const axisOpts   = {
        x: { grid: { display: false }, border: { display: false }, ticks: { color: tickColor, font: { size: 11 }, maxTicksLimit: 14 } },
        y: { grid: { color: gridColor }, border: { display: false }, ticks: { color: tickColor, font: { size: 11 } }, beginAtZero: true }
    };
    const tooltip = {
        backgroundColor: '#fff', titleColor: '#111827', bodyColor: '#6b7280',
        borderColor: '#e5e7eb', borderWidth: 1, cornerRadius: 10, padding: 12, displayColors: true
    };

    // Views & Sessions over time (line chart)
    new Chart(document.getElementById('overTimeChart'), {
        type: 'line',
        data: {
            labels: d.labels,
            datasets: [
                {
                    label: 'Page Views',
                    data: d.viewData,
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249,115,22,0.08)',
                    tension: 0.3,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#f97316',
                    borderWidth: 2,
                },
                {
                    label: 'Sessions',
                    data: d.sessionData,
                    borderColor: '#94a3b8',
                    backgroundColor: 'transparent',
                    tension: 0.3,
                    fill: false,
                    pointRadius: 2,
                    pointHoverRadius: 4,
                    pointBackgroundColor: '#94a3b8',
                    borderWidth: 1.5,
                    borderDash: [4, 3],
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { labels: { color: '#6b7280', font: { size: 11 }, padding: 16, usePointStyle: true } }, tooltip },
            scales: axisOpts
        }
    });

    const barOpts = (data, label, color) => ({
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{ label, data: data.data, backgroundColor: color, borderRadius: { topLeft: 5, topRight: 5 }, borderSkipped: false, borderWidth: 0 }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false }, tooltip },
            scales: {
                x: { grid: { display: false }, border: { display: false }, ticks: { color: tickColor, font: { size: 10 } } },
                y: { grid: { color: gridColor }, border: { display: false }, ticks: { color: tickColor, font: { size: 11 } }, beginAtZero: true }
            }
        }
    });

    new Chart(document.getElementById('hourlyChart'), barOpts({ labels: d.hourLabels, data: d.hourData }, 'Views', 'rgba(249,115,22,0.7)'));
    new Chart(document.getElementById('dowChart'),    barOpts({ labels: d.dowLabels,  data: d.dowData  }, 'Views', 'rgba(249,115,22,0.7)'));
})();
</script>
@endpush
@endsection
