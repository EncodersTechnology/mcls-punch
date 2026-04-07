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

        .important {
            color: red;
            font-weight: bold;
        }

        .container {
            width: 90%;
            margin: 20px auto;
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

    <div class="container mb-6">
        <form method="GET" action="{{ route('site.checklist') }}" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label for="site_id" class="block text-black font-semibold mb-2">Select Site</label>
                <select name="site_id" id="site_id" class="w-full p-3 rounded-lg bg-white text-black border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Choose Site --</option>
                    @foreach ($sites as $site)
                    <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>
                        {{ $site->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1">
                <label for="week_start" class="block text-black font-semibold mb-2">Select Sunday</label>
                <input type="date" name="week_start" id="week_start" 
                    value="{{ \Carbon\Carbon::parse($weekStart)->format('Y-m-d') }}"
                    class="w-full p-3 rounded-lg border-gray-400"
                    onchange="validateSunday(this)">
            </div>

            <div class="flex items-end">
                <button style="background-color: black;" type="submit" class="px-6 py-3 bg-black text-white rounded-lg hover:bg-gray-900 transition">
                    Filter
                </button>
            </div>
        </form>
    </div>


        @php
        $selectedSiteId = request('site_id');
        $filteredSites = $selectedSiteId ? $sites->where('id', $selectedSiteId) : collect();
        @endphp

        @if (!$selectedSiteId)
        <div class="container mb-6">
            <p class="text-red-500 text-center font-semibold">Please select a site to view the checklist data.</p>
        </div>
        @endif

        @foreach ($filteredSites as $site)
        <h2 style="text-align:center;">
            Checklist for {{ $site->name }} (Week: {{ \Carbon\Carbon::parse($weekStart)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($weekEnd)->format('M d, Y') }})
        </h2>
<div class="container">
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
                        </div>
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
        function validateSunday(input) {
            const [year, month, day] = input.value.split('-');
            const selectedDate = new Date(year, month - 1, day); // JS months are 0-based
            if (selectedDate.getDay() !== 0) {
                alert('Please select a Sunday as the start of the week.');
                input.value = '';
            }
        }

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