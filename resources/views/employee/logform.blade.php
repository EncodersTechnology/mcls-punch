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

    <div class="container mx-auto p-6">
        <div class="bg-white rounded-lg shadow-lg p-8">
            @if (session('success'))
            <div class="mb-4 p-4 rounded bg-green-100 text-green-800">
                {{ session('success') }}
            </div>
            @endif
            @if (session('error'))
            <div class="mb-4 p-4 rounded bg-red-100 text-red-800">
                {{ session('error') }}
            </div>
            @endif

            @if ($errors->any())
            <div class="mb-4 p-4 rounded bg-red-100 text-red-800">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form id="logForm" class="space-y-6" method="POST" action="{{ route('sitechecklistdata.store') }}">
                @csrf

                <h2 class="text-xl font-semibold text-gray-800 mb-4">
                    Please fill out the form below. <br>
                    <span class="text-red-600">*</span> fields are required.
                </h2>

                <!-- Checklist Type -->
                <div>
                    <label for="site_checklist_id" class="block text-gray-700 font-medium mb-1">
                        Checklist Type <span class="text-red-600">*</span>
                    </label>
                    <select name="site_checklist_id" id="site_checklist_id" required
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring focus:ring-blue-300">
                        <option value="" disabled selected>Select Checklist</option>
                        @foreach ($checklistTypes as $type => $items)
                        <optgroup label="{{ $type }}">
                            @foreach ($items as $checklist)
                            <option value="{{ $checklist->id }}">{{ $checklist->task_name }}</option>
                            @endforeach
                        </optgroup>
                        @endforeach
                    </select>
                </div>


                <!-- Days (chip-style toggles) -->
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

                <div>
                    <label class="block text-gray-700 font-medium mb-2">Days</label>
                    <div class="flex flex-wrap gap-2" id="day-toggle-group">
                        @foreach ($dayConfigs as $abbr => $dayName)
                        <button type="button"
                            data-day="{{ $abbr }}"
                            class="day-toggle px-4 py-2 rounded-full border border-gray-300 bg-gray-100 text-gray-700 hover:bg-blue-100 hover:border-blue-400 transition">
                            {{ $dayName }}
                        </button>
                        <input type="hidden" name="{{ $abbr }}_bool" id="{{ $abbr }}_bool" value="0">
                        @endforeach
                    </div>
                </div>

                <!-- Temperature Value -->
                <div>
                    <label for="temp_value" class="block text-gray-700 font-medium mb-1">Temperature</label>
                    <input type="text" name="temp_value" id="temp_value"
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring focus:ring-blue-300"
                        placeholder="Enter temperature">
                </div>

                <!-- Log Date and Time -->
                <div>
                    <label for="log_date_time" class="block text-gray-700 font-medium mb-1">
                        Log Date & Time <span class="text-red-600">*</span>
                    </label>
                    <input type="datetime-local" name="log_date_time" id="log_date_time" required
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring focus:ring-blue-300">
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-md shadow transition">
                        Submit Log
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-app-layout>

<script>
    const checklistSettings = @json($siteChecklistSettings);
    document.getElementById('site_checklist_id').addEventListener('change', function() {
        const selectedId = parseInt(this.value);

        // Find the matching setting object
        const setting = checklistSettings.find(item => item.site_checklist_id == selectedId);

        if (!setting) return;

        const days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];

        days.forEach(day => {
            const isEnabled = setting[`${day}_enabled_bool`] == 1;
            const button = document.querySelector(`.day-toggle[data-day="${day}"]`);
            const input = document.getElementById(`${day}_bool`);

            if (isEnabled) {
                button.disabled = false;
                button.classList.remove('opacity-50', 'cursor-not-allowed');
                input.disabled = false;
            } else {
                button.disabled = true;
                input.disabled = true;
                input.value = 0;

                button.classList.remove('bg-blue-500', 'text-white', 'border-blue-600');
                button.classList.add('bg-gray-100', 'text-gray-700', 'border-gray-300', 'opacity-50', 'cursor-not-allowed');
            }
        });
    });

    document.addEventListener("DOMContentLoaded", function() {
        const buttons = document.querySelectorAll(".day-toggle");

        buttons.forEach(button => {
            button.addEventListener("click", function() {
                const day = this.getAttribute("data-day");
                const hiddenInput = document.getElementById(day + "_bool");

                const currentValue = hiddenInput.value;
                const newValue = currentValue === "1" ? "0" : "1";
                hiddenInput.value = newValue;

                // Toggle button styling based on value
                if (newValue === "1") {
                    this.classList.add("bg-gray-800", "text-white", "border-blue-600");
                    this.classList.remove("bg-gray-100", "text-gray-700", "border-gray-300");
                } else {
                    this.classList.remove("bg-gray-800", "text-white", "border-blue-600");
                    this.classList.add("bg-gray-100", "text-gray-700", "border-gray-300");
                }
            });
        });
    });
</script>