@extends('layouts.app')
@section('title', $site->name . ' — Integration — Alyse')

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
                <a href="{{ route('analytics.integration', $s->tracking_id) }}"
                    class="block px-4 py-2 text-sm hover:bg-gray-50 {{ $s->id === $site->id ? 'text-orange-600 font-semibold' : 'text-gray-700' }}">
                    {{ $s->domain ?: $s->name }}
                </a>
                @endforeach
            </div>
        </div>
        <span class="text-gray-300">/</span>
        <span class="text-gray-800 font-semibold">Integration</span>
    </div>
</header>

<div class="flex-1 p-8 space-y-6 max-w-4xl">

    {{-- Tracking ID info --}}
    <div class="bg-orange-50 border border-orange-200 rounded-2xl p-5">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-orange-800">Your Tracking ID</p>
                <p class="text-xs text-orange-600 mt-0.5">Keep this private — it identifies your site in all tracking calls.</p>
            </div>
        </div>
        <code class="block mt-2 font-mono text-sm bg-white border border-orange-200 rounded-xl px-4 py-3 text-orange-700 select-all">{{ $site->tracking_id }}</code>
    </div>

    {{-- Basic analytics script --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <h2 class="font-semibold text-gray-900 mb-1 text-base">1. Analytics Tracker</h2>
        <p class="text-sm text-gray-500 mb-4">Paste this snippet into the <code class="text-orange-500 bg-orange-50 px-1 py-0.5 rounded text-xs">&lt;head&gt;</code> of every page you want to track. It records page views, click events, and session durations.</p>

        @php $trackerSnippet = <<<EOT
<!-- Alyse Analytics -->
<script>
window._analyticsId = '{$site->tracking_id}';
window._analyticsUrl = '{{ url('/') }}';
</script>
<script src="{{ url('/tracker.js') }}" defer></script>
EOT; @endphp

        <pre id="tracker-snippet" class="bg-gray-950 rounded-xl p-4 text-xs text-emerald-400 overflow-x-auto whitespace-pre-wrap font-mono leading-relaxed"><code>&lt;!-- Alyse Analytics --&gt;
&lt;script&gt;
window._analyticsId = '{{ $site->tracking_id }}';
window._analyticsUrl = '{{ url('/') }}';
&lt;/script&gt;
&lt;script src="{{ url('/tracker.js') }}" defer&gt;&lt;/script&gt;</code></pre>
        <button onclick="copy('tracker-snippet', this)" class="mt-3 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
            Copy to Clipboard
        </button>
    </div>

    {{-- Feedback widget --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <h2 class="font-semibold text-gray-900 mb-1 text-base">2. Feedback Widget <span class="text-xs font-normal text-gray-400 ml-1">Optional</span></h2>
        <p class="text-sm text-gray-500 mb-4">Add a floating satisfaction widget that lets visitors rate your pages with a single click. Add this <strong>after</strong> the tracker script.</p>

        <pre id="feedback-snippet" class="bg-gray-950 rounded-xl p-4 text-xs text-emerald-400 overflow-x-auto whitespace-pre-wrap font-mono leading-relaxed"><code>&lt;!-- Alyse Feedback Widget --&gt;
&lt;script src="{{ url('/feedback.js') }}" defer&gt;&lt;/script&gt;</code></pre>
        <button onclick="copy('feedback-snippet', this)" class="mt-3 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
            Copy to Clipboard
        </button>
        <div class="mt-4 p-3 bg-gray-50 rounded-xl text-xs text-gray-500 flex items-start gap-2">
            <span class="text-lg shrink-0">💬</span>
            <span>The widget shows a small prompt at the bottom-right corner: <strong>Was this page helpful?</strong> with 😊 😐 😞 buttons. Responses appear in the <strong>Feedback</strong> tab.</span>
        </div>
    </div>

    {{-- SPA usage --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <h2 class="font-semibold text-gray-900 mb-1 text-base">3. Single-Page Apps (SPA)</h2>
        <p class="text-sm text-gray-500 mb-4">The tracker automatically detects <code class="text-xs bg-gray-100 px-1 py-0.5 rounded">history.pushState</code> navigation. No extra setup needed for React, Vue, Next.js, etc.</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            @foreach(['React', 'Vue', 'Next.js', 'Nuxt', 'Svelte', 'Angular'] as $fw)
            <div class="flex items-center gap-2 px-3 py-2 bg-gray-50 rounded-xl text-xs text-gray-600 font-medium">
                <svg class="w-3 h-3 text-green-500 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                {{ $fw }}
            </div>
            @endforeach
        </div>
    </div>

    {{-- API reference --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <h2 class="font-semibold text-gray-900 mb-4 text-base">4. API Reference</h2>
        <p class="text-sm text-gray-500 mb-5">All endpoints accept <code class="text-xs bg-gray-100 px-1 py-0.5 rounded">application/json</code> and require CORS headers (pre-configured). Base URL: <code class="text-xs bg-gray-100 px-1 py-0.5 rounded font-mono">{{ url('/api') }}</code></p>

        <div class="space-y-4">
            @foreach([
                ['POST', '/track/pageview', 'Record a page view', 'tracking_id, session_id, url, title?, referrer?, screen_w?, screen_h?, language?'],
                ['POST', '/track/click',    'Record a click event', 'tracking_id, session_id, page_url, element_tag?, element_text?, element_href?, x?, y?'],
                ['POST', '/track/duration', 'Update time-on-page', 'view_id, duration (seconds)'],
                ['POST', '/track/feedback', 'Submit page feedback', 'tracking_id, session_id?, page_url, rating (1–3), comment?'],
            ] as [$method, $path, $desc, $params])
            <div class="border border-gray-100 rounded-xl overflow-hidden">
                <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 border-b border-gray-100">
                    <span class="text-xs font-bold bg-green-100 text-green-700 px-2 py-0.5 rounded-md">{{ $method }}</span>
                    <code class="text-xs font-mono text-gray-800">{{ $path }}</code>
                    <span class="text-xs text-gray-500">— {{ $desc }}</span>
                </div>
                <div class="px-4 py-3">
                    <p class="text-xs text-gray-500"><span class="font-semibold text-gray-700">Fields:</span> {{ $params }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>
</div>

@push('scripts')
<script>
function copy(id, btn) {
    const el = document.getElementById(id);
    navigator.clipboard.writeText(el.querySelector('code').innerText);
    btn.textContent = 'Copied!';
    setTimeout(() => btn.textContent = 'Copy to Clipboard', 2000);
}
</script>
@endpush
@endsection
