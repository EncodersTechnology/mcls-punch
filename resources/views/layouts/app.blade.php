<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MCLS Logging System') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        @include('layouts.navigation')
        <!-- @auth
        <div class="min-w-[300px] fixed bottom-4 right-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-lg rounded-lg p-4 text-sm text-gray-700 dark:text-gray-300 z-50">
            <div><span class="font-semibold">Logged as:</span> {{ Auth::user()->name }}</div>
            @if(Auth::user()->usertype != 'admin' && Auth::user()->site)
            <div><span class="font-semibold">Site:</span> {{ Auth::user()->site->name }}</div>
            @endif
            <div><span class="font-semibold">Email:</span> {{ Auth::user()->email }}</div>
            <div><span class="font-semibold">User Type:</span> {{ Auth::user()->usertype }}</div>
        </div>
        @endauth -->

        <!-- Page Heading -->
        @if (isset($header))
        <header class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
        @endif

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>
</body>

</html>