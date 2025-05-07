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
                <!-- Checklist Selects -->
                <div x-data="checklistDropdown()" x-init="init()">
                    <!-- Shift Selector -->
                    <div>
                        <label for="shift_type" class="block text-gray-700 font-medium mb-1">
                            Shift Type <span class="text-red-600">*</span>
                        </label>
                        <select id="shift_type" x-model="selectedShift" class="w-full border border-gray-300 rounded-md p-2 focus:ring focus:ring-blue-300">
                            <option value="" disabled selected>Select Shift</option>
                            <option value="DAY SHIFT CHECKLIST">🌞 Day Shift</option>
                            <option value="NIGHT SHIFT CHECKLIST">🌙 Night Shift</option>
                        </select>
                    </div>

                    <!-- Checklist Selector -->
                    <div class="mt-4">
                        <label for="site_checklist_id" class="block text-gray-700 font-medium mb-1">
                            Checklist Type <span class="text-red-600">*</span>
                        </label>
                        <select name="site_checklist_id" id="site_checklist_id" required
                            class="w-full border border-gray-300 rounded-md p-2 focus:ring focus:ring-blue-300">
                            <option value="" disabled selected>Select Checklist</option>
                            <template x-for="item in filteredChecklists" :key="item.id">
                                <option
                                    :value="item.id"
                                    :data-label="selectedShift"
                                    x-text="(selectedShift === 'DAY SHIFT CHECKLIST' ? '🌞 ' : '🌙 ') + item.task_name">
                                </option>
                            </template>

                        </select>
                    </div>
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
                    <label class="block text-gray-700 font-medium mb-2">
                        Days (Week: {{ $startOfWeek }} to {{ $endOfWeek }})
                    </label>
                    <div class="flex flex-wrap gap-2" id="day-toggle-group">
                        @foreach ($dayConfigs as $abbr => $dayName)
                        <button type="button"
                            data-day="{{ $abbr }}"
                            class="day-toggle px-4 py-2 rounded-full border border-gray-300 bg-gray-100 text-gray-700 hover:bg-blue-100 hover:border-blue-400 transition">
                            {{ $dayName }} <br><small class="text-xs text-gray-600">{{ $weekDates[$abbr] ?? '' }}</small>
                        </button>
                        <input type="hidden" name="{{ $abbr }}_bool" id="{{ $abbr }}_bool" value="0">
                        @endforeach
                    </div>
                </div>

                <!-- Temperature Value -->
                <div>
                    <label for="temp_value" class="block text-gray-700 font-medium mb-1">Temperature</label>
                    <input type="text" name="temp_value" id="temp_value"
                        class="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring focus:ring-blue-300"
                        placeholder="Enter temperature">
                    <p class="text-xs text-blue-500">The value is in °C (Celsius).</p>
                </div>

                <!-- Acknowledge Toggle -->
                <div class="mt-4" id="ack-wrapper" style="display: none;">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <button type="button" id="ack-btn" class="text-gray-700 bg-gray-100 border border-gray-300 px-4 py-2 rounded hover:bg-blue-100">
                            ☐ I acknowledge that I have completed this checklist item
                        </button>
                        <input type="hidden" name="acknowledged" id="acknowledged" value="0">
                    </label>
                    <p class="text-red-600 text-sm mt-1 hidden" id="ack-error">Please acknowledge before submitting.</p>
                </div>






                <!-- Log Date and Time -->
                <!-- <div>
                    <label for="log_date_time" class="block text-gray-700 font-medium mb-1">
                        Log Date & Time <span class="text-red-600">*</span>
                    </label>
                    <input type="datetime-local" name="log_date_time" id="log_date_time" required
                        class="w-full border border-gray-300 rounded-md p-2 focus:ring focus:ring-blue-300">
                </div> -->

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


    function checklistDropdown() {
        return {
            selectedShift: '',
            allChecklists: {},
            filteredChecklists: [],
            init() {
                this.allChecklists = @json($checklistTypes);
                this.$watch('selectedShift', (val) => {
                    this.filteredChecklists = this.allChecklists[val] || [];

                    // Clear selected checklist
                    document.getElementById('site_checklist_id').value = "";
                });
            }
        };
    }


    function getDayAbbrFromDate(date) {
        const abbr = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
        return abbr[date.getDay()];
    }

    function selectDayButton(dayAbbr) {
        const button = document.querySelector(`.day-toggle[data-day="${dayAbbr}"]`);
        const input = document.getElementById(`${dayAbbr}_bool`);

        if (!button || button.disabled) return;

        // Mark it as selected
        input.value = "1";
        button.classList.add("bg-gray-800", "text-white", "border-blue-600");
        button.classList.remove("bg-gray-100", "text-gray-700", "border-gray-300");
    }

    document.getElementById('site_checklist_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const selectedId = parseInt(this.value);
        const tempValuesByDate = @json($tempValuesByDate);
        const tempInput = document.getElementById('temp_value');
        const ackDiv = document.getElementById('ack-wrapper');
        const parentGroup = selectedOption.closest('optgroup');
        // const groupLabel = parentGroup ? parentGroup.label.trim().toUpperCase() : "";
        const groupLabel = selectedOption.getAttribute('data-label')?.trim().toUpperCase() || "";

        if (['🌞 STAFF INITIAL', '🌙 STAFF INITIAL'].includes(selectedOption.text)) {
            ackDiv.style.display = 'block';
        } else {
            ackDiv.style.display = 'none';
        }

        // Reset all days
        const days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
        days.forEach(day => {
            const button = document.querySelector(`.day-toggle[data-day="${day}"]`);
            const input = document.getElementById(`${day}_bool`);
            input.value = "0";

            button.classList.remove("bg-gray-800", "text-white", "border-blue-600");
            button.classList.add("bg-gray-100", "text-gray-700", "border-gray-300");
        });

        // Enable/disable days based on checklist settings
        const setting = checklistSettings.find(item => item.site_checklist_id == selectedId);
        if (setting) {
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
        }

        // Auto-select day based on group label
        const today = new Date();
        let targetDay;
        if (groupLabel === "DAY SHIFT CHECKLIST") {
            targetDay = getDayAbbrFromDate(today);
        } else if (groupLabel === "NIGHT SHIFT CHECKLIST") {
            const yesterday = new Date();
            yesterday.setDate(today.getDate() - 1);
            targetDay = getDayAbbrFromDate(yesterday);
        }

        if (targetDay) {
            selectDayButton(targetDay);
            if (tempValuesByDate[targetDay]) {
                tempInput.value = tempValuesByDate[targetDay]; // first value
                tempInput.readOnly = true;
            } else {
                tempInput.value = '';
                tempInput.readOnly = false;
            }
        }
    });

    document.addEventListener("DOMContentLoaded", function() {
        const buttons = document.querySelectorAll(".day-toggle");
        const tempValuesByDate = @json($tempValuesByDate);
        const tempInput = document.getElementById('temp_value');

        buttons.forEach(button => {
            button.addEventListener("click", function() {
                if (this.disabled) return;
                const day = this.getAttribute("data-day");
                const hiddenInput = document.getElementById(day + "_bool");
                const currentValue = hiddenInput.value;
                const newValue = currentValue === "1" ? "0" : "1";
                hiddenInput.value = newValue;

                // Toggle styling
                if (newValue === "1") {
                    // Handle temp value
                    if (tempValuesByDate[day]) {
                        tempInput.value = tempValuesByDate[day]; // first value
                        tempInput.readOnly = true;
                    } else {
                        tempInput.value = '';
                        tempInput.readOnly = false;
                    }
                    this.classList.add("bg-gray-800", "text-white", "border-blue-600");
                    this.classList.remove("bg-gray-100", "text-gray-700", "border-gray-300");
                } else {
                    tempInput.value = '';
                    tempInput.readOnly = false;
                    this.classList.remove("bg-gray-800", "text-white", "border-blue-600");
                    this.classList.add("bg-gray-100", "text-gray-700", "border-gray-300");
                }
            });
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        const ackBtn = document.getElementById("ack-btn");
        const ackInput = document.getElementById("acknowledged");
        const ackError = document.getElementById("ack-error");

        if (ackBtn) {
            ackBtn.addEventListener("click", function () {
                if (ackInput.value === "0") {
                    ackInput.value = "1";
                    ackBtn.innerHTML = "☑️ I acknowledge that I have completed this checklist item.";
                    ackBtn.classList.remove("bg-gray-100", "text-gray-700");
                    ackBtn.classList.add("bg-blue-600", "text-white");
                    ackError.classList.add("hidden");
                } else {
                    ackInput.value = "0";
                    ackBtn.innerHTML = "☐ I acknowledge that I have completed this checklist item.";
                    ackBtn.classList.remove("bg-blue-600", "text-white");
                    ackBtn.classList.add("bg-gray-100", "text-gray-700");
                }
            });
        }

        // Prevent form submission if acknowledge not checked
        const form = document.getElementById("logForm");
        form.addEventListener("submit", function (e) {
            const ackWrapper = document.getElementById("ack-wrapper");
            if (ackWrapper && ackWrapper.style.display !== "none") {
                if (ackInput.value !== "1") {
                    e.preventDefault();
                    ackError.classList.remove("hidden");
                }
            }
        });
    });
</script>