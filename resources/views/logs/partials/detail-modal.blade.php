<div x-show="detail === {{ $data->id }}" x-cloak @keydown.escape.window="detail = null"
     class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-ink-950/60 p-4 backdrop-blur-sm sm:p-8">
    <div @click.outside="detail = null" x-transition
         class="w-full max-w-2xl rounded-xl2 border border-ink-200 bg-white shadow-lift dark:border-ink-800 dark:bg-ink-900">
        <div class="card-head">
            <div>
                <h2 class="text-base font-bold text-ink-900 dark:text-white">Resident log details</h2>
                <p class="mt-0.5 text-xs text-ink-500">
                    {{ $data->resident->name ?? '—' }} · {{ $data->site->name ?? '—' }} ·
                    <span class="num">{{ \Carbon\Carbon::parse($data->log_date)->format('d M Y') }}</span>
                </p>
            </div>
            <button type="button" @click="detail = null" class="btn btn-subtle btn-icon"><x-icon name="close" /></button>
        </div>

        <div class="card-pad space-y-5">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                @foreach ([
                    'Shift' => ucfirst($data->shift),
                    'Staff type' => $data->employee_type === 'mcls' ? 'MCLS' : 'Agency',
                    'Recorded' => $data->log_time,
                    'Temperature' => $data->temperature ? $data->temperature . '°' : '—',
                ] as $k => $v)
                    <div class="rounded-lg bg-ink-100/70 px-3 py-2.5 dark:bg-ink-800/50">
                        <dt class="text-[10px] font-bold uppercase tracking-wide text-ink-500">{{ $k }}</dt>
                        <dd class="mt-1 truncate text-sm font-semibold text-ink-900 dark:text-white">{{ $v }}</dd>
                    </div>
                @endforeach
            </div>

            <dl class="divide-y divide-ink-200/80 dark:divide-ink-800">
                @foreach ([
                    'Staff name' => $data->mcls_name ?: $data->agency_employee_name,
                    'Agency' => $data->agency_name,
                    'ADLs' => $data->adls,
                    'Medical & health' => $data->medical,
                    'Behaviour & emotional well-being' => $data->behavior,
                    'Activities & engagement' => $data->activities,
                    'Nutritional intake' => $data->nutrition,
                    'Sleep patterns' => $data->sleep,
                    'Additional notes' => $data->notes,
                ] as $label => $value)
                    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">{{ $label }}</dt>
                        <dd class="text-sm text-ink-800 dark:text-ink-200 sm:col-span-2">{{ $value ?: '—' }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        <div class="flex justify-end gap-2 border-t border-ink-200/80 px-5 py-3.5 dark:border-ink-800">
            <button type="button" @click="detail = null" class="btn btn-ghost">Close</button>
            <button type="button" @click="detail = null; edit = {{ $data->id }}" class="btn btn-primary">
                <x-icon name="edit" class="h-4 w-4" /> Edit this log
            </button>
        </div>
    </div>
</div>
