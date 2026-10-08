@php
    $role = auth()->user()->usertype;
    $isAdmin = in_array($role, ['admin', 'siteadmin']);
@endphp

<div class="flex h-16 shrink-0 items-center gap-2.5 border-b border-ink-200 px-4 dark:border-ink-800">
    <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-2.5">
        <span class="shrink-0 rounded-lg bg-white p-1.5 ring-1 ring-ink-200/80 dark:ring-ink-700">
            <x-application-logo class="h-7 w-auto" />
        </span>
        <span class="min-w-0 text-[10px] font-semibold uppercase leading-[1.35] tracking-wide text-ink-500 dark:text-ink-400">
            Records Management System
        </span>
    </a>
    <button @click="open = false" class="btn btn-subtle btn-icon ml-auto lg:hidden" aria-label="Close menu">
        <x-icon name="close" />
    </button>
</div>

<nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
    <p class="px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-ink-400">Overview</p>

    @if ($role === 'employee')
        <x-nav-link icon="dashboard" :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-nav-link>
    @endif

    @if ($isAdmin)
        <x-nav-link icon="logs" :href="route('admin.log.data')" :active="request()->routeIs('admin.log.data')">{{ __('Resident Log Data') }}</x-nav-link>
    @else
        <x-nav-link icon="logs" :href="route('employee.log.data')" :active="request()->routeIs('employee.log.data')">{{ __('Resident Log Data') }}</x-nav-link>
        @if ($role === 'employee')
            <x-nav-link icon="form" :href="route('employee.log.form')" :active="request()->routeIs('employee.log.form')">{{ __('Site Log Form') }}</x-nav-link>
        @endif
    @endif

    @if ($isAdmin)
        <x-nav-link icon="checklist" :href="route('admin.site.checklist')" :active="request()->routeIs('admin.site.checklist')">{{ __('Site Checklist') }}</x-nav-link>
    @else
        <x-nav-link icon="checklist" :href="route('site.checklist')" :active="request()->routeIs('site.checklist')">{{ __('Site Checklist') }}</x-nav-link>
    @endif

    @if (in_array($role, ['admin', 'siteadmin', 'director', 'manager', 'supervisor']))
        <p class="px-3 pb-1.5 pt-5 text-[10px] font-bold uppercase tracking-wider text-ink-400">Administration</p>

        <x-nav-link icon="settings" :href="route('admin.checklist.management')" :active="request()->routeIs('admin.checklist.management')">{{ __('Sites Checklist Settings') }}</x-nav-link>

        {{-- Kept in step with the admin middleware on this route. --}}
        @if ($isAdmin)
            <x-nav-link icon="sites" :href="route('admin.resident')" :active="request()->routeIs('admin.resident')">{{ __('Sites and Residents') }}</x-nav-link>
        @endif

        <x-nav-link icon="users" :href="route('site.access.index')" :active="request()->routeIs('site.access.index')">{{ __('Access Management') }}</x-nav-link>
    @endif

    {{-- Operational health sits last: admin-only, same as the route's middleware. --}}
    @if ($isAdmin)
        <p class="px-3 pb-1.5 pt-5 text-[10px] font-bold uppercase tracking-wider text-ink-400">System</p>
        <x-nav-link icon="health" :href="route('health.index')" :active="request()->routeIs('health.index')">{{ __('Health Check') }}</x-nav-link>
    @endif
</nav>

<div class="shrink-0 border-t border-ink-200 p-3 dark:border-ink-800">
    <div class="rounded-lg bg-ink-100 px-3 py-2.5 dark:bg-ink-800/60">
        <p class="truncate text-xs font-semibold text-ink-800 dark:text-ink-100">{{ auth()->user()->name }}</p>
        <p class="mt-1"><x-role-badge :type="$role" /></p>
    </div>
</div>
