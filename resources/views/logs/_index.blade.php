@php
    $isAdmin   = ($mode ?? 'employee') === 'admin';
    $formRoute = $isAdmin ? route('admin.log.data') : route('employee.log.data');
    $fromName  = $isAdmin ? 'start_date' : 'from_date';
    $toName    = $isAdmin ? 'end_date'   : 'to_date';
    $hasFilters = collect(['site_id','resident_id','shift','search',$fromName,$toName])
        ->contains(fn ($k) => request()->filled($k));
@endphp

<x-app-layout>
    <x-page-header title="Resident Log Data"
                   subtitle="Daily shift records across the sites you have access to.">
        <x-slot name="actions">
            <a href="{{ route('logs.export', request()->query()) }}" class="btn btn-ghost">
                <x-icon name="download" class="h-4 w-4" /> Export CSV
            </a>
        </x-slot>
    </x-page-header>

    {{-- Filters --}}
    <form method="GET" action="{{ $formRoute }}" class="card card-pad mb-5">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">
            <div class="xl:col-span-2">
                <label for="f-search" class="field-label">Search staff</label>
                <div class="relative">
                    <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" />
                    <input id="f-search" type="search" name="search" value="{{ request('search') }}"
                           placeholder="Name or agency…" class="w-full rounded-lg pl-9 text-sm">
                </div>
            </div>

            <div>
                <label for="f-site" class="field-label">Site</label>
                <select id="f-site" name="site_id" class="w-full text-sm">
                    <option value="">All sites</option>
                    @foreach ($sites as $s)
                        <option value="{{ $s->id }}" @selected(request('site_id') == $s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="f-resident" class="field-label">Resident</label>
                <select id="f-resident" name="resident_id" class="w-full text-sm">
                    <option value="">All residents</option>
                    @foreach ($residents as $r)
                        <option value="{{ $r->id }}" @selected(request('resident_id') == $r->id)>{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="f-from" class="field-label">From</label>
                <input id="f-from" type="date" name="{{ $fromName }}" value="{{ request($fromName) }}" class="w-full text-sm">
            </div>

            <div>
                <label for="f-to" class="field-label">To</label>
                <input id="f-to" type="date" name="{{ $toName }}" value="{{ request($toName) }}" class="w-full text-sm">
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-ink-200/80 pt-4 dark:border-ink-800">
            <button type="submit" class="btn btn-primary"><x-icon name="filter" class="h-4 w-4" /> Apply filters</button>
            @if ($hasFilters)
                <a href="{{ $formRoute }}" class="btn btn-ghost">Clear</a>
                <span class="pill pill-brand ml-1">Filtered</span>
            @endif
            <span class="ml-auto text-xs text-ink-500">
                <span class="num font-semibold text-ink-700 dark:text-ink-300">{{ $datas->total() }}</span> matching {{ Str::plural('record', $datas->total()) }}
            </span>
        </div>
    </form>

    <div x-data="{ detail: null, edit: null }">
        <div class="card overflow-hidden">
            @if ($datas->count())
                <div class="tablewrap max-h-[70vh]">
                    <table class="dtable">
                        <thead>
                            <tr>
                                <th class="w-12">#</th>
                                <th>Date</th>
                                <th>Site</th>
                                <th>Resident</th>
                                <th>Shift</th>
                                <th>Staff</th>
                                <th>Temp</th>
                                <th>Medical summary</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($datas as $i => $data)
                                <tr>
                                    <td class="num text-xs text-ink-400">{{ $datas->firstItem() + $i }}</td>
                                    <td class="whitespace-nowrap">
                                        <span class="num font-semibold text-ink-900 dark:text-white">
                                            {{ \Carbon\Carbon::parse($data->log_date)->format('d M Y') }}
                                        </span>
                                        <span class="num mt-0.5 block text-[11px] text-ink-400">{{ $data->log_time }}</span>
                                    </td>
                                    <td class="whitespace-nowrap">{{ $data->site->name ?? '—' }}</td>
                                    <td class="whitespace-nowrap font-medium">{{ $data->resident->name ?? '—' }}</td>
                                    <td><span class="pill pill-neutral capitalize">{{ $data->shift }}</span></td>
                                    <td>
                                        <span class="block font-medium">{{ $data->mcls_name ?: $data->agency_employee_name }}</span>
                                        <span class="pill {{ $data->employee_type === 'mcls' ? 'pill-brand' : 'pill-info' }} mt-1">
                                            {{ $data->employee_type === 'mcls' ? 'MCLS' : 'Agency' }}
                                        </span>
                                    </td>
                                    <td class="num whitespace-nowrap">{{ $data->temperature ? $data->temperature . '°' : '—' }}</td>
                                    <td><span class="cell-clamp text-ink-600 dark:text-ink-400">{{ $data->medical ?: '—' }}</span></td>
                                    <td>
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button" @click="detail = {{ $data->id }}"
                                                    class="btn btn-subtle btn-sm btn-icon" title="View details">
                                                <x-icon name="eye" class="h-4 w-4" />
                                            </button>
                                            <button type="button" @click="edit = {{ $data->id }}"
                                                    class="btn btn-subtle btn-sm btn-icon" title="Edit">
                                                <x-icon name="edit" class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <x-pagination :paginator="$datas" label="log records" />
            @else
                <x-empty-state icon="logs"
                    title="{{ $hasFilters ? 'No logs match these filters' : 'No resident logs yet' }}"
                    message="{{ $hasFilters ? 'Try widening the date range or clearing the filters.' : 'Logs recorded by site staff will appear here.' }}">
                    @if ($hasFilters)
                        <x-slot name="actions"><a href="{{ $formRoute }}" class="btn btn-ghost">Clear filters</a></x-slot>
                    @endif
                </x-empty-state>
            @endif
        </div>

        {{-- Modals live outside the table so they are never clipped by its scroll container. --}}
        @foreach ($datas as $data)
            @include('logs.partials.detail-modal', ['data' => $data])
            @include('logs.partials.edit-modal', ['data' => $data, 'sites' => $sites])
        @endforeach
    </div>
</x-app-layout>
