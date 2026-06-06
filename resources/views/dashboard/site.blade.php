@extends('layouts.app')
@section('title', $site->name . ' — Analytics')

@section('content')
{{-- Header --}}
<div class="flex flex-wrap items-start justify-between gap-4 mb-6">
    <div>
        <a href="{{ route('dashboard.index') }}" class="text-sm text-gray-500 hover:text-gray-300">&larr; All Sites</a>
        <h1 class="text-2xl font-bold mt-1">{{ $site->name }}</h1>
        @if($site->domain)
            <p class="text-sm text-gray-500">{{ $site->domain }}</p>
        @endif
    </div>
    <div class="flex items-center gap-3">
        <select onchange="location='?days='+this.value"
            class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-300 focus:outline-none">
            @foreach([7,14,30,60,90] as $d)
                <option value="{{ $d }}" {{ $range == $d ? 'selected' : '' }}>Last {{ $d }} days</option>
            @endforeach
        </select>
        <button onclick="document.getElementById('embed-modal').classList.remove('hidden')"
            class="bg-gray-800 border border-gray-700 hover:border-indigo-600 text-sm px-4 py-2 rounded-lg transition">
            &lt;/&gt; Embed
        </button>
        <form action="{{ route('sites.destroy', $site) }}" method="POST" onsubmit="return confirm('Delete this site and all its data?')">
            @csrf @method('DELETE')
            <button class="bg-red-900/40 border border-red-800 hover:bg-red-800 text-red-300 text-sm px-4 py-2 rounded-lg transition">
                Delete
            </button>
        </form>
    </div>
</div>

