@extends('layouts.app')
@section('title', $site->name . ' — Replay — Alyze')

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
                <a href="{{ route('analytics.replay', $s->tracking_id) }}"
                    class="block px-4 py-2 text-sm hover:bg-gray-50 {{ $s->id === $site->id ? 'text-orange-600 font-semibold' : 'text-gray-700' }}">
                    {{ $s->domain ?: $s->name }}
                </a>
                @endforeach
            </div>
        </div>
        <span class="text-gray-300">/</span>
        <span class="text-gray-800 font-semibold">Replay</span>
    </div>

    <div class="flex items-center bg-gray-100 rounded-xl p-1 gap-0.5">
        @foreach([1 => '1d', 3 => '3d', 7 => '7d', 14 => '14d', 30 => '30d'] as $d => $label)
        <a href="?days={{ $d }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $range == $d ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">{{ $label }}</a>
        @endforeach
    </div>
</header>

<div class="flex-1 p-8">

    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-500">
            <span class="font-semibold text-gray-900">{{ $sessions->total() }}</span> sessions in the last {{ $range }}d
        </p>
        <p class="text-xs text-gray-400">Click a session to expand its page journey</p>
    </div>

    @if($sessions->isEmpty())
    <div class="flex flex-col items-center justify-center py-24 text-center bg-white rounded-2xl border border-gray-100">
        <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center mb-4">
            <svg class="w-7 h-7 text-orange-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M5 3l14 9-14 9V3z"/></svg>
        </div>
        <p class="text-gray-500 font-medium">No sessions recorded yet.</p>
    </div>
    @else

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Table header --}}
        <div class="grid text-xs font-semibold text-gray-400 uppercase tracking-wider px-5 py-3 border-b border-gray-100"
             style="grid-template-columns: 2fr 1fr 1fr 1fr 1fr 1fr 0.5fr">
            <span>Session</span>
            <span>Country</span>
            <span>Browser</span>
            <span>Device</span>
            <span>Pages</span>
            <span>Duration</span>
            <span></span>
        </div>

        @foreach($sessions as $session)
        @php
            $sid   = $session->session_id;
            $pages = $sessionPages[$sid] ?? collect();
            $secs  = (int) $session->total_duration;
            $dur   = $secs >= 60 ? floor($secs/60).'m '.($secs%60).'s' : ($secs > 0 ? $secs.'s' : '—');
        @endphp
        <div x-data="{ open: false }" class="border-b border-gray-50 last:border-b-0">

            {{-- Session row --}}
            <button @click="open = !open"
                class="w-full grid items-center px-5 py-3.5 hover:bg-gray-50/60 transition-colors text-left"
                style="grid-template-columns: 2fr 1fr 1fr 1fr 1fr 1fr 0.5fr">
                <div class="min-w-0">
                    <p class="text-xs font-mono text-gray-500">{{ substr($sid, 0, 12) }}…</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ \Carbon\Carbon::parse($session->first_seen)->diffForHumans() }}</p>
                </div>
                <div class="flex items-center gap-1.5">
                    @if($session->country_code)
                    <img src="https://flagcdn.com/16x12/{{ strtolower($session->country_code) }}.png" alt="" class="rounded-sm shrink-0" width="14" height="10">
                    @endif
                    <span class="text-xs text-gray-600 truncate">{{ $session->country ?: '—' }}</span>
                </div>
                <span class="text-xs text-gray-600 truncate">{{ $session->browser ?: '—' }}</span>
                <span class="text-xs text-gray-600">{{ $session->device ?: '—' }}</span>
                <span class="text-xs font-semibold text-gray-800">{{ $session->page_count }}</span>
                <span class="text-xs text-gray-600">{{ $dur }}</span>
                <span class="text-xs text-gray-400 text-right">
                    <svg class="w-4 h-4 inline transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                </span>
            </button>

            {{-- Expanded: page journey --}}
            <div x-show="open" x-cloak class="px-5 pb-4 bg-gray-50/50">
                <div class="pt-3 pl-4 border-l-2 border-orange-200 space-y-2">
                    @forelse($pages as $i => $page)
                    <div class="flex items-start gap-3">
                        <div class="w-5 h-5 rounded-full bg-orange-100 border-2 border-white flex items-center justify-center shrink-0 mt-0.5">
                            <span class="text-[9px] font-bold text-orange-500">{{ $i + 1 }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs text-gray-700 truncate font-medium" title="{{ $page->url }}">{{ $page->url }}</p>
                            <div class="flex items-center gap-3 mt-0.5 text-xs text-gray-400">
                                <span>{{ \Carbon\Carbon::parse($page->created_at)->format('H:i:s') }}</span>
                                @if($page->duration)
                                <span>{{ $page->duration }}s on page</span>
                                @endif
                                @if($page->title)
                                <span class="truncate italic">{{ $page->title }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-gray-400">No page data.</p>
                    @endforelse
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="mt-5">
        {{ $sessions->appends(request()->query())->links() }}
    </div>

    @endif
</div>
</div>
@endsection
