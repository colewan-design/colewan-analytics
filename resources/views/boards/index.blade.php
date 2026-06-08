@extends('layouts.app')
@section('title', 'Boards — Alyze')

@section('content')
<div class="flex flex-col min-h-screen">

<header class="flex items-center justify-between px-8 py-5 bg-white border-b border-gray-100">
    <div>
        <h1 class="text-lg font-bold text-gray-900">Boards</h1>
        <p class="text-sm text-gray-400 mt-0.5">7-day overview across all your sites</p>
    </div>
    <a href="{{ route('dashboard.index') }}"
       class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 transition-colors">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
        All Sites
    </a>
</header>

<div class="flex-1 p-8">
    @if($sites->isEmpty())
    <div class="flex flex-col items-center justify-center py-24 text-center">
        <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center mb-4">
            <svg class="w-7 h-7 text-orange-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M3 3h18v14H3z"/><path d="M7 17v2M17 17v2"/></svg>
        </div>
        <p class="text-gray-500 font-medium">No sites tracked yet.</p>
        <a href="{{ route('dashboard.index') }}" class="mt-3 text-sm text-orange-500 hover:underline">Add your first site →</a>
    </div>
    @else

    {{-- Summary row --}}
    @php
        $totalViews    = collect($stats)->sum('views');
        $totalVisitors = collect($stats)->sum('visitors');
        $totalClicks   = collect($stats)->sum('clicks');
    @endphp
    <div class="grid grid-cols-3 gap-4 mb-8">
        @foreach([['Total Views', number_format($totalViews), 'M3 3h18v14H3z M7 9l3 3 3-3 4 4'], ['Total Visitors', number_format($totalVisitors), 'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2 M9 7a4 4 0 100 8 4 4 0 000-8z'], ['Total Clicks', number_format($totalClicks), 'M15 15l-7-7 M8.5 8.5v7h7']] as [$label, $val, $icon])
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $icon }}"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium">{{ $label }} <span class="font-normal">(7d)</span></p>
                <p class="text-xl font-bold text-gray-900">{{ $val }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Site cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @foreach($sites as $site)
        @php $s = $stats[$site->id]; @endphp
        <a href="{{ route('dashboard.site', $site->tracking_id) }}"
           class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:border-orange-200 hover:shadow-md transition-all block group">

            {{-- Header --}}
            <div class="flex items-start justify-between mb-4">
                <div class="min-w-0">
                    <p class="font-semibold text-gray-900 truncate group-hover:text-orange-600 transition-colors">
                        {{ $site->domain ?: $site->name }}
                    </p>
                    @if($site->domain && $site->domain !== $site->name)
                    <p class="text-xs text-gray-400 truncate mt-0.5">{{ $site->name }}</p>
                    @endif
                </div>
                @if($s['change'] !== null)
                    @php $up = $s['change'] >= 0; @endphp
                    <span class="inline-flex items-center gap-0.5 text-xs font-semibold px-2 py-0.5 rounded-full shrink-0 ml-2 {{ $up ? 'text-green-700 bg-green-100' : 'text-red-600 bg-red-100' }}">
                        {{ $up ? '↗' : '↘' }} {{ abs($s['change']) }}%
                    </span>
                @else
                    <span class="text-xs text-gray-300 shrink-0 ml-2">No prior data</span>
                @endif
            </div>

            {{-- Sparkline --}}
            @php
                $spark = $s['sparkline'];
                $max   = max(array_merge($spark, [1]));
                $pts   = [];
                foreach ($spark as $i => $v) {
                    $x = ($i / (count($spark) - 1)) * 100;
                    $y = 30 - ($v / $max) * 28;
                    $pts[] = "{$x},{$y}";
                }
                $ptStr = implode(' ', $pts);
            @endphp
            <div class="mb-4">
                <svg viewBox="0 0 100 30" class="w-full h-8" preserveAspectRatio="none">
                    <polyline points="{{ $ptStr }}" fill="none" stroke="#f97316" stroke-width="2"
                              stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke"/>
                </svg>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-3 gap-2 pt-3 border-t border-gray-50">
                <div class="text-center">
                    <p class="text-base font-bold text-gray-900">{{ number_format($s['views']) }}</p>
                    <p class="text-xs text-gray-400">Views</p>
                </div>
                <div class="text-center">
                    <p class="text-base font-bold text-gray-900">{{ number_format($s['visitors']) }}</p>
                    <p class="text-xs text-gray-400">Visitors</p>
                </div>
                <div class="text-center">
                    <p class="text-base font-bold text-gray-900">{{ number_format($s['clicks']) }}</p>
                    <p class="text-xs text-gray-400">Clicks</p>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @endif
</div>
</div>
@endsection