{{-- Stat Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <p class="text-sm text-gray-400">Page Views</p>
        <p class="text-3xl font-bold mt-1">{{ number_format($totalViews) }}</p>
    </div>
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <p class="text-sm text-gray-400">Unique Visitors</p>
        <p class="text-3xl font-bold mt-1">{{ number_format($uniqueVisitors) }}</p>
    </div>
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <p class="text-sm text-gray-400">Clicks Tracked</p>
        <p class="text-3xl font-bold mt-1">{{ number_format($totalClicks) }}</p>
    </div>
</div>

{{-- Views Over Time Chart --}}
<div class="bg-gray-900 border border-gray-800 rounded-xl p-5 mb-6">
    <h2 class="font-semibold text-gray-300 mb-4">Views Over Time</h2>
    <canvas id="viewsChart" height="100"></canvas>
</div>

{{-- Bottom grid --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    {{-- Top Pages --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <h2 class="font-semibold text-gray-300 mb-4">Top Pages</h2>
        @forelse($topPages as $page)
            <div class="flex items-center justify-between py-2 border-b border-gray-800 last:border-0">
                <span class="text-sm text-gray-400 truncate max-w-xs" title="{{ $page->url }}">{{ $page->url }}</span>
                <span class="text-sm font-semibold ml-4 shrink-0">{{ number_format($page->views) }}</span>
            </div>
        @empty
            <p class="text-gray-600 text-sm">No data yet.</p>
        @endforelse
    </div>

    {{-- Top Countries --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <h2 class="font-semibold text-gray-300 mb-4">Top Countries</h2>
        @forelse($topCountries as $c)
            <div class="flex items-center justify-between py-2 border-b border-gray-800 last:border-0">
                <div class="flex items-center gap-2">
                    @if($c->country_code)
                        <img src="https://flagcdn.com/16x12/{{ strtolower($c->country_code) }}.png"
                             alt="{{ $c->country_code }}" class="rounded-sm" width="16" height="12">
                    @endif
                    <span class="text-sm text-gray-400">{{ $c->country }}</span>
                </div>
                <span class="text-sm font-semibold">{{ number_format($c->views) }}</span>
            </div>
        @empty
            <p class="text-gray-600 text-sm">No data yet.</p>
        @endforelse
    </div>

    {{-- Browsers --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <h2 class="font-semibold text-gray-300 mb-4">Browsers</h2>
        <canvas id="browserChart" height="180"></canvas>
    </div>

    {{-- Devices --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <h2 class="font-semibold text-gray-300 mb-4">Devices</h2>
        <canvas id="deviceChart" height="180"></canvas>
    </div>

    {{-- Top Referrers --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <h2 class="font-semibold text-gray-300 mb-4">Top Referrers</h2>
        @forelse($referrers as $ref)
            <div class="flex items-center justify-between py-2 border-b border-gray-800 last:border-0">
                <span class="text-sm text-gray-400 truncate max-w-xs" title="{{ $ref->referrer }}">{{ $ref->referrer }}</span>
                <span class="text-sm font-semibold ml-4 shrink-0">{{ number_format($ref->cnt) }}</span>
            </div>
        @empty
            <p class="text-gray-600 text-sm">No referrers yet.</p>
        @endforelse
    </div>

    {{-- Top Clicks --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <h2 class="font-semibold text-gray-300 mb-4">Most Clicked Elements</h2>
        @forelse($topClicks as $click)
            <div class="flex items-center justify-between py-2 border-b border-gray-800 last:border-0">
                <div class="min-w-0">
                    <span class="text-xs font-mono bg-gray-800 text-indigo-400 px-1 py-0.5 rounded mr-2">{{ strtolower($click->element_tag ?? '?') }}</span>
                    <span class="text-sm text-gray-400 truncate">{{ Str::limit($click->element_text ?: $click->element_href ?: '(no text)', 50) }}</span>
                </div>
                <span class="text-sm font-semibold ml-4 shrink-0">{{ number_format($click->cnt) }}</span>
            </div>
        @empty
            <p class="text-gray-600 text-sm">No clicks tracked yet.</p>
        @endforelse
    </div>
</div>

{{-- Recent Views --}}
<div class="bg-gray-900 border border-gray-800 rounded-xl p-5 mb-6">
    <h2 class="font-semibold text-gray-300 mb-4">Recent Page Views</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-gray-500 text-xs border-b border-gray-800">
                    <th class="text-left pb-2 font-medium">Time</th>
                    <th class="text-left pb-2 font-medium">URL</th>
                    <th class="text-left pb-2 font-medium">Country</th>
                    <th class="text-left pb-2 font-medium">Browser</th>
                    <th class="text-left pb-2 font-medium">Device</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentViews as $view)
                <tr class="border-b border-gray-800/50 hover:bg-gray-800/30">
                    <td class="py-2 text-gray-500 whitespace-nowrap">{{ $view->created_at->diffForHumans() }}</td>
                    <td class="py-2 max-w-xs truncate text-gray-300" title="{{ $view->url }}">{{ $view->url }}</td>
                    <td class="py-2 text-gray-400">{{ $view->country ?: '—' }}</td>
                    <td class="py-2 text-gray-400">{{ $view->browser ?: '—' }}</td>
                    <td class="py-2 text-gray-400">{{ $view->device ?: '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-4 text-gray-600">No views yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Embed Modal --}}
<div id="embed-modal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 w-full max-w-2xl shadow-2xl">
        <h2 class="text-lg font-semibold mb-2">Embed Tracking Script</h2>
        <p class="text-sm text-gray-400 mb-4">Paste this snippet into the <code class="text-indigo-400">&lt;head&gt;</code> of every page you want to track.</p>
        <pre class="bg-gray-950 border border-gray-700 rounded-lg p-4 text-xs text-green-400 overflow-x-auto whitespace-pre-wrap"><code>&lt;!-- Analytics Tracker --&gt;
&lt;script&gt;
window._analyticsId = '{{ $site->tracking_id }}';
window._analyticsUrl = '{{ url('/') }}';
&lt;/script&gt;
&lt;script src="{{ url('/tracker.js') }}" defer&gt;&lt;/script&gt;</code></pre>
        <button onclick="
            navigator.clipboard.writeText(document.querySelector('#embed-modal pre code').innerText);
            this.textContent='Copied!';
            setTimeout(()=>this.textContent='Copy to Clipboard',2000)
        " class="mt-4 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
            Copy to Clipboard
        </button>
    </div>
</div>

@push('scripts')
<script>
const chartDefaults = {
    plugins: { legend: { labels: { color: '#9ca3af' } } },
};

// Views over time
new Chart(document.getElementById('viewsChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode($chartData['labels']) !!},
        datasets: [{
            label: 'Page Views',
            data: {!! json_encode($chartData['data']) !!},
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99,102,241,0.1)',
            fill: true,
            tension: 0.4,
            pointRadius: 3,
        }]
    },
    options: {
        ...chartDefaults,
        scales: {
            x: { ticks: { color: '#6b7280' }, grid: { color: '#1f2937' } },
            y: { ticks: { color: '#6b7280' }, grid: { color: '#1f2937' }, beginAtZero: true },
        },
    }
});

// Browser doughnut
const browserLabels = {!! json_encode($browsers->pluck('browser')) !!};
const browserData   = {!! json_encode($browsers->pluck('cnt')) !!};
new Chart(document.getElementById('browserChart'), {
    type: 'doughnut',
    data: {
        labels: browserLabels,
        datasets: [{ data: browserData, backgroundColor: ['#6366f1','#8b5cf6','#06b6d4','#10b981','#f59e0b','#ef4444','#ec4899','#64748b'] }]
    },
    options: { ...chartDefaults },
});

// Device doughnut
const deviceLabels = {!! json_encode($devices->pluck('device')) !!};
const deviceData   = {!! json_encode($devices->pluck('cnt')) !!};
new Chart(document.getElementById('deviceChart'), {
    type: 'doughnut',
    data: {
        labels: deviceLabels,
        datasets: [{ data: deviceData, backgroundColor: ['#6366f1','#06b6d4','#10b981','#f59e0b'] }]
    },
    options: { ...chartDefaults },
});
</script>
@endpush
@endsection
