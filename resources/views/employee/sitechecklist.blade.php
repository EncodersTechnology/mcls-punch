<x-app-layout>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            width: 80%;
            margin: 20px auto;
        }

        h2 {
            text-align: center;
            text-decoration: underline;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #005f73;
            color: white;
        }

        .section-header {
            background-color: #d1d1d1;
            font-weight: bold;
        }

        .highlight {
            font-weight: bold;
        }

        .monthly {
            background-color: #cce7f0;
        }

        .important {
            color: red;
            font-weight: bold;
        }

        .no-input {
            background-color: #e0e0e0;
        }

        .missing-value {
            background-color: rgb(227, 222, 222);
        }

        .tick {
            color: green;
            font-weight: bold;
            font-size: 18px;
        }

        .cross {
            color: red;
            font-weight: bold;
            font-size: 18px;
        }

        .site-filter {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
    </style>

    <div class="container">
        <form method="GET" action="{{ route('site.checklist') }}" class="site-filter">
            <label for="week_start">Select Sunday:</label>
            <input type="date" name="week_start" id="week_start" 
                value="{{ \Carbon\Carbon::parse($startOfWeek)->format('Y-m-d') }}"
                onchange="this.form.submit()"
                class="border rounded px-2 py-1">
            
            <label for="site_id" class="ml-4">Select Site:</label>
            <select name="site_id" id="site_id" onchange="this.form.submit()" class="border rounded px-2 py-1">
                <option value="">All Sites</option>
                @foreach ($sites as $site)
                <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>
                    {{ $site->name }}
                </option>
                @endforeach
            </select>
        </form>


        @php
        $filteredSites = request('site_id') ? $sites->where('id', request('site_id')) : $sites;
        @endphp

        @foreach ($filteredSites as $site)
        <h2 style="text-align:center;">
            Checklist for {{ $site->name }} (Week: {{ \Carbon\Carbon::parse($startOfWeek)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endOfWeek)->format('M d, Y') }})
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
        <div class="container">
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
</x-app-layout>