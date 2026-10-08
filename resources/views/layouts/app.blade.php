<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'MCLS') }}</title>
    <link rel="icon" href="{{ asset('logo.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|jetbrains-mono:400,500&display=swap" rel="stylesheet" />

    {{-- Applied before paint so the theme never flashes. --}}
    <script>
        try {
            const t = localStorage.getItem('theme');
            if (t === 'dark' || (!t && matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        } catch (e) {}
    </script>

    @vite(['resources/css/app.css', 'resources/css/new.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans">
<div x-data="{ open: false }" class="min-h-full">

    {{-- Mobile scrim --}}
    <div x-show="open" x-transition.opacity @click="open = false"
         class="fixed inset-0 z-30 bg-ink-950/50 backdrop-blur-sm lg:hidden" x-cloak></div>

    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-40 flex w-[260px] flex-col border-r border-ink-200 bg-white transition-transform duration-200 dark:border-ink-800 dark:bg-ink-900 lg:translate-x-0"
           :class="open ? 'translate-x-0' : '-translate-x-full'">
        @include('layouts.navigation')
    </aside>

    {{-- Content --}}
    <div class="lg:pl-[260px]">
        <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-ink-200 bg-ink-50/85 px-4 backdrop-blur-md dark:border-ink-800 dark:bg-ink-950/85 sm:px-6">
            <button @click="open = true" class="btn btn-subtle btn-icon lg:hidden" aria-label="Open menu">
                <x-icon name="menu" />
            </button>

            <div class="min-w-0 flex-1">
                @isset($header)
                    <div class="truncate text-sm font-semibold text-ink-700 dark:text-ink-200">{{ $header }}</div>
                @endisset
            </div>

            <button type="button" class="btn btn-subtle btn-icon" aria-label="Toggle theme"
                    onclick="(function(){const d=document.documentElement.classList.toggle('dark');try{localStorage.setItem('theme',d?'dark':'light')}catch(e){}})()">
                <x-icon name="sun" class="hidden h-5 w-5 dark:block" />
                <x-icon name="moon" class="h-5 w-5 dark:hidden" />
            </button>

            <div class="relative" x-data="{ menu: false }">
                <button @click="menu = !menu" class="flex items-center gap-2.5 rounded-lg p-1.5 pr-2.5 transition hover:bg-ink-100 dark:hover:bg-ink-800">
                    <span class="grid h-8 w-8 place-items-center rounded-full bg-brand-600 text-xs font-bold text-white">
                        {{ \Illuminate\Support\Str::of(Auth::user()->name)->explode(' ')->take(2)->map(fn($p) => \Illuminate\Support\Str::substr($p, 0, 1))->implode('') }}
                    </span>
                    <span class="hidden text-left sm:block">
                        <span class="block text-xs font-semibold leading-tight text-ink-900 dark:text-white">{{ Auth::user()->name }}</span>
                        <span class="block text-[11px] leading-tight text-ink-500">
                            {{ Auth::user()->usertype === 'employee' ? 'Site User' : ucfirst(Auth::user()->usertype ?? 'User') }}
                        </span>
                    </span>
                    <x-icon name="chevron-d" class="h-4 w-4 text-ink-400" />
                </button>

                <div x-show="menu" @click.outside="menu = false" x-transition x-cloak
                     class="absolute right-0 mt-2 w-60 overflow-hidden rounded-xl border border-ink-200 bg-white py-1.5 shadow-lift dark:border-ink-800 dark:bg-ink-900">
                    <div class="border-b border-ink-200 px-3 pb-2.5 pt-1.5 dark:border-ink-800">
                        <p class="truncate text-sm font-semibold text-ink-900 dark:text-white">{{ Auth::user()->name }}</p>
                        <p class="truncate text-xs text-ink-500">{{ Auth::user()->email }}</p>
                        <div class="mt-2"><x-role-badge :type="Auth::user()->usertype" /></div>
                    </div>
                    <x-dropdown-link :href="route('profile.edit')"><x-icon name="user" class="h-4 w-4" /> {{ __('Profile') }}</x-dropdown-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-2.5 px-3 py-2 text-sm font-medium text-rose-600 transition hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/40">
                            <x-icon name="logout" class="h-4 w-4" /> {{ __('Log Out') }}
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-[1500px] px-4 py-6 sm:px-6 lg:py-8">
            <x-flash />
            {{ $slot }}
        </main>
    </div>
</div>
<style>[x-cloak]{display:none !important}</style>
</body>
</html>
