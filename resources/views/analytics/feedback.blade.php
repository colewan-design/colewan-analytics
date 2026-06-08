@extends('layouts.app')
@section('title', $site->name . ' — Feedback — Alyze')

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
                <a href="{{ route('analytics.feedback', $s->tracking_id) }}"
                    class="block px-4 py-2 text-sm hover:bg-gray-50 {{ $s->id === $site->id ? 'text-orange-600 font-semibold' : 'text-gray-700' }}">
                    {{ $s->domain ?: $s->name }}
                </a>
                @endforeach
            </div>
        </div>
        <span class="text-gray-300">/</span>
        <span class="text-gray-800 font-semibold">Feedback</span>
    </div>

    <div class="flex items-center bg-gray-100 rounded-xl p-1 gap-0.5">
        @foreach([7 => '7d', 14 => '14d', 30 => '30d', 60 => '60d', 90 => '90d'] as $d => $label)
        <a href="?days={{ $d }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $range == $d ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">{{ $label }}</a>
        @endforeach
    </div>
</header>

<div class="flex-1 p-8 space-y-6">

    {{-- Stats --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        @php
            $total    = (int) ($stats->total    ?? 0);
            $positive = (int) ($stats->positive ?? 0);
            $neutral  = (int) ($stats->neutral  ?? 0);
            $negative = (int) ($stats->negative ?? 0);
            $avgRating = round($stats->avg_rating ?? 0, 1);
            $posRate = $total > 0 ? round($positive / $total * 100) : 0;
        @endphp

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <p class="text-xs font-semibold text-gray-400 mb-1">Total Responses</p>
            <p class="text-3xl font-bold text-gray-900">{{ number_format($total) }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <p class="text-xs font-semibold text-gray-400 mb-1">Satisfaction Rate</p>
            <p class="text-3xl font-bold {{ $posRate >= 70 ? 'text-green-600' : ($posRate >= 40 ? 'text-yellow-600' : 'text-red-500') }}">{{ $posRate }}%</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center gap-3">
                <div>
                    <p class="text-xs font-semibold text-gray-400 mb-2">Rating Breakdown</p>
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2 text-xs">
                            <span class="text-base">😊</span>
                            <div class="flex-1 h-1.5 bg-gray-100 rounded-full"><div class="h-full bg-green-400 rounded-full" style="width: {{ $total > 0 ? round($positive/$total*100) : 0 }}%"></div></div>
                            <span class="text-gray-500 w-6 text-right">{{ $positive }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <span class="text-base">😐</span>
                            <div class="flex-1 h-1.5 bg-gray-100 rounded-full"><div class="h-full bg-yellow-400 rounded-full" style="width: {{ $total > 0 ? round($neutral/$total*100) : 0 }}%"></div></div>
                            <span class="text-gray-500 w-6 text-right">{{ $neutral }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <span class="text-base">😞</span>
                            <div class="flex-1 h-1.5 bg-gray-100 rounded-full"><div class="h-full bg-red-400 rounded-full" style="width: {{ $total > 0 ? round($negative/$total*100) : 0 }}%"></div></div>
                            <span class="text-gray-500 w-6 text-right">{{ $negative }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <p class="text-xs font-semibold text-gray-400 mb-1">Avg Score</p>
            <p class="text-3xl font-bold text-gray-900">{{ $avgRating }}<span class="text-sm text-gray-400 font-normal"> / 3</span></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Feedback by page --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <h2 class="font-semibold text-gray-900 mb-4 text-sm">Feedback by Page</h2>
            @forelse($byPage as $i => $row)
            @php
                $avg = round($row->avg, 1);
                $emoji = $avg >= 2.5 ? '😊' : ($avg >= 1.5 ? '😐' : '😞');
            @endphp
            <div class="flex items-center justify-between py-2.5 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="text-xs text-gray-300 font-mono w-5 text-right shrink-0">{{ $i + 1 }}</span>
                    <span class="text-sm text-gray-600 truncate" title="{{ $row->page_url }}">{{ Str::limit(parse_url($row->page_url, PHP_URL_PATH) ?: '/', 45) }}</span>
                </div>
                <div class="flex items-center gap-2 ml-3 shrink-0">
                    <span class="text-xs text-gray-400">{{ $row->cnt }}</span>
                    <span class="text-base">{{ $emoji }}</span>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-400 py-4 text-center">No feedback yet.</p>
            @endforelse
        </div>

        {{-- How to collect feedback --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <h2 class="font-semibold text-gray-900 mb-3 text-sm">Collect Feedback on Your Site</h2>
            <p class="text-xs text-gray-500 mb-4">Add this snippet after your tracking script to show a satisfaction widget on your pages.</p>
            <pre class="bg-gray-950 rounded-xl p-4 text-xs text-emerald-400 overflow-x-auto whitespace-pre-wrap font-mono leading-relaxed"><code>&lt;script src="{{ url('/feedback.js') }}" defer&gt;&lt;/script&gt;</code></pre>
            <button onclick="
                navigator.clipboard.writeText(document.querySelector('.feedback-snippet').innerText);
                this.textContent='Copied!';
                setTimeout(()=>this.textContent='Copy',2000)
            " class="mt-3 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
                Copy
            </button>
            <div class="mt-4 p-3 bg-orange-50 rounded-xl text-xs text-orange-700">
                <strong>Widget preview:</strong> Shows a floating 😊 😐 😞 prompt — visitors can rate your page in one click.
            </div>
        </div>
    </div>

    {{-- Recent feedback --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <h2 class="font-semibold text-gray-900 mb-4 text-sm">Recent Feedback</h2>
        @if($recentFeedback->isEmpty())
        <p class="text-sm text-gray-400 py-8 text-center">No feedback received yet in this period.</p>
        @else
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
                    @foreach($recentFeedback as $fb)
                    <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                        <td class="py-3 pr-4 text-gray-400 whitespace-nowrap text-xs">{{ $fb->created_at->diffForHumans() }}</td>
                        <td class="py-3 pr-4 text-xl">
                            {{ $fb->rating === 3 ? '😊' : ($fb->rating === 2 ? '😐' : '😞') }}
                        </td>
                        <td class="py-3 pr-4 max-w-xs truncate text-gray-600 text-xs" title="{{ $fb->page_url }}">{{ Str::limit(parse_url($fb->page_url, PHP_URL_PATH) ?: '/', 50) }}</td>
                        <td class="py-3 text-gray-500 text-xs">{{ $fb->comment ?: '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $recentFeedback->appends(request()->query())->links() }}</div>
        @endif
    </div>

</div>
</div>
@endsection
