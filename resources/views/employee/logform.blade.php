@php
    $dayConfigs = ['sun' => 'Sunday', 'mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday',
                   'thu' => 'Thursday', 'fri' => 'Friday', 'sat' => 'Saturday'];
@endphp

<x-app-layout>
    <x-page-header title="Site Log Form"
                   subtitle="Record which checklist tasks were completed, and on which day of this week." />

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3"
         x-data="checklistLogForm(@js($checklistTypes), @js($siteChecklistSettings), @js($tempValuesByDate), @js($weekDates))">

        <form method="POST" action="{{ route('sitechecklistdata.store') }}" class="xl:col-span-2" @submit="onSubmit">
            @csrf

            <x-card title="Checklist entry"
                    subtitle="Week of {{ \Carbon\Carbon::parse($startOfWeek)->format('d M') }} – {{ \Carbon\Carbon::parse($endOfWeek)->format('d M Y') }}">

                <div class="space-y-6">
                    {{-- Shift --}}
                    <div>
                        <label for="shift_type" class="field-label">Shift <span class="text-rose-600">*</span></label>
                        <div class="grid grid-cols-2 gap-3 sm:max-w-md">
                            @foreach (['DAY SHIFT CHECKLIST' => ['Day shift', 'sun'], 'NIGHT SHIFT CHECKLIST' => ['Night shift', 'moon']] as $val => [$label, $ico])
                                <button type="button" @click="setShift('{{ $val }}')"
                                        :class="shift === '{{ $val }}'
                                            ? 'border-brand-600 bg-brand-50 text-brand-800 dark:bg-brand-950 dark:text-brand-200'
                                            : 'border-ink-200 hover:bg-ink-50 dark:border-ink-700 dark:hover:bg-ink-800'"
                                        class="flex items-center justify-center gap-2 rounded-lg border-2 px-4 py-3 text-sm font-semibold transition">
                                    <x-icon name="{{ $ico }}" class="h-4 w-4" /> {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Tasks --}}
                    <div x-show="shift" x-cloak>
                        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                            <label class="field-label mb-0">Checklist tasks <span class="text-rose-600">*</span></label>
                            <span class="pill pill-brand" x-show="selected.length">
                                <span class="num" x-text="selected.length"></span> selected
                            </span>
                        </div>

                        <div class="relative mb-2">
                            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" />
                            <input type="search" x-model="search" placeholder="Filter tasks…" class="w-full rounded-lg pl-9 text-sm">
                        </div>

                        <div class="max-h-72 overflow-y-auto rounded-lg border border-ink-200 dark:border-ink-800">
                            <template x-for="group in groups" :key="group.name">
                                <div>
                                    <p class="sticky top-0 bg-ink-100/95 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-ink-600 backdrop-blur dark:bg-ink-800/95 dark:text-ink-300"
                                       x-text="group.name"></p>
                                    <template x-for="item in group.items" :key="item.id">
                                        <label class="flex cursor-pointer items-start gap-3 border-b border-ink-100 px-3 py-2.5 transition last:border-0 hover:bg-brand-50/60 dark:border-ink-800 dark:hover:bg-ink-800/60">
                                            <input type="checkbox" name="site_checklist_ids[]" :value="item.id"
                                                   :checked="selected.includes(item.id)" @change="toggleTask(item.id)"
                                                   class="mt-0.5 h-4 w-4 rounded border-ink-300 text-brand-600 focus:ring-brand-500 dark:border-ink-600 dark:bg-ink-900">
                                            <span class="text-sm text-ink-800 dark:text-ink-200" x-text="item.task_name"></span>
                                        </label>
                                    </template>
                                </div>
                            </template>
                            <p x-show="!groups.length" class="px-3 py-6 text-center text-sm text-ink-500">No tasks match that search.</p>
                        </div>
                    </div>

                    {{-- Days --}}
                    <div x-show="selected.length" x-cloak>
                        <label class="field-label">Days completed</label>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" x-show="prevSatVisible" @click="togglePrevSat()"
                                    :class="prevSat ? 'border-brand-600 bg-brand-600 text-white' : 'border-ink-200 bg-white text-ink-700 dark:border-ink-700 dark:bg-ink-900 dark:text-ink-300'"
                                    class="rounded-xl border-2 px-4 py-2.5 text-sm font-semibold transition">
                                Previous Saturday
                                <span class="num mt-0.5 block text-[10px] font-normal opacity-80">{{ $weekDates['prev_sat'] ?? '' }}</span>
                            </button>
                            <input type="hidden" name="prev_sat_bool" :value="prevSat ? 1 : 0">

                            @foreach ($dayConfigs as $abbr => $dayName)
                                <button type="button" @click="toggleDay('{{ $abbr }}')" :disabled="disabled.{{ $abbr }}"
                                        :class="days.{{ $abbr }}
                                            ? 'border-brand-600 bg-brand-600 text-white'
                                            : (disabled.{{ $abbr }}
                                                ? 'border-ink-200 bg-ink-100 text-ink-400 opacity-60 cursor-not-allowed dark:border-ink-800 dark:bg-ink-800/50'
                                                : 'border-ink-200 bg-white text-ink-700 hover:border-brand-400 dark:border-ink-700 dark:bg-ink-900 dark:text-ink-300')"
                                        class="rounded-xl border-2 px-4 py-2.5 text-sm font-semibold transition">
                                    {{ $dayName }}
                                    <span class="num mt-0.5 block text-[10px] font-normal opacity-80">{{ $weekDates[$abbr] ?? '' }}</span>
                                </button>
                                <input type="hidden" name="{{ $abbr }}_bool" :value="days.{{ $abbr }} ? 1 : 0">
                            @endforeach
                        </div>
                        <p class="field-hint">Days greyed out are not enabled for every selected task at this site.</p>
                    </div>

                    {{-- Temperature --}}
                    <div class="sm:max-w-xs">
                        <label for="temp_value" class="field-label">Temperature (°C)</label>
                        <input type="text" name="temp_value" id="temp_value" x-model="temp" :readonly="tempLocked"
                               inputmode="decimal" placeholder="e.g. 21.5" class="w-full text-sm">
                        <p class="field-hint" x-show="tempLocked" x-cloak>Already recorded for this day.</p>
                    </div>

                    {{-- Staff initial --}}
                    <div x-show="needsInitial" x-cloak class="sm:max-w-xs">
                        <label for="staff_initial" class="field-label">Staff initial <span class="text-rose-600">*</span></label>
                        <input type="text" name="staff_initial" id="staff_initial" :required="needsInitial"
                               placeholder="e.g. AB" class="w-full text-sm">
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-ink-200/80 pt-5 dark:border-ink-800">
                    <button type="submit" class="btn btn-primary" :disabled="!selected.length || !anyDay">
                        <x-icon name="check" class="h-4 w-4" /> Submit checklist
                    </button>
                    <button type="button" @click="reset()" class="btn btn-ghost">Reset</button>
                    <span x-show="selected.length && !anyDay" x-cloak class="text-xs font-medium text-amber-600">
                        Pick at least one day.
                    </span>
                </div>
            </x-card>
        </form>

        {{-- Guidance --}}
        <div>
            <x-card title="How this works">
                <ol class="space-y-4 text-sm text-ink-600 dark:text-ink-400">
                    @foreach ([
                        'Choose the shift you are recording for.',
                        'Tick the checklist tasks that were completed.',
                        'The day is picked for you — today for a day shift, yesterday for a night shift. Adjust if needed.',
                        'Add the temperature reading and your initials if the task asks for them.',
                    ] as $i => $step)
                        <li class="flex gap-3">
                            <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-100 text-[11px] font-bold text-brand-800 dark:bg-brand-950 dark:text-brand-200">{{ $i + 1 }}</span>
                            <span>{{ $step }}</span>
                        </li>
                    @endforeach
                </ol>
                <div class="mt-5 rounded-lg bg-ink-100/70 px-3.5 py-3 text-xs text-ink-600 dark:bg-ink-800/50 dark:text-ink-400">
                    Each task can only be recorded once per day per week. Re-submitting a day that is already filled is rejected.
                </div>
            </x-card>
        </div>
    </div>

    <script>
        function checklistLogForm(allTypes, settings, tempByDate, weekDates) {
            const DAYS = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
            const blank = (v) => Object.fromEntries(DAYS.map(d => [d, v]));

            return {
                allTypes, settings, tempByDate, weekDates,
                shift: '', search: '', selected: [],
                days: blank(false), disabled: blank(false),
                prevSat: false, prevSatVisible: false,
                temp: '', tempLocked: false, needsInitial: false,

                get options() {
                    const list = this.allTypes[this.shift] || [];
                    const q = this.search.trim().toLowerCase();
                    return q ? list.filter(i => i.task_name.toLowerCase().includes(q)) : list;
                },
                get groups() {
                    const out = [];
                    for (const item of this.options) {
                        let g = out.find(x => x.name === item.group_name);
                        if (!g) { g = { name: item.group_name, items: [] }; out.push(g); }
                        g.items.push(item);
                    }
                    return out;
                },
                get anyDay() { return this.prevSat || DAYS.some(d => this.days[d]); },

                setShift(v) { this.shift = v; this.selected = []; this.recompute(); },

                toggleTask(id) {
                    const i = this.selected.indexOf(id);
                    if (i === -1) this.selected.push(id); else this.selected.splice(i, 1);
                    this.recompute();
                },

                toggleDay(d) {
                    if (this.disabled[d]) return;
                    this.days[d] = !this.days[d];
                    this.applyTemp(this.days[d] ? d : null);
                },

                togglePrevSat() {
                    this.prevSat = !this.prevSat;
                    this.applyTemp(this.prevSat ? 'prev_sat' : null);
                },

                applyTemp(key) {
                    if (key && this.tempByDate[key]) { this.temp = this.tempByDate[key]; this.tempLocked = true; }
                    else { this.temp = ''; this.tempLocked = false; }
                },

                recompute() {
                    const chosen = this.allTypes[this.shift]?.filter(i => this.selected.includes(i.id)) || [];

                    this.needsInitial = chosen.some(i => (i.task_name || '').toUpperCase().includes('STAFF INITIAL'));
                    this.days = blank(false);
                    this.prevSat = false;
                    this.prevSatVisible = false;

                    // A day is offered only when every selected task is enabled for it at this
                    // site. (The old code let whichever task happened to be last decide.)
                    this.disabled = blank(false);
                    if (this.selected.length) {
                        for (const d of DAYS) {
                            this.disabled[d] = this.selected.some(id => {
                                const s = this.settings.find(x => x.site_checklist_id == id);
                                return s ? Number(s[d + '_enabled_bool']) !== 1 : false;
                            });
                        }
                    }

                    if (!this.selected.length) { this.applyTemp(null); return; }

                    // Day shift records today; a night shift records the day that just ended.
                    const ref = new Date();
                    if (this.shift === 'NIGHT SHIFT CHECKLIST') ref.setDate(ref.getDate() - 1);
                    const target = DAYS[ref.getDay()];

                    if (target === 'sat' && this.shift === 'NIGHT SHIFT CHECKLIST') {
                        this.prevSatVisible = true;
                        this.prevSat = true;
                        this.applyTemp('prev_sat');
                    } else if (!this.disabled[target]) {
                        this.days[target] = true;
                        this.applyTemp(target);
                    } else {
                        this.applyTemp(null);
                    }
                },

                reset() {
                    this.shift = ''; this.search = ''; this.selected = [];
                    this.days = blank(false); this.disabled = blank(false);
                    this.prevSat = false; this.prevSatVisible = false;
                    this.temp = ''; this.tempLocked = false; this.needsInitial = false;
                },

                onSubmit(e) {
                    if (!this.selected.length || !this.anyDay) e.preventDefault();
                }
            };
        }
    </script>
</x-app-layout>
