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
        <form method="GET" action="{{ route('admin.site.checklist') }}">
            <label for="site_id" class="block text-black font-semibold mb-2">Select Site</label>
            <select name="site_id" id="site_id" onchange="this.form.submit()"
                class="w-full md:w-1/3 p-3 rounded-lg bg-gray-800 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                <option value="">-- Choose Site --</option>
                @foreach ($sites as $site)
                    <option value="{{ $site->id }}" {{ $selectedSiteId == $site->id ? 'selected' : '' }}>
                        {{ $site->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    @if (!$selectedSiteId)
        <div class="container mb-6">
            <p class="text-red-500 text-center font-semibold">Please select a site to view the checklist data.</p>
        </div>
    @endif

    @if ($selectedSiteId)
        <div class="container">
            <!-- <h2 style="text-align:center;">Day Shift Checklist</h2> -->
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
                            $dataRow = DB::table('site_checklist_data')
                                ->where('site_checklist_id', $row->site_checklist_id)
                                ->where('site_id', $selectedSiteId)
                                ->first();
                        @endphp
                        <tr>
                            <td>{{ $row->task_name }}</td>
                            @foreach (['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'] as $day)
                                <td>
                                    @if (isset($dataRow) && $dataRow->{$day . '_bool'})
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
            <!-- <h2 style="text-align:center;">Night Shift Checklist</h2> -->
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
                            $dataRow = DB::table('site_checklist_data')
                                ->where('site_checklist_id', $row->site_checklist_id)
                                ->where('site_id', $selectedSiteId)
                                ->first();
                        @endphp
                        <tr>
                            <td>{{ $row->task_name }}</td>
                            @foreach (['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'] as $day)
                                <td>
                                    @if (isset($dataRow) && $dataRow->{$day . '_bool'})
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
