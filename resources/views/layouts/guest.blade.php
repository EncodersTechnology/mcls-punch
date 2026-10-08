<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'MCLS') }}</title>
    <link rel="icon" href="{{ asset('logo.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

    <script>
        try {
            const t = localStorage.getItem('theme');
            if (t === 'dark' || (!t && matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        } catch (e) {}
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans">
<div class="flex min-h-full flex-col lg:flex-row">

    {{-- Brand panel --}}
    <div class="relative hidden overflow-hidden bg-brand-900 lg:flex lg:w-[46%] lg:flex-col lg:justify-between lg:p-12">
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 opacity-[0.18]"
             style="background-image:radial-gradient(circle at 1px 1px, #fff 1px, transparent 0); background-size: 26px 26px;"></div>
        <div aria-hidden="true" class="pointer-events-none absolute -right-24 -top-24 h-[420px] w-[420px] rounded-full bg-brand-400/25 blur-3xl"></div>
        <div aria-hidden="true" class="pointer-events-none absolute -bottom-32 -left-20 h-[380px] w-[380px] rounded-full bg-brand-300/20 blur-3xl"></div>

        <div class="relative">
            <div class="inline-flex items-center rounded-2xl bg-white/95 px-6 py-5 shadow-lift ring-1 ring-white/20">
                <img src="{{ asset('logo.png') }}" alt="Multicultural Community Living Services" class="h-20 w-auto">
            </div>
            <p class="mt-4 text-[13px] font-semibold uppercase tracking-[0.18em] text-brand-200">
                Records Management System
            </p>
        </div>

        <div class="relative max-w-md">
            <h2 class="text-[34px] font-extrabold leading-[1.15] tracking-tight text-white">
                Resident care, recorded with care.
            </h2>
            <p class="mt-4 text-[15px] leading-relaxed text-brand-100/90">
                Shift logs, site checklists and staff access for every location — in one place, scoped to the people who should see them.
            </p>
            <dl class="mt-10 grid grid-cols-3 gap-4 border-t border-white/15 pt-6">
                <div><dt class="text-[11px] font-medium uppercase tracking-wide text-brand-300">Daily</dt><dd class="mt-1 text-sm font-semibold text-white">Resident logs</dd></div>
                <div><dt class="text-[11px] font-medium uppercase tracking-wide text-brand-300">Weekly</dt><dd class="mt-1 text-sm font-semibold text-white">Site checklists</dd></div>
                <div><dt class="text-[11px] font-medium uppercase tracking-wide text-brand-300">Always</dt><dd class="mt-1 text-sm font-semibold text-white">Role-scoped</dd></div>
            </dl>
        </div>

        <p class="relative text-xs text-brand-300/80">&copy; {{ date('Y') }} Multicultural Community Living Services</p>
    </div>

    {{-- Form panel --}}
    <div class="flex flex-1 items-center justify-center px-5 py-12 sm:px-8">
        <div class="w-full max-w-[400px] animate-fade-up">
            <div class="mb-8 lg:hidden">
                <div class="inline-flex items-center rounded-xl bg-white px-4 py-3 shadow-card ring-1 ring-ink-200/70">
                    <img src="{{ asset('logo.png') }}" alt="Multicultural Community Living Services" class="h-14 w-auto">
                </div>
                <p class="mt-3 text-[11px] font-semibold uppercase tracking-[0.16em] text-ink-500">
                    Records Management System
                </p>
            </div>
            {{ $slot }}
        </div>
    </div>
</div>
</body>
</html>
