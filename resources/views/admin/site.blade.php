<x-app-layout>
    <style>
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

        .container {
            width: 90%;
            margin: 20px auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background-color: white;
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
    </style>

    <div class="container mb-6">
        <form method="GET" action="{{ route('admin.site.checklist') }}" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label for="site_id" class="block text-black font-semibold mb-2">Select Site</label>
                <select name="site_id" id="site_id" class="w-full p-3 rounded-lg bg-gray-800 text-white border border-gray-600">
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
                <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Filter</button>
            </div>
        </form>
    </div>


    @if (!$selectedSiteId)
    <div class="container mb-6">
        <p class="text-red-500 text-center font-semibold">Please select a site to view the checklist data.</p>
    </div>
    @endif

    @if ($selectedSiteId)
    <div class="container">
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
                    @else
                    <span class="cross">&#10008;</span>
                    @endif
                </td>
                @endforeach
            </tr>
            @endforeach

            @endforeach
        </table>
    </div>

    <div class="container">
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
                    @else
                    <span class="cross">&#10008;</span>
                    @endif
                </td>
                @endforeach
            </tr>
            @endforeach

            @endforeach
        </table>
    </div>
    @endif
</x-app-layout>

<script>
function validateSunday(input) {
    const selectedDate = new Date(input.value);
    if (selectedDate.getDay() !== 0) {
        alert('Please select a Sunday as the start of the week.');
        input.value = '';
    }
}
</script>