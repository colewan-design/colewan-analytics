@extends('layouts.app')
@section('title', $site->name . ' — Events Click — Alyze')

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
                <a href="{{ route('analytics.events', $s->tracking_id) }}"
                    class="block px-4 py-2 text-sm hover:bg-gray-50 {{ $s->id === $site->id ? 'text-orange-600 font-semibold' : 'text-gray-700' }}">
                    {{ $s->domain ?: $s->name }}
                </a>
                @endforeach
            </div>
        </div>
        <span class="text-gray-300">/</span>
        <span class="text-gray-800 font-semibold">Events Click</span>
    </div>

    <div class="flex items-center gap-3">
        {{-- Tag filter --}}
        @if($tags->isNotEmpty())
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open"
                class="flex items-center gap-1.5 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-600 hover:border-gray-300 bg-white transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></svg>
                {{ request('tag') ? 'Tag: '.request('tag') : 'Filter by tag' }}
            </button>
            <div x-show="open" x-cloak @click.outside="open = false"
                class="absolute right-0 top-full mt-1.5 bg-white border border-gray-100 rounded-xl shadow-lg z-20 min-w-36 py-1">
                <a href="?days={{ $range }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ !request('tag') ? 'text-orange-600 font-semibold' : 'text-gray-700' }}">All tags</a>
                @foreach($tags as $tag)
                <a href="?days={{ $range }}&tag={{ urlencode($tag) }}"
                    class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request('tag') === $tag ? 'text-orange-600 font-semibold' : 'text-gray-700' }}">
                    {{ $tag ?: 'unknown' }}
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <div class="flex items-center bg-gray-100 rounded-xl p-1 gap-0.5">
            @foreach([7 => '7d', 14 => '14d', 30 => '30d', 60 => '60d', 90 => '90d'] as $d => $label)
            <a href="?days={{ $d }}{{ request('tag') ? '&tag='.urlencode(request('tag')) : '' }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $range == $d ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>
</header>

<div class="flex-1 p-8 space-y-6">

    {{-- Summary by tag --}}
    @if($summary->isNotEmpty())
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <h2 class="font-semibold text-gray-900 mb-4 text-sm">Clicks by Element Type</h2>
        <div class="flex flex-wrap gap-3">
            @foreach($summary as $row)
            @php $total = $summary->sum('cnt'); $pct = $total > 0 ? round($row->cnt / $total * 100) : 0; @endphp
            <a href="?days={{ $range }}&tag={{ urlencode($row->element_tag ?? '') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-xl border {{ request('tag') === $row->element_tag ? 'border-orange-300 bg-orange-50' : 'border-gray-200 bg-white hover:border-gray-300' }} transition-colors">
                <span class="text-xs font-mono bg-orange-50 text-orange-600 px-1.5 py-0.5 rounded-md">{{ $row->element_tag ?: '?' }}</span>
                <span class="text-sm font-semibold text-gray-800">{{ number_format($row->cnt) }}</span>
                <span class="text-xs text-gray-400">{{ $pct }}%</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Events table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
            <p class="text-sm text-gray-500">
                <span class="font-semibold text-gray-900">{{ $events->total() }}</span> click events
            </p>
        </div>

        @if($events->isEmpty())
        <div class="py-16 text-center">
            <p class="text-sm text-gray-400">No click events in this period.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead>
                    <tr class="text-xs font-semibold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                        <th class="text-left px-5 py-3 font-medium">Time</th>
                        <th class="text-left px-4 py-3 font-medium">Tag</th>
                        <th class="text-left px-4 py-3 font-medium">Text / Href</th>
                        <th class="text-left px-4 py-3 font-medium">Page</th>
                        <th class="text-left px-4 py-3 font-medium">Position</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($events as $event)
                    <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                        <td class="py-3 px-5 text-xs text-gray-400 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($event->created_at)->diffForHumans() }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="text-xs font-mono bg-orange-50 text-orange-600 px-1.5 py-0.5 rounded-md">{{ strtolower($event->element_tag ?? '?') }}</span>
                        </td>
                        <td class="py-3 px-4 max-w-xs">
                            @if($event->element_text)
                            <p class="text-xs text-gray-700 truncate">{{ Str::limit($event->element_text, 60) }}</p>
                            @endif
                            @if($event->element_href)
                            <p class="text-xs text-blue-500 truncate mt-0.5" title="{{ $event->element_href }}">{{ Str::limit($event->element_href, 60) }}</p>
                            @endif
                            @if(!$event->element_text && !$event->element_href)
                            <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 max-w-xs">
                            <p class="text-xs text-gray-500 truncate" title="{{ $event->page_url }}">{{ Str::limit(parse_url($event->page_url, PHP_URL_PATH) ?: '/', 40) }}</p>
                        </td>
                        <td class="py-3 px-4 text-xs text-gray-400 font-mono">
                            @if($event->x_pos !== null)
                            {{ $event->x_pos }}, {{ $event->y_pos }}
                            @else
                            —
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-gray-50">{{ $events->links() }}</div>
        @endif
    </div>

</div>
</div>
@endsection
