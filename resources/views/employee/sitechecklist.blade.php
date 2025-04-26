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
        <table style="background-color:white;">
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
            @foreach ($day_shift_checklist as $groupName => $data)
            <tr class="section-header">
                <td colspan="8">{{ $groupName }}</td>
            </tr>
            @foreach ($data as $row)
            @php
            $dataRow = $checklist_data[$row->site_checklist_id] ?? null;
            @endphp
            <tr>
                <td>{{ $row->task_name }}</td>
                <td>
                    @if(isset($dataRow) && $dataRow->sun_bool)
                    <span class="tick">&#10004;</span>
                    @else
                    <span class="cross">&#10008;</span>
                    @endif
                </td>
                <td>
                    @if(isset($dataRow) && $dataRow->mon_bool)
                    <span class="tick">&#10004;</span>
                    @else
                    <span class="cross">&#10008;</span>
                    @endif
                </td>
                <td>
                    @if(isset($dataRow) && $dataRow->tue_bool)
                    <span class="tick">&#10004;</span>
                    @else
                    <span class="cross">&#10008;</span>
                    @endif
                </td>
                <td>
                    @if(isset($dataRow) && $dataRow->wed_bool)
                    <span class="tick">&#10004;</span>
                    @else
                    <span class="cross">&#10008;</span>
                    @endif
                </td>
                <td>
                    @if(isset($dataRow) && $dataRow->thu_bool)
                    <span class="tick">&#10004;</span>
                    @else
                    <span class="cross">&#10008;</span>
                    @endif
                </td>
                <td>
                    @if(isset($dataRow) && $dataRow->fri_bool)
                    <span class="tick">&#10004;</span>
                    @else
                    <span class="cross">&#10008;</span>
                    @endif
                </td>
                <td>
                    @if(isset($dataRow) && $dataRow->sat_bool)
                    <span class="tick">&#10004;</span>
                    @else
                    <span class="cross">&#10008;</span>
                    @endif
                </td>
            </tr>
            @endforeach
            @endforeach

        </table>
    </div>

    <div class="container">
        <table style="background-color:white;">
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
            @foreach ($night_shift_checklist as $groupName => $data)
            <tr class="section-header">
                <td colspan="8">{{ $groupName }}</td>
            </tr>
            @foreach ($data as $row)
            @php
            $dataRow = $checklist_data[$row->site_checklist_id] ?? null;
            @endphp
            <tr>
                <td>{{ $row->task_name }}</td>
                <td>
                    @if(isset($dataRow) && $dataRow->sun_bool)
                    <span class="tick">&#10004;</span>
                    @else
                    <span class="cross">&#10008;</span>
                    @endif
                </td>
                <td>
                    @if(isset($dataRow) && $dataRow->mon_bool)
                    <span class="tick">&#10004;</span>
                    @else
                    <span class="cross">&#10008;</span>
                    @endif
                </td>
                <td>
                    @if(isset($dataRow) && $dataRow->tue_bool)
                    <span class="tick">&#10004;</span>
                    @else
                    <span class="cross">&#10008;</span>
                    @endif
                </td>
                <td>
                    @if(isset($dataRow) && $dataRow->wed_bool)
                    <span class="tick">&#10004;</span>
                    @else
                    <span class="cross">&#10008;</span>
                    @endif
                </td>
                <td>
                    @if(isset($dataRow) && $dataRow->thu_bool)
                    <span class="tick">&#10004;</span>
                    @else
                    <span class="cross">&#10008;</span>
                    @endif
                </td>
                <td>
                    @if(isset($dataRow) && $dataRow->fri_bool)
                    <span class="tick">&#10004;</span>
                    @else
                    <span class="cross">&#10008;</span>
                    @endif
                </td>
                <td>
                    @if(isset($dataRow) && $dataRow->sat_bool)
                    <span class="tick">&#10004;</span>
                    @else
                    <span class="cross">&#10008;</span>
                    @endif
                </td>
            </tr>

            @endforeach
            @endforeach
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