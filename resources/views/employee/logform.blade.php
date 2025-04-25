<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center relative w-full">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('This section is for the Site Log Form. Please enter the respective data for the Site.') }}
            </h2>
        </div>
    </x-slot>

    <link href="https://fonts.googleapis.com/css2?family=Muli&family=Rubik:wght@500&display=swap" rel="stylesheet">

    <style>
        #section1 {
            background-size: cover;
            background-position: center;
            position: relative;
        }

        form label {
            font-weight: bold;
        }
    </style>

    <div class="container mx-auto">
        <div id="section1" class="section1 gradient-one p-6 rounded-lg shadow-lg overflow-scroll">
            <div id="errorMessages"></div>

            <form id="logForm" class="space-y-4" method="POST" action="{{ route('formdata.store') }}">
                @csrf

                <h2>Please fill out the form below. Note: The fields marked with <span style="color:red">*</span> are required.</h2>
                <!-- Checklist Type -->
                <div>
                    <label for="site_checklist_id">Checklist Type <span style="color:red">*</span></label>
                    <select name="site_checklist_id" id="site_checklist_id" required class="w-full p-2 border rounded">
                        @foreach ($checklistTypes as $checklist)
                            <option value="{{ $checklist->id }}">{{ $checklist->task_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Days (only show if *_enabled_bool is true) -->
                <div class="grid grid-cols-2 gap-4">
                    @php
                        $dayConfigs = [
                            'sun' => 'Sunday',
                            'mon' => 'Monday',
                            'tue' => 'Tuesday',
                            'wed' => 'Wednesday',
                            'thu' => 'Thursday',
                            'fri' => 'Friday',
                            'sat' => 'Saturday',
                        ];
                    @endphp

                    @foreach ($dayConfigs as $abbr => $dayName)
                        @php $enabledField = "{$abbr}_enabled_bool"; @endphp
                        @if (!empty($siteChecklistSettings) && $siteChecklistSettings->$enabledField)
                            <div>
                                <label for="{{ $abbr }}_bool">
                                    <input type="checkbox" name="{{ $abbr }}_bool" id="{{ $abbr }}_bool" value="1" class="mr-2">
                                    {{ $dayName }}
                                </label>
                            </div>
                        @endif
                    @endforeach
                </div>

                <!-- Temperature Value -->
                <div>
                    <label for="temp_value">Temperature</label>
                    <input type="text" name="temp_value" id="temp_value" class="w-full p-2 border rounded" placeholder="Enter temperature">
                </div>

                <!-- Log Date and Time -->
                <div>
                    <label for="log_date_time">Log Date & Time <span style="color:red">*</span></label>
                    <input type="datetime-local" name="log_date_time" id="log_date_time" required class="w-full p-2 border rounded">
                </div>

                <!-- Submit Button -->
                <div>
                    <button class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-300" type="submit">
                        Submit Log
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
