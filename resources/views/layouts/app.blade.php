<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Analytics Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen">

<nav class="bg-gray-900 border-b border-gray-800 px-6 py-4 flex items-center justify-between">
    <a href="{{ route('dashboard.index') }}" class="text-xl font-bold text-indigo-400 tracking-tight">
        &#9679; Analytics
    </a>
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button class="text-sm text-gray-400 hover:text-white transition">Logout</button>
    </form>
</nav>

<main class="p-6 max-w-7xl mx-auto">
    @if(session('success'))
        <div class="mb-4 bg-green-900/40 border border-green-700 text-green-300 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif
    @yield('content')
</main>

@stack('scripts')
</body>
</html>
