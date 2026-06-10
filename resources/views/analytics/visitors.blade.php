@extends('layouts.app')
@section('title', $site->name . ' — Visitors — Alyse')

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
                <a href="{{ route('analytics.visitors', $s->tracking_id) }}"
                    class="block px-4 py-2 text-sm hover:bg-gray-50 {{ $s->id === $site->id ? 'text-orange-600 font-semibold' : 'text-gray-700' }}">
                    {{ $s->domain ?: $s->name }}
                </a>
                @endforeach
            </div>
        </div>
        <span class="text-gray-300">/</span>
        <span class="text-gray-800 font-semibold">Visitors</span>
    </div>

    <div class="flex items-center bg-gray-100 rounded-xl p-1 gap-0.5">
        @foreach([7 => '7d', 14 => '14d', 30 => '30d', 60 => '60d', 90 => '90d'] as $d => $label)
        <a href="?days={{ $d }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $range == $d ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">{{ $label }}</a>
        @endforeach
    </div>
</header>

<div class="flex-1 p-8">

    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-500">
            <span class="font-semibold text-gray-900">{{ $sessions->total() }}</span> unique sessions in the last {{ $range }}d
        </p>
    </div>

    @if($sessions->isEmpty())
    <div class="flex flex-col items-center justify-center py-24 text-center bg-white rounded-2xl border border-gray-100">
        <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center mb-4">
            <svg class="w-7 h-7 text-orange-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <p class="text-gray-500 font-medium">No visitors yet.</p>
    </div>
    @else

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[700px]">
                <thead>
                    <tr class="text-xs font-semibold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                        <th class="text-left px-5 py-3 font-medium">Session</th>
                        <th class="text-left px-4 py-3 font-medium">Country</th>
                        <th class="text-left px-4 py-3 font-medium">Browser</th>
                        <th class="text-left px-4 py-3 font-medium">Device</th>
                        <th class="text-left px-4 py-3 font-medium">OS</th>
                        <th class="text-left px-4 py-3 font-medium">Pages</th>
                        <th class="text-left px-4 py-3 font-medium">Duration</th>
                        <th class="text-left px-4 py-3 font-medium">Screen</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessions as $session)
                    @php
                        $secs = (int) $session->total_duration;
                        $dur  = $secs >= 60 ? floor($secs/60).'m '.($secs%60).'s' : ($secs > 0 ? $secs.'s' : '—');
                    @endphp
                    <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                        <td class="py-3 px-5">
                            <p class="font-mono text-xs text-gray-500">{{ substr($session->session_id, 0, 12) }}…</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ \Carbon\Carbon::parse($session->first_seen)->diffForHumans() }}</p>
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-1.5">
                                @if($session->country_code)
                                <img src="https://flagcdn.com/16x12/{{ strtolower($session->country_code) }}.png" alt="" class="rounded-sm shrink-0" width="14" height="10">
                                @endif
                                <span class="text-xs text-gray-600">{{ $session->country ?: '—' }}</span>
                            </div>
                            @if($session->city)
                            <p class="text-xs text-gray-400 mt-0.5">{{ $session->city }}</p>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-xs text-gray-600">{{ $session->browser ?: '—' }}</td>
                        <td class="py-3 px-4">
                            @php
                                $deviceIcons = ['Mobile' => '📱', 'Tablet' => '💻', 'Desktop' => '🖥️'];
                                $icon = $deviceIcons[$session->device] ?? '🖥️';
                            @endphp
                            <span class="text-xs text-gray-600">{{ $icon }} {{ $session->device ?: '—' }}</span>
                        </td>
                        <td class="py-3 px-4 text-xs text-gray-600">{{ $session->os ?: '—' }}</td>
                        <td class="py-3 px-4 text-xs font-semibold text-gray-800">{{ $session->page_count }}</td>
                        <td class="py-3 px-4 text-xs text-gray-600">{{ $dur }}</td>
                        <td class="py-3 px-4 text-xs text-gray-400 font-mono">
                            @if($session->screen_width)
                            {{ $session->screen_width }}×{{ $session->screen_height }}
                            @else
                            —
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">{{ $sessions->appends(request()->query())->links() }}</div>

    @endif
</div>
</div>
@endsection
