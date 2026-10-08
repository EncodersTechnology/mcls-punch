@php
    $rowResidents = \App\Models\Resident::where('site_id', $data->site_id)->orderBy('name')->get(['id', 'name']);
@endphp
<div x-show="edit === {{ $data->id }}" x-cloak @keydown.escape.window="edit = null"
     class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-ink-950/60 p-4 backdrop-blur-sm sm:p-8">
    <div @click.outside="edit = null" x-transition
         x-data="{
            type: '{{ $data->employee_type }}',
            residents: @js($rowResidents),
            residentId: '{{ $data->resident_id }}',
            async siteChanged(e) {
                const res = await fetch('{{ route('get.residents') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ site_id: e.target.value })
                });
                this.residents = await res.json();
                this.residentId = '';
            }
         }"
         class="w-full max-w-3xl rounded-xl2 border border-ink-200 bg-white shadow-lift dark:border-ink-800 dark:bg-ink-900">

        <form method="POST" action="{{ route('log.update', $data->id) }}">
            @csrf
            @method('PUT')

            <div class="card-head">
                <div>
                    <h2 class="text-base font-bold text-ink-900 dark:text-white">Edit resident log</h2>
                    <p class="mt-0.5 text-xs text-ink-500">
                        Recorded <span class="num">{{ \Carbon\Carbon::parse($data->log_date)->format('d M Y') }}</span>
                    </p>
                </div>
                <button type="button" @click="edit = null" class="btn btn-subtle btn-icon"><x-icon name="close" /></button>
            </div>

            <div class="card-pad grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="field-label">Site</label>
                    <select name="site_id" class="w-full text-sm" @change="siteChanged($event)" required>
                        @foreach ($sites as $s)
                            <option value="{{ $s->id }}" @selected($data->site_id == $s->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="field-label">Resident</label>
                    <select name="resident_id" class="w-full text-sm" x-model="residentId" required>
                        <option value="">Select resident</option>
                        <template x-for="r in residents" :key="r.id">
                            <option :value="r.id" x-text="r.name" :selected="String(r.id) === String(residentId)"></option>
                        </template>
                    </select>
                    <p class="field-hint">Must belong to the selected site.</p>
                </div>

                <div>
                    <label class="field-label">Staff type</label>
                    <select name="employee_type" x-model="type" class="w-full text-sm" required>
                        <option value="mcls">MCLS Employee</option>
                        <option value="agency">Agency Employee</option>
                    </select>
                </div>

                <div>
                    <label class="field-label">Shift</label>
                    <input type="text" name="shift" value="{{ $data->shift }}" class="w-full text-sm" required>
                </div>

                <div x-show="type === 'mcls'" class="grid gap-4 sm:col-span-2 sm:grid-cols-2">
                    <div>
                        <label class="field-label">MCLS name</label>
                        <input type="text" name="mcls_name" value="{{ $data->mcls_name }}" class="w-full text-sm">
                    </div>
                    <div>
                        <label class="field-label">MCLS email</label>
                        <input type="email" name="mcls_email" value="{{ $data->mcls_email }}" class="w-full text-sm">
                    </div>
                </div>

                <div x-show="type === 'agency'" x-cloak class="grid gap-4 sm:col-span-2 sm:grid-cols-2">
                    <div>
                        <label class="field-label">Agency name</label>
                        <input type="text" name="agency_name" value="{{ $data->agency_name }}" class="w-full text-sm">
                    </div>
                    <div>
                        <label class="field-label">Agency employee name</label>
                        <input type="text" name="agency_employee_name" value="{{ $data->agency_employee_name }}" class="w-full text-sm">
                    </div>
                </div>

                <div>
                    <label class="field-label">Shift date</label>
                    <input type="date" name="log_date" value="{{ $data->log_date }}" class="w-full text-sm" required>
                </div>

                <div>
                    <label class="field-label">Temperature (°C)</label>
                    <input type="text" name="temperature" value="{{ $data->temperature }}" class="w-full text-sm" inputmode="decimal">
                </div>

                @foreach ([
                    'medical' => 'Medical & health info',
                    'behavior' => 'Behaviour & emotional well-being',
                    'activities' => 'Activities & engagement',
                    'nutrition' => 'Nutritional intake',
                    'sleep' => 'Sleep patterns',
                    'notes' => 'Additional notes',
                ] as $field => $label)
                    <div class="sm:col-span-2">
                        <label class="field-label">{{ $label }}</label>
                        <textarea name="{{ $field }}" rows="2" class="w-full text-sm">{{ $data->$field }}</textarea>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end gap-2 border-t border-ink-200/80 px-5 py-3.5 dark:border-ink-800">
                <button type="button" @click="edit = null" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary"><x-icon name="check" class="h-4 w-4" /> Save changes</button>
            </div>
        </form>
    </div>
</div>
