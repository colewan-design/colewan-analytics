<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Analytics</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-950 text-gray-100 flex items-center justify-center min-h-screen">
<div class="w-full max-w-sm bg-gray-900 rounded-xl border border-gray-800 p-8 shadow-2xl">
    <h1 class="text-2xl font-bold text-indigo-400 mb-6 text-center">&#9679; Analytics</h1>
    <form action="{{ route('login.submit') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm text-gray-400 mb-1">Admin Password</label>
            <input type="password" name="password" autofocus required
                class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            @error('password')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit"
            class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2.5 rounded-lg transition">
            Sign In
        </button>
    </form>
</div>
</body>
</html>
