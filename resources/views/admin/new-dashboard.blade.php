<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center relative w-full">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Welcome to MCLS Document Management System! Please select an option to continue.') }}
            </h2>
            <div class="flex justify-end items-center gap-3 mb-3">
                <div>
                    <select id="site1" name="site1" class="shadow-sm border-primary rounded-2 px-3 py-2 w-64"
                        required>
                        <option value="" disabled>Select an Option</option>
                        <option value="form" selected>View Resident Log Form</option>
                        <option value="log">View Latest Log</option>
                    </select>
                </div>

                <!-- Tailwind Button Styling -->
                <!-- <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-300">
                    Submit
                </button> -->
            </div>
        </div>
    </x-slot>
    <link href="https://fonts.googleapis.com/css2?family=Muli&family=Rubik:wght@500&display=swap" rel="stylesheet">
    <style>
        #section1 {
            background-image: url('{{ asset(' blur.png') }}');
            background-size: cover;
            background-position: center;
            position: relative;
        }

        form label {
            font-weight: bold;
        }
    </style>
    <div class="container mx-auto">
        <!-- Section 1: Form Section -->
        <div id="section1" class="section1 gradient-one p-6 rounded-lg shadow-lg overflow-scroll">
            <div id="errorMessages"></div>
            <form id="logForm" class="space-y-4" method="POST" action="{{ route('formdata.store') }}">
                @csrf
                <h2>Please fill out the form below. Note: The fields marked with <span style="color:red">*</span> are
                    required.</h2>
                <label for="employeeType" class="required">Employee Type:</label>
                <select id="employeeType" name="employee_type" required>
                    <option value="">Select Employee Type</option>
                    <option value="mcls">MCLS Employee</option>
                    <option value="agency">Agency Employee</option>
                </select>

                <div id="mclsFields" style="display: none;">
                    <label for="mclsName" class="required">Full Name:</label>
                    <input type="text" id="mclsName" name="mcls_name" required placeholder="John Doe">

                    <label for="mclsEmail" class="required">MCLS Email:</label>
                    <input type="email" id="mclsEmail" name="mcls_email"
                        pattern="^[a-zA-Z0-9._%+-]+@multiculturalcls\.org$" placeholder="name@multiculturalcls.org"
                        required>
                </div>

                <div id="agencyFields" style="display: none;">
                    <label for="agencyName" class="required">Agency Name:</label>
                    <input type="text" id="agencyName" name="agency_name" required placeholder="Agency ABC">

                    <label for="agencyEmployeeName" class="required">Full Name:</label>
                    <input type="text" id="agencyEmployeeName" name="agency_employee_name" required
                        placeholder="John Doe">
                </div>

                <label for="shift" class="required">Date:</label>
                <input type="date" id="logDateTime" name="log_date">


                <label for="shift" class="required">Select Shift:</label>
                <select id="shift" name="shift" required onchange="toggleCustomShift(this)">
                    <option value="">Select Shift</option>
                    <option value="{{ $site ? $site->site->shift_1 : 'morning' }}">
                        {{ $site ? $site->site->shift_1 : 'Morning' }}
                    </option>
                    <option value="{{ $site ? $site->site->shift_2 : 'night' }}">
                        {{ $site ? $site->site->shift_2 : 'Night' }}
                    </option>
                    <option value="custom">Other</option>
                </select>

                <div id="customShiftContainer" style="display:none; margin-top: 10px;">
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="text" id="customShiftName" placeholder="Custom Shift Name" style="flex: 1;">
                        <input type="time" id="customShiftFrom" style="width: 130px;">
                        <input type="time" id="customShiftTo" style="width: 130px;">
                    </div>
                </div>

                <input type="hidden" name="shift" id="shiftFinalValue">



                <input type="hidden" name="site_id" id="site_id" value="{{ $site ? $site->site_id : null }}">
                <label for="resident_select" class="required">Select Resident:</label>
                <select name="resident_id" required>
                    <option value="" selected disabled>Select Resident</option>
                    @foreach ($site_residents as $data)
                    <option value="{{ $data->id }}">{{ $data->name }}</option>
                    @endforeach
                </select>

                <!-- <input type="hidden" id="logDate" name="log_date"> -->
                <!-- <input type="hidden" id="logTime" name="log_time"> -->

                <label for="adls" class="required">Activities of Daily Living:</label>
                <textarea id="adls" name="adls" required placeholder="E.g., Brushed teeth at 8 AM..."></textarea>

                <label for="medical" class="required">Medical and Health Information:</label>
                <textarea id="medical" name="medical" required placeholder="E.g., Took prescribed medication at 9 AM..."></textarea>

                <label for="behavior" class="required">Behavior and Emotional Well-being:</label>
                <textarea id="behavior" name="behavior" required placeholder="E.g., SM was happy and engaged during activities..."></textarea>

                <label for="activities" class="required">Activities and Engagement:</label>
                <textarea id="activities" name="activities" required placeholder="E.g., Participated in group art session..."></textarea>

                <label for="nutrition" class="required">Nutritional Intake:</label>
                <textarea id="nutrition" name="nutrition" required placeholder="E.g., Ate oatmeal and fruit for breakfast..."></textarea>

                <label for="sleep" class="required">Sleep Patterns:</label>
                <textarea id="sleep" name="sleep" required placeholder="E.g., Went to bed at 9 PM and woke up at 7 AM..."></textarea>

                <label for="notes" class="required">Additional Notes:</label>
                <textarea id="notes" name="notes" required placeholder="E.g., No concerns noted today..."></textarea>

                <label for="temperature" class="required">Temperature(°C):</label>
                <input type="text" id="temperature" name="temperature" required></input>

                <button
                    class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-300"
                    type="submit">Submit Log</button>
            </form>
        </div>

        <!-- Section 2: Table Section -->
        <div id="section2" class="section2 gradient-two p-6 rounded-lg shadow-lg overflow-auto hidden">
            <div class="mew">
                <h1 class="text-2xl font-semibold mb-4">Latest Log Entry</h1>
                @if (auth()->user()->usertype == 'admin')
                <div class="flex gap-4">
                    <div style="display: flex; flex-direction: column;">
                        <label for="filter_site" class="required">Site of Work:</label>
                        <select id="filter_site" name="filter_site_id" required class="filter_site_select">
                            <option value="" selected disabled>All Site</option>
                            @foreach ($sites as $site)
                            <option value="{{ $site->id }}">{{ $site->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display: flex; flex-direction: column;">
                        <label for="filter_resident_select" class="required">Resident:</label>
                        <select id="filter_resident_select" name="filter_resident_id" required>
                            <option value="" selected disabled>All Resident</option>
                        </select>
                    </div>
                </div>
                @endif
                <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-md">
                    <tbody>
                        <tr>
                            <th class="p-4 text-left text-sm font-medium text-gray-700"></th>
                            <th class="p-4 text-left text-sm font-medium text-gray-700"></th>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Employee Type:</td>
                            <td class="p-4 text-sm text-gray-900" id="display_employeeType">
                                {{ $form_data ? $form_data->employee_type : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Full Name:</td>
                            <td class="p-4 text-sm text-gray-900" id="display_fullName">
                                {{ $form_data ? ($form_data->mcls_name ? $form_data->mcls_name : $form_data->agency_employee_name) : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Site of Work:</td>
                            <td class="p-4 text-sm text-gray-900" id="display_site">{{ $form_data ? $form_data->site->name : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Shift:</td>
                            <td class="p-4 text-sm text-gray-900" id="display_shift">{{ $form_data ? $form_data->shift : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Activities of Daily Living:
                            </td>
                            <td class="p-4 text-sm text-gray-900" id="display_adls">{{ $form_data ? $form_data->adls : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Medical and Health Information:
                            </td>
                            <td class="p-4 text-sm text-gray-900" id="display_medical">{{ $form_data ? $form_data->medical : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Behavior and Emotional Well-being:
                            </td>
                            <td class="p-4 text-sm text-gray-900" id="display_behavior">
                                {{ $form_data ? $form_data->behavior : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Activities and Engagement:</td>
                            <td class="p-4 text-sm text-gray-900" id="display_activities">
                                {{ $form_data ? $form_data->activities : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Nutritional Intake:</td>
                            <td class="p-4 text-sm text-gray-900" id="display_nutrition">
                                {{ $form_data ? $form_data->nutrition : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Sleep Patterns:</td>
                            <td class="p-4 text-sm text-gray-900" id="display_sleep">{{ $form_data ? $form_data->sleep : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Additional Notes:</td>
                            <td class="p-4 text-sm text-gray-900" id="display_notes">{{ $form_data ? $form_data->notes : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Temperature(°C):</td>
                            <td class="p-4 text-sm text-gray-900" id="display_temperature">{{ $form_data ? $form_data->temperature : '-' }}</td>
                        </tr>
                    </tbody>
                    
                </table>
            </div>
        </div>
    </div>
    <script>
        function toggleCustomShift(select) {
            const container = document.getElementById('customShiftContainer');
            const hiddenInput = document.getElementById('shiftFinalValue');

            if (select.value === 'custom') {
                container.style.display = 'block';
                hiddenInput.disabled = false;
                select.name = 'unused_shift'; // Temporarily disable this select from being submitted
            } else {
                container.style.display = 'none';
                hiddenInput.value = select.value;
                hiddenInput.disabled = false;
                select.name = 'shift';
            }
        }

        document.getElementById('customShiftTo').addEventListener('input', function() {
            const name = document.getElementById('customShiftName').value;
            const from = document.getElementById('customShiftFrom').value;
            const to = document.getElementById('customShiftTo').value;

            if (name && from && to) {
                document.getElementById('shiftFinalValue').value = `${name} (${from} - ${to})`;
            }
        });

        document.getElementById("site1").addEventListener("change", function() {
            const formSection = document.getElementById("section1");
            const logSection = document.getElementById("section2");

            if (this.value === "form") {
                formSection.style.display = "block";
                logSection.style.display = "none";
            } else if (this.value === "log") {
                formSection.style.display = "none";
                logSection.style.display = "block";
            } else {
                formSection.style.display = "none";
                logSection.style.display = "none";
            }
        });

        document.getElementById("employeeType").addEventListener("change", function() {
            let mclsFields = document.getElementById("mclsFields");
            let agencyFields = document.getElementById("agencyFields");

            if (this.value === "mcls") {
                mclsFields.style.display = "block";
                agencyFields.style.display = "none";

                // Enable required for MCLS inputs, disable for Agency inputs
                mclsFields.querySelectorAll("input").forEach(input => input.setAttribute("required", "required"));
                agencyFields.querySelectorAll("input").forEach(input => input.removeAttribute("required"));
            } else if (this.value === "agency") {
                mclsFields.style.display = "none";
                agencyFields.style.display = "block";

                // Enable required for Agency inputs, disable for MCLS inputs
                agencyFields.querySelectorAll("input").forEach(input => input.setAttribute("required", "required"));
                mclsFields.querySelectorAll("input").forEach(input => input.removeAttribute("required"));
            } else {
                mclsFields.style.display = "none";
                agencyFields.style.display = "none";

                // Remove required from all inputs if no selection
                mclsFields.querySelectorAll("input").forEach(input => input.removeAttribute("required"));
                agencyFields.querySelectorAll("input").forEach(input => input.removeAttribute("required"));
            }
        });

        document.getElementById("logForm").addEventListener("submit", function(event) {
            event.preventDefault(); // Prevent default form submission

            // Set logDate and logTime automatically
            // document.getElementById("logDate").value = new Date().toISOString().split("T")[0];
            // document.getElementById("logTime").value = new Date().toLocaleTimeString();

            let formData = new FormData(this);
            let errorContainer = document.getElementById("errorMessages");
            errorContainer.innerHTML = ""; // Clear previous errors

            fetch("{{ route('formdata.store') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                })
                .then(response => response.json().then(data => ({
                    status: response.status,
                    body: data
                })))
                .then(({
                    status,
                    body
                }) => {
                    if (status === 422) {
                        // Display validation errors
                        Object.entries(body.errors).forEach(([key, messages]) => {
                            let errorText = document.createElement("p");
                            errorText.style.color = "red";
                            errorText.textContent = messages.join(" ");
                            errorContainer.appendChild(errorText);
                        });
                    } else if (status === 201) {
                        updateTableWithData(body.data);
                        alert("Form submitted successfully!");
                        document.getElementById("logForm").reset();
                    } else {
                        throw new Error(body.message || "Something went wrong.");
                    }
                })
                .catch(error => {
                    alert(error.message);
                });
        });

        function updateTableWithData(data) {
            document.getElementById('display_employeeType').textContent = data.employee_type || 'N/A';
            document.getElementById('display_fullName').textContent = data.mcls_name ? data.mcls_name : data
                .agency_employee_name || 'N/A';
            document.getElementById('display_site').textContent = data.site ? data.site.name : 'N/A';
            document.getElementById('display_shift').textContent = data.shift || 'N/A';
            document.getElementById('display_adls').textContent = data.adls || 'N/A';
            document.getElementById('display_medical').textContent = data.medical || 'N/A';
            document.getElementById('display_behavior').textContent = data.behavior || 'N/A';
            document.getElementById('display_activities').textContent = data.activities || 'N/A';
            document.getElementById('display_nutrition').textContent = data.nutrition || 'N/A';
            document.getElementById('display_sleep').textContent = data.sleep || 'N/A';
            document.getElementById('display_notes').textContent = data.notes || 'N/A';
            document.getElementById('display_temperature').textContent = data.temperature || 'N/A';
        }
    </script>
</x-app-layout>