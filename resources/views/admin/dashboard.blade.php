<x-app-layout>
<x-slot name="header">
    <div class="w-full">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-6 text-center">
            {{ __('Welcome to MCLS Document Management System! Please select an option to continue.') }}
        </h2>

      <div class="flex justify-center gap-4">
    <button 
        id="chip-form"
        class="chip-option active-chip px-4 py-2 rounded-full text-sm font-medium transition transform hover:scale-105 focus:outline-none"
        data-value="form"
    >
        View Form
    </button>

    <button 
        id="chip-log"
        class="chip-option inactive-chip px-4 py-2 rounded-full text-sm font-medium transition transform hover:scale-105 focus:outline-none"
        data-value="log"
    >
        View Latest Log
    </button>
</div>
    </div>
</x-slot>
    <link href="https://fonts.googleapis.com/css2?family=Muli&family=Rubik:wght@500&display=swap" rel="stylesheet">
    <style>
        #section1, #latest-table {
            background-image: url('{{ asset('blur.png') }}');
            background-size: cover;
            background-position: center;
            position: relative;
        }

        form label {
            font-weight: bold;
        }
        .active-chip {
        background-color: #3B82F6; /* Tailwind's blue-500 */
        color: white;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    }

    .inactive-chip {
        background-color: #E5E7EB; /* Tailwind's gray-200 */
        color: #374151; /* Tailwind's gray-700 */
    }
    </style>
    <div class="container mx-auto">
        <!-- Section 1: Form Section -->
        <div id="section1" class="section1 gradient-one p-6 rounded-lg shadow-lg overflow-scroll">
            <div id="errorMessages"></div>
            <form id="logForm" class="space-y-4" method="POST" action="{{ route('formdata.store') }}">
                @csrf
                <h2>Please fill out the form below. Note: The fields marked with <span style="color:red">*</span> are required.</h2>
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
                    <input type="email" id="mclsEmail" name="mcls_email" pattern="^[a-zA-Z0-9._%+-]+@multiculturalcls\.org$" placeholder="name@multiculturalcls.org" required>
                </div>

                <div id="agencyFields" style="display: none;">
                    <label for="agencyName" class="required">Agency Name:</label>
                    <input type="text" id="agencyName" name="agency_name" required placeholder="Agency ABC">

                    <label for="agencyEmployeeName" class="required">Full Name:</label>
                    <input type="text" id="agencyEmployeeName" name="agency_employee_name" required placeholder="John Doe">
                </div>

                <label for="shift" class="required">Select Shift:</label>
                <select id="shift" name="shift" required>
                    <option value="">Select Shift</option>
                    <option value="morning">Morning Shift (8:00 AM to 8:00 PM)</option>
                    <option value="night">Night Shift (8:00 PM to 8:00 AM)</option>
                </select>

                <label for="site" class="required">Site of Work:</label>
                <select id="site" name="site_id" required class="site_select">
                    <option value="" selected disabled>Select Site</option>
                    @foreach($sites as $site)
                    <option value="{{$site->id}}">{{$site->name}}</option>
                    @endforeach
                </select>


                <label for="resident_select" class="required">Resident:</label>
                <select id="resident_select" name="resident_id" required>
                    <option value="" selected disabled>Select Resident</option>
                </select>

                <input type="hidden" id="logDate" name="log_date">
                <input type="hidden" id="logTime" name="log_time">

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

                <button class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-300" type="submit">Submit Log</button>
            </form>
        </div>

        <!-- Section 2: Table Section -->
        <div id="section2" class="section2 gradient-two p-6 rounded-lg shadow-lg overflow-auto hidden">
            <div class="mew">
                <h1 class="text-2xl font-semibold mb-4">Latest Log Entry</h1>
                <div class="flex gap-4">
                    <div style="display: flex; flex-direction: column;">
                        <label for="filter_site" class="required">Site of Work:</label>
                        <select id="filter_site" name="filter_site_id" required class="filter_site_select">
                            <option value="" selected>All Site</option>
                            @foreach($sites as $site)
                            <option value="{{$site->id}}">{{$site->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display: flex; flex-direction: column;">
                        <label for="filter_resident_select" class="required">Resident:</label>
                        <select id="filter_resident_select" name="filter_resident_id" required>
                            <option value="" selected>All Resident</option>
                        </select>
                    </div>
                </div>
                <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-md" id="latest-table">
                    @if($form_data)
                    <tbody>
                        <tr>
                            <th class="p-4 text-left text-sm font-medium text-gray-700"></th>
                            <th class="p-4 text-left text-sm font-medium text-gray-700"></th>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Employee Type:</td>
                            <td class="p-4 text-sm text-gray-900" id="display_employeeType">{{ $form_data->employee_type }}</td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Full Name:</td>
                            <td class="p-4 text-sm text-gray-900" id="display_fullName">{{ $form_data->mcls_name ? $form_data->mcls_name : $form_data->agency_employee_name }}</td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Site of Work:</td>
                            <td class="p-4 text-sm text-gray-900" id="display_site">{{ $form_data->site->name }}</td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Shift:</td>
                            <td class="p-4 text-sm text-gray-900" id="display_shift">{{ $form_data->shift }}</td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Medical and Health Information:</td>
                            <td class="p-4 text-sm text-gray-900" id="display_medical">{{ $form_data->medical }}</td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Behavior and Emotional Well-being:</td>
                            <td class="p-4 text-sm text-gray-900" id="display_behavior">{{ $form_data->behavior }}</td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Activities and Engagement:</td>
                            <td class="p-4 text-sm text-gray-900" id="display_activities">{{ $form_data->activities }}</td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Nutritional Intake:</td>
                            <td class="p-4 text-sm text-gray-900" id="display_nutrition">{{ $form_data->nutrition }}</td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Sleep Patterns:</td>
                            <td class="p-4 text-sm text-gray-900" id="display_sleep">{{ $form_data->sleep }}</td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Additional Notes:</td>
                            <td class="p-4 text-sm text-gray-900" id="display_notes">{{ $form_data->notes }}</td>
                        </tr>
                    </tbody>
                    @else
                    <tbody>
                        <tr>
                            <td colspan="2" class="p-4 text-center text-gray-700">No log data found.</td>
                        </tr>
                    </tbody>
                    @endif
                </table>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const chipButtons = document.querySelectorAll('.chip-option');
        const formSection = document.getElementById("section1");
        const logSection = document.getElementById("section2");

        chipButtons.forEach(button => {
            button.addEventListener('click', function () {
                chipButtons.forEach(btn => {
                    btn.classList.remove('active-chip');
                    btn.classList.add('inactive-chip');
                });

                this.classList.remove('inactive-chip');
                this.classList.add('active-chip');

                const value = this.getAttribute("data-value");

                if (value === "form") {
                    formSection.classList.remove("hidden");
                    logSection.classList.add("hidden");
                } else if (value === "log") {
                    formSection.classList.add("hidden");
                    logSection.classList.remove("hidden");
                }
            });
        });
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
            document.getElementById("logDate").value = new Date().toISOString().split("T")[0];
            document.getElementById("logTime").value = new Date().toLocaleTimeString();

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

        document.querySelector("select.site_select").addEventListener('change', function() {
            var site_id = this.value;
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '{{ route('get.residents') }}', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                if (xhr.status === 200) {
                    var data = JSON.parse(xhr.responseText);
                    var subcategories = data;
                    var residentSelect = document.getElementById("resident_select");
                    residentSelect.innerHTML = ''; // Clear existing options

                    var defaultOption = document.createElement("option");
                    defaultOption.disabled = true;
                    defaultOption.selected = true;
                    defaultOption.textContent = "Select Resident";
                    residentSelect.appendChild(defaultOption);

                    subcategories.forEach(function(value) {
                        var option = document.createElement("option");
                        option.value = value.id;
                        option.textContent = value.name;
                        residentSelect.appendChild(option);
                    });
                }
            };

            xhr.onerror = function() {
                console.log('Error: ', xhr.statusText);
            };

            xhr.send('site_id=' + encodeURIComponent(site_id) + '&_token=' + encodeURIComponent(csrfToken));
        });

        document.querySelector("select.filter_site_select").addEventListener('change', function() {
            var site_id = this.value;
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '{{ route('get.residents') }}', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                if (xhr.status === 200) {
                    var data = JSON.parse(xhr.responseText);
                    var subcategories = data;
                    var residentSelect = document.getElementById("filter_resident_select");
                    residentSelect.innerHTML = ''; // Clear existing options

                    var defaultOption = document.createElement("option");
                    defaultOption.disabled = true;
                    defaultOption.selected = true;
                    defaultOption.value = "";
                    defaultOption.textContent = "Select Resident";
                    residentSelect.appendChild(defaultOption);

                    subcategories.forEach(function(value) {
                        var option = document.createElement("option");
                        option.value = value.id;
                        option.textContent = value.name;
                        residentSelect.appendChild(option);
                    });
                }
            };

            xhr.onerror = function() {
                console.log('Error: ', xhr.statusText);
            };

            xhr.send('site_id=' + encodeURIComponent(site_id) + '&_token=' + encodeURIComponent(csrfToken));
        });

        document.getElementById('filter_site').addEventListener('change', function () {
            const site_id = this.value;
            const resident_id = document.getElementById('filter_resident_select').value;
            fetchFormData(site_id, resident_id);
        });

        document.getElementById('filter_resident_select').addEventListener('change', function () {
            const resident_id = this.value;
            const site_id = document.getElementById('filter_site').value;
            fetchFormData(site_id, resident_id);
        });

        function fetchFormData(site_id, resident_id) {
            fetch(`/form-data-query?site_id=${site_id}&resident_id=${resident_id}`, {
        method: 'GET',  // or 'POST' depending on your request type
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
            "Content-Type": "application/json"  // Optional, depending on your request
        }
    })
            .then(response => response.json())
            .then(body => {
                if (body.data) {
                    // Assuming body.data contains the returned object with the form data
                    updateTableWithData(body.data);
                } else {
                    // Handle case where no data is returned, e.g., no log data found
                    resetTable();
                }
            })
            .catch(error => {
                console.error('Error fetching form data:', error);
            });
        }

        function updateTableWithData(data) {
            document.getElementById('display_employeeType').textContent = data.employee_type || 'N/A';
            document.getElementById('display_fullName').textContent = data.mcls_name ? data.mcls_name : data.agency_employee_name || 'N/A';
            document.getElementById('display_site').textContent = data.site ? data.site.name : 'N/A';
            document.getElementById('display_shift').textContent = data.shift || 'N/A';
            document.getElementById('display_adls').textContent = data.adls || 'N/A';
            document.getElementById('display_medical').textContent = data.medical || 'N/A';
            document.getElementById('display_behavior').textContent = data.behavior || 'N/A';
            document.getElementById('display_activities').textContent = data.activities || 'N/A';
            document.getElementById('display_nutrition').textContent = data.nutrition || 'N/A';
            document.getElementById('display_sleep').textContent = data.sleep || 'N/A';
            document.getElementById('display_notes').textContent = data.notes || 'N/A';
        }

        function resetTable() {
            document.getElementById('display_employeeType').textContent = 'No data available';
            document.getElementById('display_fullName').textContent = 'No data available';
            document.getElementById('display_site').textContent = 'No data available';
            document.getElementById('display_shift').textContent = 'No data available';
            document.getElementById('display_adls').textContent = 'No data available';
            document.getElementById('display_medical').textContent = 'No data available';
            document.getElementById('display_behavior').textContent = 'No data available';
            document.getElementById('display_activities').textContent = 'No data available';
            document.getElementById('display_nutrition').textContent = 'No data available';
            document.getElementById('display_sleep').textContent = 'No data available';
            document.getElementById('display_notes').textContent = 'No data available';
        }
    </script>
</x-app-layout>