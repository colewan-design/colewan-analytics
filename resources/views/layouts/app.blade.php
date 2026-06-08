<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Alyze — Analytics')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                    colors: {
                        brand: { 50:'#fff7ed', 100:'#ffedd5', 200:'#fed7aa', 500:'#f97316', 600:'#ea580c' }
                    }
                }
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; }
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen flex">

{{-- ── Sidebar ──────────────────────────────────────────────── --}}
<aside class="w-60 bg-white border-r border-gray-100 min-h-screen flex flex-col fixed left-0 top-0 z-30">

    {{-- Logo --}}
    <div class="px-5 pt-6 pb-5">
        <a href="{{ route('dashboard.index') }}" class="flex items-center gap-2.5">
            <div class="w-9 h-9 bg-orange-500 rounded-xl flex items-center justify-center shadow-sm shrink-0">
                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                    <rect x="3" y="12" width="4" height="9" rx="1.5"/>
                    <rect x="10" y="7" width="4" height="14" rx="1.5"/>
                    <rect x="17" y="3" width="4" height="18" rx="1.5"/>
                </svg>
            </div>
            <span class="font-bold text-xl tracking-tight text-gray-900">Alyze</span>
        </a>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 space-y-0.5 overflow-y-auto pb-4">

        @php
            $tid = request()->route('trackingId');
            $navLink = fn($active) => 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-colors ' .
                ($active ? 'bg-orange-50 text-orange-600 font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700 font-medium');
            $siteHref = fn(string $name) => $tid ? route($name, $tid) : '#';
        @endphp

        <a href="{{ route('dashboard.index') }}" class="{{ $navLink(request()->routeIs('dashboard.index')) }}">
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <rect x="2" y="2" width="7" height="7" rx="1.5"/>
                <rect x="11" y="2" width="7" height="7" rx="1.5"/>
                <rect x="2" y="11" width="7" height="7" rx="1.5"/>
                <rect x="11" y="11" width="7" height="7" rx="1.5"/>
            </svg>
            Dashboard
        </a>

        <a href="{{ route('boards.index') }}" class="{{ $navLink(request()->routeIs('boards.index')) }}">
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                <rect x="3" y="3" width="7" height="9" rx="1.5"/>
                <rect x="14" y="3" width="7" height="5" rx="1.5"/>
                <rect x="14" y="12" width="7" height="9" rx="1.5"/>
                <rect x="3" y="16" width="7" height="5" rx="1.5"/>
            </svg>
            Boards
        </a>

        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest px-3 pt-5 pb-1.5">Insight</p>

        <a href="{{ $siteHref('analytics.charts') }}" class="{{ $navLink(request()->routeIs('analytics.charts')) }}">
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 3h18v14H3z"/><path d="M7 17v2M17 17v2M7 9l3 3 3-3 4 4"/>
            </svg>
            Charts
        </a>

        <a href="{{ $siteHref('analytics.replay') }}" class="{{ $navLink(request()->routeIs('analytics.replay')) }}">
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 3l14 9-14 9V3z"/>
            </svg>
            Replay
        </a>

        <a href="{{ $siteHref('analytics.heatmap') }}" class="{{ $navLink(request()->routeIs('analytics.heatmap')) }}">
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2C9 5 7 8 7 11a5 5 0 0010 0c0-3-2-6-5-9z"/>
            </svg>
            Heatmap
        </a>

        <a href="{{ $siteHref('analytics.feedback') }}" class="{{ $navLink(request()->routeIs('analytics.feedback')) }}">
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
            </svg>
            Feedback
        </a>

        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest px-3 pt-5 pb-1.5">Data</p>

        <a href="{{ $siteHref('analytics.visitors') }}" class="{{ $navLink(request()->routeIs('analytics.visitors')) }}">
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                <circle cx="9" cy="7" r="4"/>
            </svg>
            Visitors
        </a>

        <a href="{{ $siteHref('analytics.events') }}" class="{{ $navLink(request()->routeIs('analytics.events')) }}">
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 15l-7-7M8.5 8.5v7h7"/>
            </svg>
            Events Click
        </a>

        <a href="{{ $siteHref('analytics.integration') }}" class="{{ $navLink(request()->routeIs('analytics.integration')) }}">
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/>
            </svg>
            Integration
        </a>

        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest px-3 pt-5 pb-1.5">Manage</p>

        <a href="{{ $siteHref('workspace.show') }}" class="{{ $navLink(request()->routeIs('workspace.show')) }}">
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9zM9 22V12h6v10"/>
            </svg>
            Workspace
        </a>
    </nav>

    {{-- Logout --}}
    <div class="px-3 pb-5 pt-3 border-t border-gray-100">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="flex items-center gap-3 px-3 py-2.5 rounded-xl w-full text-left text-sm text-gray-500 hover:bg-gray-50 hover:text-gray-700 font-medium transition-colors">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>
                </svg>
                Logout
            </button>
        </form>
    </div>
</aside>

{{-- ── Main ─────────────────────────────────────────────────── --}}
<main class="ml-60 flex-1 min-h-screen">
    @if(session('success'))
        <div class="mx-8 mt-5 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif
    @yield('content')
</main>

@stack('scripts')
</body>
</html>
