@php
    $mySite  = $site?->site;
    $shifts  = array_values(array_filter([$mySite?->shift_1, $mySite?->shift_2]));
    $canLog  = (bool) $site;
    $fields  = [
        'adls' => 'ADLs', 'medical' => 'Medical & health', 'behavior' => 'Behaviour & emotional',
        'activities' => 'Activities & engagement', 'nutrition' => 'Nutritional intake',
        'sleep' => 'Sleep patterns', 'notes' => 'Additional notes',
    ];
@endphp

<x-app-layout>
    <x-page-header title="Dashboard"
                   subtitle="{{ $canLog ? 'Record a shift log for ' . ($mySite->name ?? 'your site') . '.' : 'Overview of recent activity across your sites.' }}">
        <x-slot name="actions">
            <a href="{{ route('employee.log.data') }}" class="btn btn-ghost">
                <x-icon name="logs" class="h-4 w-4" /> View all logs
            </a>
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-5">

        {{-- Log entry --}}
        <div class="xl:col-span-3">
            @if ($canLog)
                <div class="card" x-data="{
                        type: '',
                        saving: false,
                        errors: {},
                        toast: null,
                        async submit(e) {
                            this.saving = true; this.errors = {};
                            const res = await fetch('{{ route('formdata.store') }}', {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                                body: new FormData(e.target)
                            });
                            const body = await res.json();
                            this.saving = false;
                            if (res.status === 201) {
                                this.paint(body.data);
                                e.target.reset(); this.type = '';
                                this.toast = 'Log submitted successfully.';
                                setTimeout(() => this.toast = null, 4000);
                            } else if (res.status === 422) {
                                this.errors = body.errors || {};
                            } else {
                                this.toast = body.message || 'Something went wrong.';
                                setTimeout(() => this.toast = null, 6000);
                            }
                        },
                        paint(d) {
                            const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v || '—'; };
                            set('display_employeeType', d.employee_type === 'mcls' ? 'MCLS' : 'Agency');
                            set('display_fullName', d.mcls_name || d.agency_employee_name);
                            set('display_site', d.site ? d.site.name : '');
                            set('display_shift', d.shift);
                            @foreach (array_keys($fields) as $f)
                                set('display_{{ $f }}', d.{{ $f }});
                            @endforeach
                            set('display_temperature', d.temperature);
                        }
                     }">
                    <div class="card-head">
                        <div>
                            <h2 class="text-base font-bold text-ink-900 dark:text-white">New shift log</h2>
                            <p class="mt-0.5 text-xs text-ink-500">{{ $mySite->name ?? 'Your site' }} · {{ now()->format('D d M Y') }}</p>
                        </div>
                        <span class="pill pill-brand"><x-icon name="clock" class="h-3 w-3" /> Auto-timed</span>
                    </div>

                    <form id="logForm" @submit.prevent="submit" class="card-pad space-y-5">
                        <div x-show="toast" x-cloak class="alert alert-success" x-text="toast"></div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="field-label" for="employeeType">Staff type</label>
                                <select id="employeeType" name="employee_type" x-model="type" class="w-full text-sm" required>
                                    <option value="">Select type</option>
                                    <option value="mcls">MCLS Employee</option>
                                    <option value="agency">Agency Employee</option>
                                </select>
                                <template x-if="errors.employee_type"><p class="mt-1 text-xs text-rose-600" x-text="errors.employee_type[0]"></p></template>
                            </div>

                            <div>
                                <label class="field-label" for="logDate">Shift date</label>
                                <input id="logDate" type="date" name="log_date" value="{{ now()->toDateString() }}" class="w-full text-sm" required>
                                <template x-if="errors.log_date"><p class="mt-1 text-xs text-rose-600" x-text="errors.log_date[0]"></p></template>
                            </div>

                            <template x-if="type === 'mcls'">
                                <div class="grid gap-4 sm:col-span-2 sm:grid-cols-2">
                                    <div>
                                        <label class="field-label">MCLS name</label>
                                        <input type="text" name="mcls_name" class="w-full text-sm">
                                        <template x-if="errors.mcls_name"><p class="mt-1 text-xs text-rose-600" x-text="errors.mcls_name[0]"></p></template>
                                    </div>
                                    <div>
                                        <label class="field-label">MCLS email</label>
                                        <input type="email" name="mcls_email" class="w-full text-sm" placeholder="you@multiculturalcls.org">
                                        <template x-if="errors.mcls_email"><p class="mt-1 text-xs text-rose-600" x-text="errors.mcls_email[0]"></p></template>
                                    </div>
                                </div>
                            </template>

                            <template x-if="type === 'agency'">
                                <div class="grid gap-4 sm:col-span-2 sm:grid-cols-2">
                                    <div>
                                        <label class="field-label">Agency name</label>
                                        <input type="text" name="agency_name" class="w-full text-sm">
                                        <template x-if="errors.agency_name"><p class="mt-1 text-xs text-rose-600" x-text="errors.agency_name[0]"></p></template>
                                    </div>
                                    <div>
                                        <label class="field-label">Agency employee name</label>
                                        <input type="text" name="agency_employee_name" class="w-full text-sm">
                                        <template x-if="errors.agency_employee_name"><p class="mt-1 text-xs text-rose-600" x-text="errors.agency_employee_name[0]"></p></template>
                                    </div>
                                </div>
                            </template>

                            <div>
                                <label class="field-label" for="resident_id">Resident</label>
                                <select id="resident_id" name="resident_id" class="w-full text-sm" required>
                                    <option value="">Select resident</option>
                                    @foreach ($site_residents as $r)
                                        <option value="{{ $r->id }}">{{ $r->name }}</option>
                                    @endforeach
                                </select>
                                <template x-if="errors.resident_id"><p class="mt-1 text-xs text-rose-600" x-text="errors.resident_id[0]"></p></template>
                            </div>

                            <div>
                                <label class="field-label" for="shift">Shift</label>
                                <select id="shift" name="shift" class="w-full text-sm" required>
                                    @forelse ($shifts as $s)
                                        <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                                    @empty
                                        <option value="morning">Morning</option>
                                        <option value="night">Night</option>
                                    @endforelse
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="field-label" for="temperature">Temperature (°C)</label>
                                <input id="temperature" type="text" name="temperature" inputmode="decimal" class="w-full text-sm sm:max-w-[180px]">
                            </div>

                            @foreach ($fields as $name => $label)
                                <div class="sm:col-span-2">
                                    <label class="field-label" for="{{ $name }}">{{ $label }}</label>
                                    <textarea id="{{ $name }}" name="{{ $name }}" rows="2" class="w-full text-sm"
                                        @if (in_array($name, ['adls', 'medical', 'behavior'])) required @endif></textarea>
                                    <template x-if="errors.{{ $name }}"><p class="mt-1 text-xs text-rose-600" x-text="errors.{{ $name }}[0]"></p></template>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex items-center gap-3 border-t border-ink-200/80 pt-4 dark:border-ink-800">
                            <button type="submit" class="btn btn-primary" :disabled="saving">
                                <x-icon name="check" class="h-4 w-4" />
                                <span x-text="saving ? 'Saving…' : 'Submit log'"></span>
                            </button>
                            <button type="reset" @click="type = ''; errors = {}" class="btn btn-ghost">Reset</button>
                        </div>
                    </form>
                </div>
            @else
                <x-card>
                    <x-empty-state icon="form" title="No site assigned to your account"
                                   message="Shift logs are recorded against a site. Your role reviews logs rather than recording them — use Resident Log Data to browse and export." >
                        <x-slot name="actions">
                            <a href="{{ route('employee.log.data') }}" class="btn btn-primary">
                                <x-icon name="logs" class="h-4 w-4" /> Go to Resident Log Data
                            </a>
                        </x-slot>
                    </x-empty-state>
                </x-card>
            @endif
        </div>

        {{-- Latest log --}}
        <div class="xl:col-span-2">
            <x-card title="Most recent log" subtitle="The latest entry from a site you can access.">
                @if ($form_data)
                    <dl class="divide-y divide-ink-200/80 dark:divide-ink-800">
                        @foreach ([
                            'display_employeeType' => ['Staff type', $form_data->employee_type === 'mcls' ? 'MCLS' : 'Agency'],
                            'display_fullName'     => ['Staff name', $form_data->mcls_name ?: $form_data->agency_employee_name],
                            'display_site'         => ['Site', $form_data->site->name ?? '—'],
                            'display_shift'        => ['Shift', ucfirst($form_data->shift)],
                            'display_temperature'  => ['Temperature', $form_data->temperature],
                            'display_adls'         => ['ADLs', $form_data->adls],
                            'display_medical'      => ['Medical & health', $form_data->medical],
                            'display_behavior'     => ['Behaviour', $form_data->behavior],
                            'display_activities'   => ['Activities', $form_data->activities],
                            'display_nutrition'    => ['Nutrition', $form_data->nutrition],
                            'display_sleep'        => ['Sleep', $form_data->sleep],
                            'display_notes'        => ['Notes', $form_data->notes],
                        ] as $id => [$label, $value])
                            <div class="grid grid-cols-3 gap-3 py-2.5">
                                <dt class="text-[11px] font-semibold uppercase tracking-wide text-ink-500">{{ $label }}</dt>
                                <dd id="{{ $id }}" class="col-span-2 text-sm text-ink-800 dark:text-ink-200">{{ $value ?: '—' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @else
                    <x-empty-state icon="inbox" title="No logs yet"
                                   message="Once a shift log is recorded it will show here." />
                @endif
            </x-card>
        </div>
    </div>
</x-app-layout>
