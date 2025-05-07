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
            /* Light red for empty cells */
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
    </style>

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
                    @if($row->task_name == 'STAFF INITIAL')
                    <br>
                    ({{ isset($staffInitialByDate[$day]) ? $staffInitialByDate[$day] : ''}})
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
                <td>{{ isset($tempValuesByDate[$day]) ? $tempValuesByDate[$day] : ''}}</td>
                @endforeach
            </tr>
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
                    @if($row->task_name == 'STAFF INITIAL')
                    <br>
                    ({{ isset($staffInitialByDate[$day]) ? $staffInitialByDate[$day] : ''}})
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
                <td>{{ isset($tempValuesByDate[$day]) ? $tempValuesByDate[$day] : ''}}</td>
                @endforeach
            </tr>
        </table>
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