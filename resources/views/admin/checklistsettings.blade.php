@php $days = ['sun' => 'Sun', 'mon' => 'Mon', 'tue' => 'Tue', 'wed' => 'Wed', 'thu' => 'Thu', 'fri' => 'Fri', 'sat' => 'Sat']; @endphp

<x-app-layout>
    <div x-data="{
            toast: null, tone: 'ok',
            async toggle(el) {
                const res = await fetch('{{ route('admin.settings.toggle') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json', 'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({
                        site_id: {{ (int) $selectedSiteId }},
                        task_id: Number(el.dataset.taskId),
                        day: el.dataset.day,
                        enabled: el.checked
                    })
                }).catch(() => null);

                const ok = res && res.ok && (await res.json()).success;
                if (!ok) { el.checked = !el.checked; }
                this.tone = ok ? 'ok' : 'bad';
                this.toast = ok ? 'Setting saved' : 'Could not save that change';
                setTimeout(() => this.toast = null, 2600);
            }
         }">

        <div x-show="toast" x-cloak x-transition
             class="fixed right-5 top-20 z-50 rounded-lg px-4 py-2.5 text-sm font-semibold text-white shadow-lift"
             :class="tone === 'ok' ? 'bg-brand-600' : 'bg-rose-600'" x-text="toast"></div>

        <x-page-header title="Sites Checklist Settings"
                       subtitle="Choose which tasks appear on which weekday, per site." />

        @if ($sites->isNotEmpty())
            <form method="GET" action="{{ route('admin.checklist.management') }}" class="card card-pad mb-5">
                <div class="flex flex-wrap items-end gap-3">
                    <div class="min-w-[260px] flex-1">
                        <label for="cs-site" class="field-label">Site</label>
                        <select id="cs-site" name="site_id" onchange="this.form.submit()" class="w-full text-sm">
                            <option value="">Select a site…</option>
                            @foreach ($sites as $s)
                                <option value="{{ $s->id }}" @selected($selectedSiteId == $s->id)>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if ($selectedSiteId)
                        <span class="pill pill-brand mb-2.5"><x-icon name="check" class="h-3 w-3" /> Changes save instantly</span>
                    @endif
                </div>
            </form>

            @if ($selectedSiteId)
                @foreach (['DAY' => $day_shift_checklist, 'NIGHT' => $night_shift_checklist] as $shift => $checklists)
                    <x-card class="mb-6" :pad="false">
                        <div class="card-head">
                            <h2 class="text-base font-bold text-ink-900 dark:text-white">
                                {{ ucfirst(strtolower($shift)) }} shift checklist
                            </h2>
                            <span class="pill {{ $shift === 'DAY' ? 'pill-warn' : 'pill-info' }}">
                                <x-icon :name="$shift === 'DAY' ? 'sun' : 'moon'" class="h-3 w-3" /> {{ $shift }}
                            </span>
                        </div>

                        @if ($checklists->isEmpty())
                            <x-empty-state icon="checklist" title="No tasks configured for this shift" />
                        @else
                            <div class="tablewrap">
                                <table class="dtable">
                                    <thead>
                                        <tr>
                                            <th class="min-w-[280px]">Task</th>
                                            @foreach ($days as $label)
                                                <th class="text-center">{{ $label }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($checklists as $groupName => $rows)
                                            <tr class="bg-ink-100/70 dark:bg-ink-800/50">
                                                <td colspan="8" class="text-[11px] font-bold uppercase tracking-wider text-ink-600 dark:text-ink-300">
                                                    {{ $groupName }}
                                                </td>
                                            </tr>
                                            @foreach ($rows as $row)
                                                @php $setting = $settings[$row->id] ?? null; @endphp
                                                <tr>
                                                    <td class="text-ink-800 dark:text-ink-200">{{ $row->task_name }}</td>
                                                    @foreach ($days as $day => $label)
                                                        <td class="text-center">
                                                            <input type="checkbox" data-task-id="{{ $row->id }}" data-day="{{ $day }}"
                                                                   @change="toggle($event.target)"
                                                                   @checked(optional($setting)->{$day . '_enabled_bool'})
                                                                   class="h-4 w-4 cursor-pointer rounded border-ink-300 text-brand-600 focus:ring-brand-500 dark:border-ink-600 dark:bg-ink-800">
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </x-card>
                @endforeach
            @else
                <x-card>
                    <x-empty-state icon="settings" title="Select a site"
                                   message="Pick a site above to manage which checklist tasks run on which days." />
                </x-card>
            @endif
        @else
            <x-card>
                <x-empty-state icon="sites" title="No sites available"
                               message="You do not currently have access to any site's checklist settings." />
            </x-card>
        @endif
    </div>
</x-app-layout>
