@extends('layouts.app')
@section('title', 'Dashboard — Analytics')

@section('content')
<div class="flex items-center justify-between mb-8">
    <h1 class="text-2xl font-bold">Sites</h1>
    <button onclick="document.getElementById('modal').classList.remove('hidden')"
        class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
        + Add Site
    </button>
</div>

@if($sites->isEmpty())
    <div class="text-center py-20 text-gray-500">
        <p class="text-lg">No sites yet.</p>
        <p class="text-sm mt-1">Add your first site to start tracking.</p>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($sites as $site)
        <a href="{{ route('dashboard.site', $site->tracking_id) }}"
            class="bg-gray-900 border border-gray-800 rounded-xl p-5 hover:border-indigo-600 transition group">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="font-semibold text-lg group-hover:text-indigo-400 transition">{{ $site->name }}</h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $site->domain ?: 'No domain set' }}</p>
                </div>
                <span class="text-xs bg-gray-800 text-gray-400 px-2 py-1 rounded">
                    {{ $site->created_at->diffForHumans() }}
                </span>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-gray-800 rounded-lg p-3">
                    <p class="text-xs text-gray-400">Page Views</p>
                    <p class="text-xl font-bold text-white">{{ number_format($site->pageviews_count) }}</p>
                </div>
                <div class="bg-gray-800 rounded-lg p-3">
                    <p class="text-xs text-gray-400">Clicks</p>
                    <p class="text-xl font-bold text-white">{{ number_format($site->clicks_count) }}</p>
                </div>
            </div>
            <p class="text-xs text-gray-600 mt-3 truncate font-mono">{{ $site->tracking_id }}</p>
        </a>
        @endforeach
    </div>
@endif

{{-- Add Site Modal --}}
<div id="modal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 w-full max-w-md shadow-2xl">
        <h2 class="text-lg font-semibold mb-4">Add New Site</h2>
        <form action="{{ route('sites.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm text-gray-400 mb-1">Site Name</label>
                <input type="text" name="name" placeholder="My Website" required
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Domain (optional)</label>
                <input type="text" name="domain" placeholder="example.com"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2.5 rounded-lg transition">
                    Create Site
                </button>
                <button type="button" onclick="document.getElementById('modal').classList.add('hidden')"
                    class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 font-semibold py-2.5 rounded-lg transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
