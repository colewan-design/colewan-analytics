@extends('layouts.app')
@section('title', $site->name . ' — Workspace — Alyse')

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
                <a href="{{ route('workspace.show', $s->tracking_id) }}"
                    class="block px-4 py-2 text-sm hover:bg-gray-50 {{ $s->id === $site->id ? 'text-orange-600 font-semibold' : 'text-gray-700' }}">
                    {{ $s->domain ?: $s->name }}
                </a>
                @endforeach
            </div>
        </div>
        <span class="text-gray-300">/</span>
        <span class="text-gray-800 font-semibold">Workspace</span>
    </div>
</header>

<div class="flex-1 p-8 space-y-6 max-w-2xl">

    {{-- Site settings form --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <h2 class="font-semibold text-gray-900 mb-5 text-base">Site Settings</h2>

        <form action="{{ route('workspace.update', $site->tracking_id) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-1.5">Site Name</label>
                <input id="name" name="name" type="text" value="{{ old('name', $site->name) }}"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent transition-all"
                    placeholder="My Website">
                @error('name')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="domain" class="block text-sm font-semibold text-gray-700 mb-1.5">Domain <span class="text-gray-400 font-normal">(optional)</span></label>
                <input id="domain" name="domain" type="text" value="{{ old('domain', $site->domain) }}"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent transition-all"
                    placeholder="example.com">
                <p class="mt-1.5 text-xs text-gray-400">Used as the display label in the dashboard.</p>
                @error('domain')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
                Save Changes
            </button>
        </form>
    </div>

    {{-- Tracking ID --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <h2 class="font-semibold text-gray-900 mb-1 text-base">Tracking ID</h2>
        <p class="text-sm text-gray-500 mb-4">Use this ID in your embed script. Treat it like a password — do not expose it publicly beyond embedding.</p>
        <div class="flex items-center gap-3">
            <code id="tid-display" class="flex-1 block font-mono text-sm bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-gray-700 select-all">{{ $site->tracking_id }}</code>
            <button onclick="
                navigator.clipboard.writeText(document.getElementById('tid-display').innerText);
                this.textContent='Copied!';
                setTimeout(()=>this.textContent='Copy',2000)
            " class="shrink-0 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold px-3 py-2 rounded-xl transition-colors">
                Copy
            </button>
        </div>
        <div class="mt-4 flex items-center gap-4 pt-4 border-t border-gray-100">
            <a href="{{ route('analytics.integration', $site->tracking_id) }}"
               class="text-sm text-orange-500 hover:text-orange-600 font-medium transition-colors">
                View integration guide →
            </a>
            <a href="{{ route('dashboard.site', $site->tracking_id) }}"
               class="text-sm text-gray-500 hover:text-gray-700 font-medium transition-colors">
                Open dashboard →
            </a>
        </div>
    </div>

    {{-- Stats overview --}}
    @php
        $totalPv = \App\Models\AnalyticsPageview::where('tracking_id', $site->tracking_id)->count();
        $totalCl = \App\Models\AnalyticsClick::where('tracking_id', $site->tracking_id)->count();
        $totalFb = \App\Models\AnalyticsFeedback::where('tracking_id', $site->tracking_id)->count();
    @endphp
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <h2 class="font-semibold text-gray-900 mb-4 text-base">Data Overview</h2>
        <div class="grid grid-cols-3 gap-4">
            <div class="text-center p-4 bg-gray-50 rounded-xl">
                <p class="text-xl font-bold text-gray-900">{{ number_format($totalPv) }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Page Views</p>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-xl">
                <p class="text-xl font-bold text-gray-900">{{ number_format($totalCl) }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Click Events</p>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-xl">
                <p class="text-xl font-bold text-gray-900">{{ number_format($totalFb) }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Feedback</p>
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-3">All time totals. Created {{ $site->created_at->diffForHumans() }}.</p>
    </div>

    {{-- Danger zone --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-red-100">
        <h2 class="font-semibold text-red-600 mb-1 text-base">Danger Zone</h2>
        <p class="text-sm text-gray-500 mb-4">Deleting this site will permanently remove all page views, click events, and feedback data. This action cannot be undone.</p>
        <form action="{{ route('sites.destroy', $site) }}" method="POST"
              onsubmit="return confirm('Delete {{ addslashes($site->name) }} and ALL its data? This cannot be undone.')">
            @csrf @method('DELETE')
            <button type="submit"
                class="border border-red-200 bg-white hover:bg-red-50 text-red-500 text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
                Delete Site & All Data
            </button>
        </form>
    </div>

</div>
</div>
@endsection
