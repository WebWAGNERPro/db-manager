<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased overflow-hidden">
    <div class="h-screen flex flex-col bg-gray-950">

        {{-- Top bar --}}
        <header class="h-11 bg-gray-900 border-b border-gray-800 flex items-center justify-between px-4 flex-shrink-0 z-20">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-200 transition" title="Dashboard">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </a>
                <span class="text-gray-800">|</span>
                <a href="{{ route('explorer') }}" class="text-xs {{ request()->routeIs('explorer*') ? 'font-bold text-orange-400' : 'text-gray-500 hover:text-gray-300' }} uppercase tracking-wider transition">Explorer</a>
                <a href="{{ route('databases.index') }}" class="text-xs {{ request()->routeIs('databases.*') ? 'font-bold text-emerald-400' : 'text-gray-500 hover:text-gray-300' }} transition">Databases</a>
                <a href="{{ route('database-users.index') }}" class="text-xs {{ request()->routeIs('database-users.*') ? 'font-bold text-blue-400' : 'text-gray-500 hover:text-gray-300' }} transition">Users</a>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <span class="text-gray-600">{{ Auth::user()->name }}</span>
                <a href="{{ route('profile.edit') }}" class="text-gray-500 hover:text-gray-300 transition">Profil</a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-gray-500 hover:text-red-400 transition">Deconnexion</button>
                </form>
            </div>
        </header>

        {{-- Main content --}}
        <main class="flex-1 overflow-y-auto">
            @isset($header)
                <div class="sticky top-0 z-10 bg-gray-900/95 backdrop-blur border-b border-gray-800 px-5 py-2.5">
                    {{ $header }}
                </div>
            @endisset

            {{ $slot }}
        </main>

    </div>
</body>
</html>
