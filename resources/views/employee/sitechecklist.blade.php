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

    <x-page-header title="Site Checklist" subtitle="Weekly task completion for the sites you can access." />

    <div class="cgrid">

    <div class="card card-pad mb-6">
        @if ($sites->count() > 1)
        <form method="GET" action="{{ route('site.checklist') }}" class="site-filter">
            <label for="site_id">Select Site:</label>
            <select name="site_id" id="site_id" onchange="this.form.submit()">
                <option value="">All Sites</option>
                @foreach ($sites as $site)
                <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>
                    {{ $site->name }}
                </option>
                @endforeach
            </select>
            <input type="hidden" name="week" value="{{ request('week', 'current') }}">
        </form>
        @endif

        <form method="GET" action="{{ route('site.checklist') }}" style="margin-bottom: 20px; text-align: right;">
            <label for="week">Select Week:</label>
            <select name="week" id="week" onchange="this.form.submit()">
                <option value="current" {{ request('week') !== 'previous' ? 'selected' : '' }}>Current Week</option>
                <option value="previous" {{ request('week') === 'previous' ? 'selected' : '' }}>Previous Week</option>
            </select>
            @if (request('site_id'))
            <input type="hidden" name="site_id" value="{{ request('site_id') }}">
            @endif
        </form>

        @php
        $filteredSites = request('site_id') ? $sites->where('id', request('site_id')) : $sites;
        @endphp

        @foreach ($filteredSites as $site)
        <h2 style="text-align:center;">
            Checklist for {{ $site->name }} (Week: {{ \Carbon\Carbon::parse($weekStart)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($weekEnd)->format('M d, Y') }})
        </h2>

        <!-- Day Shift Checklist -->
        <table>
            <tr class="section-header">
                <td colspan="8">DAY SHIFT CHECKLIST - {{ $site->name }}</td>
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
            @if (isset($day_shift_checklist[$site->id]))
            @foreach ($day_shift_checklist[$site->id] as $groupName => $group)
            <tr class="section-header">
                <td colspan="8">{{ $groupName }}</td>
            </tr>
            @foreach ($group as $row)
            @php
            $taskDataList = isset($checklistDataByTask[$site->id][$row->site_checklist_id]) ? $checklistDataByTask[$site->id][$row->site_checklist_id] : collect();
            @endphp
            <tr>
                <td>{{ $row->task_name }}</td>
                @foreach (['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'] as $day)
                @php
                $dayMarked = $taskDataList->contains(fn($entry) => $entry->{$day . '_bool'});
                @endphp
                <td>
                    @if ($dayMarked)
                    <span class="tick">✔</span>
                    @if ($row->task_name == 'STAFF INITIAL')
                    <br>
                    ({{
                                                    isset($tempValuesByDateAndShift[$day]['DAY SHIFT CHECKLIST']['staff_initial'])
                                                        ? $tempValuesByDateAndShift[$day]['DAY SHIFT CHECKLIST']['staff_initial']
                                                        : ''
                                                }})
                    @endif
                    @else
                    <span class="cross">✘</span>
                    @endif
                </td>
                @endforeach
            </tr>
            @endforeach
            @endforeach
            @endif
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

        <!-- Night Shift Checklist -->
        <div class="card card-pad mb-6">
            <table>
                <tr class="section-header">
                    <td colspan="8">NIGHT SHIFT CHECKLIST - {{ $site->name }}</td>
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
                @if (isset($night_shift_checklist[$site->id]))
                @foreach ($night_shift_checklist[$site->id] as $groupName => $group)
                <tr class="section-header">
                    <td colspan="8">{{ $groupName }}</td>
                </tr>
                @foreach ($group as $row)
                @php
                $taskDataList = isset($checklistDataByTask[$site->id][$row->site_checklist_id]) ? $checklistDataByTask[$site->id][$row->site_checklist_id] : collect();
                @endphp
                <tr>
                    <td>{{ $row->task_name }}</td>
                    @foreach (['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'] as $day)
                    @php
                    $dayMarked = $taskDataList->contains(fn($entry) => $entry->{$day . '_bool'});
                    @endphp
                    <td>
                        @if ($dayMarked)
                        <span class="tick">✔</span>
                        @if ($row->task_name == 'STAFF INITIAL')
                        <br>
                        ({{
                                                        isset($tempValuesByDateAndShift[$day]['NIGHT SHIFT CHECKLIST']['staff_initial'])
                                                            ? $tempValuesByDateAndShift[$day]['NIGHT SHIFT CHECKLIST']['staff_initial']
                                                            : ''
                                                    }})
                        @endif
                        @else
                        <span class="cross">✘</span>
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach
                @endforeach
                @endif
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
        @endforeach

        @if ($filteredSites->isEmpty())
        <p style="text-align: center; color: red;">No sites available.</p>
        @endif
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const cells = document.querySelectorAll("td");

            cells.forEach(cell => {
                if (cell.textContent.trim() === "" && !cell.hasAttribute("colspan")) {
                    cell.classList.add("missing-value");
                }
            });
        });
    </script>
    </div>
</x-app-layout>