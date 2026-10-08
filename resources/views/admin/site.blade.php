<x-app-layout>
    <style>
        /* Weekly checklist grid, themed with the design system tokens. */
        .cgrid table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 13px; }
        .cgrid th, .cgrid td {
            border-bottom: 1px solid rgb(220 225 224 / 1);
            padding: 9px 12px; text-align: center; vertical-align: middle;
        }
        .cgrid th {
            background: rgb(236 239 238 / 1); color: rgb(87 98 96 / 1);
            font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em;
            position: sticky; top: 0; z-index: 5;
        }
        .cgrid td:first-child, .cgrid th:first-child { text-align: left; }
        .cgrid tbody tr:hover td, .cgrid tr:hover td { background: rgb(239 250 247 / .55); }
        .cgrid .section-header td, .cgrid .section-header th {
            background: rgb(236 239 238 / 1); font-weight: 700; text-align: left;
            font-size: 11px; text-transform: uppercase; letter-spacing: .05em; color: rgb(87 98 96 / 1);
        }
        .cgrid .highlight { font-weight: 700; }
        .cgrid .monthly { background: rgb(214 242 234 / .6); }
        .cgrid h2 { font-size: 15px; font-weight: 700; text-align: left !important; text-decoration: none !important; margin: 0 0 12px; }

        .dark .cgrid th { background: rgb(31 39 36 / 1); color: rgb(192 202 198 / 1); }
        .dark .cgrid th, .dark .cgrid td { border-bottom-color: rgb(42 51 48 / 1); }
        .dark .cgrid tbody tr:hover td, .dark .cgrid tr:hover td { background: rgb(31 39 36 / .7); }
        .dark .cgrid .section-header td, .dark .cgrid .section-header th { background: rgb(31 39 36 / 1); color: rgb(192 202 198 / 1); }
        .dark .cgrid .monthly { background: rgb(19 77 69 / .35); }
    </style>

    <x-page-header title="Site Checklist" subtitle="Weekly task completion, by site and week." />

    <div class="cgrid">

    <div class="card card-pad mb-6">
        <form method="GET" action="{{ route('admin.site.checklist') }}" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label for="site_id" class="block text-black font-semibold mb-2">Select Site</label>
                <select name="site_id" id="site_id" class="w-full p-3 rounded-lg bg-white text-black border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Choose Site --</option>
                    @foreach ($sites as $site)
                    <option value="{{ $site->id }}" {{ $selectedSiteId == $site->id ? 'selected' : '' }}>
                        {{ $site->name }}
                    </option>
                    @endforeach
                </select>
            </div>


            <div class="flex-1">
                <label for="week_start" class="block text-black font-semibold mb-2">Week Start (Only Sunday)</label>
                <input type="date"
                    name="week_start"
                    id="week_start"
                    value="{{ request('week_start', $weekStart->toDateString()) }}"
                    class="w-full p-3 rounded-lg border-gray-400"
                    onchange="validateSunday(this)" />
            </div>

            <div class="flex items-end">
                <button style="background-color: black;" type="submit" class="px-6 py-3 bg-black text-white rounded-lg hover:bg-gray-900 transition dark:bg-gray-800 dark:hover:bg-gray-700">
                    Filter
                </button>




            </div>
        </form>
    </div>


    @if (!$selectedSiteId)
    <div class="card card-pad mb-6">
        <p class="text-red-500 text-center font-semibold">Please select a site to view the checklist data.</p>
    </div>
    @endif

    @if ($selectedSiteId)
    <div class="card card-pad mb-6">
        <h2 style="text-align:center;">Day Shift Checklist for Week: {{ \Carbon\Carbon::parse($weekStart)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($weekEnd)->format('M d, Y') }}</h2>
        <table>
            <tr class="section-header">
                <td colspan="8">DAY SHIFT CHECKLIST</td>
            </tr>
            <tr>
                <th>TASK</th>
                <th>SUN</th>
                <th>MON</th>
                <th>TUE</th>
                <th>WED</th>
                <th>THU</th>
                <th>FRI</th>
                <th>SAT</th>
            </tr>
            @foreach ($day_shift_checklist as $groupName => $group)
            <tr class="section-header">
                <td colspan="8">{{ $groupName }}</td>
            </tr>
            @foreach ($group as $row)
            @php
            $taskDataList = $checklistDataByTask[$row->site_checklist_id] ?? collect();
            @endphp
            <tr>
                <td>{{ $row->task_name }}</td>
                @foreach (['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'] as $day)
                @php
                $dayMarked = $taskDataList->contains(fn($entry) => $entry->{$day . '_bool'});
                @endphp
                <td>
                    @if ($dayMarked)
                    <span class="tick">&#10004;</span>
                    @if($row->task_name == 'STAFF INITIAL')
                    <br>
                    ({{
                    isset($tempValuesByDateAndShift[$day]['DAY SHIFT CHECKLIST']['staff_initial'])
                        ? $tempValuesByDateAndShift[$day]['DAY SHIFT CHECKLIST']['staff_initial']
                        : ''
                    }})
                    @endif
                    @else
                    <span class="cross">&#10008;</span>
                    @endif
                </td>
                @endforeach
            </tr>
            @endforeach

            @endforeach
            <tr class="section-header">
                <td>Temperature</td>
                @foreach (['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'] as $day)
                <td>
                    {{
                isset($tempValuesByDateAndShift[$day]['DAY SHIFT CHECKLIST']['temp_value'])
                    ? $tempValuesByDateAndShift[$day]['DAY SHIFT CHECKLIST']['temp_value']
                    : ''
                    }}
                </td>
                @endforeach
            </tr>
        </table>
    </div>

    <div class="card card-pad mb-6">
        <h2 style="text-align:center;">Night Shift Checklist for Week: {{ \Carbon\Carbon::parse($weekStart)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($weekEnd)->format('M d, Y') }}</h2>
        <table>
            <tr class="section-header">
                <td colspan="8">NIGHT SHIFT CHECKLIST</td>
            </tr>
            <tr>
                <th>TASK</th>
                <th>SUN</th>
                <th>MON</th>
                <th>TUE</th>
                <th>WED</th>
                <th>THU</th>
                <th>FRI</th>
                <th>SAT</th>
            </tr>
            @foreach ($night_shift_checklist as $groupName => $group)
            <tr class="section-header">
                <td colspan="8">{{ $groupName }}</td>
            </tr>
            @foreach ($group as $row)
            @php
            $taskDataList = $checklistDataByTask[$row->site_checklist_id] ?? collect();
            @endphp
            <tr>
                <td>{{ $row->task_name }}</td>
                @foreach (['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'] as $day)
                @php
                $dayMarked = $taskDataList->contains(fn($entry) => $entry->{$day . '_bool'});
                @endphp
                <td>
                    @if ($dayMarked)
                    <span class="tick">&#10004;</span>
                    @if($row->task_name == 'STAFF INITIAL')
                    <br>
                    ({{
                    isset($tempValuesByDateAndShift[$day]['NIGHT SHIFT CHECKLIST']['staff_initial'])
                        ? $tempValuesByDateAndShift[$day]['NIGHT SHIFT CHECKLIST']['staff_initial']
                        : ''
                    }})
                    @endif
                    @else
                    <span class="cross">&#10008;</span>
                    @endif
                </td>
                @endforeach
            </tr>
            @endforeach

            @endforeach
            <tr class="section-header">
                <td>Temperature</td>
                @foreach (['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'] as $day)
                <td>
                    {{
                isset($tempValuesByDateAndShift[$day]['NIGHT SHIFT CHECKLIST']['temp_value'])
                    ? $tempValuesByDateAndShift[$day]['NIGHT SHIFT CHECKLIST']['temp_value']
                    : ''
                    }}
                </td>
                @endforeach
            </tr>
        </table>
    </div>
    @endif
    </div>
</x-app-layout>

<script>
    function validateSunday(input) {
        const [year, month, day] = input.value.split('-');
        const selectedDate = new Date(year, month - 1, day); // JS months are 0-based
        if (selectedDate.getDay() !== 0) {
            alert('Please select a Sunday as the start of the week.');
            input.value = '';
        }
    }
</script>