<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center relative w-full">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Welcome to MCLS Document Management System! Please select an option to continue.') }} 
            </h2>
            <div class="flex justify-end items-center gap-3 mb-3">
                <div>
                    <select id="site1" name="site1" class="shadow-sm border-primary rounded-2 px-3 py-2 w-64" required>
                        <option value="">Select an Option</option>
                        <option value="Site A">View Form</option>
                        <option value="Site B">View Latest Log</option>
                    </select>
                    
                </div>
                
                <!-- Tailwind Button Styling -->
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-300">
                    Submit
                </button>
            </div>
        </div>
    </x-slot>
    <link href="https://fonts.googleapis.com/css2?family=Muli&family=Rubik:wght@500&display=swap" rel="stylesheet">

    <div class="container mx-auto">
        <!-- Section 1: Form Section -->
        <div class="section1 gradient-one p-6 rounded-lg shadow-lg overflow-scroll">
            <form id="logForm" class="space-y-4" method="POST" action="{{ route('formdata.store') }}">
                @csrf
                <h2>Please fill out the form below. Note: The fields marked with <span style="color:red">*</span> are required.</h2>
                <label for="employeeType" class="required">Employee Type:</label>
                <select id="employeeType" name="employeeType" required>
                    <option value="">Select Employee Type</option>
                    <option value="mcls">MCLS Employee</option>
                    <option value="agency">Agency Employee</option>
                </select>

                <div id="mclsFields" style="display: none;">
                    <label for="mclsName" class="required">Full Name:</label>
                    <input type="text" id="mclsName" name="mclsName" required placeholder="John Doe">

                    <label for="mclsEmail" class="required">MCLS Email:</label>
                    <input type="email" id="mclsEmail" name="mclsEmail" pattern="^[a-zA-Z0-9._%+-]+@multiculturalcls\.org$" placeholder="name@multiculturalcls.org" required>
                </div>

                <div id="agencyFields" style="display: none;">
                    <label for="agencyName" class="required">Agency Name:</label>
                    <input type="text" id="agencyName" name="agencyName" required placeholder="Agency ABC">

                    <label for="agencyEmployeeName" class="required">Full Name:</label>
                    <input type="text" id="agencyEmployeeName" name="agencyEmployeeName" required placeholder="John Doe">
                </div>

                <label for="site" class="required">Site of Work:</label>
                <select id="site" name="site" required>
                    <option value="">Select Site</option>
                    <option value="Site A">Site A</option>
                    <option value="Site B">Site B</option>
                    <option value="Site C">Site C</option>
                </select>

                <label for="shift" class="required">Select Shift:</label>
                <select id="shift" name="shift" required>
                    <option value="">Select Shift</option>
                    <option value="morning">Morning Shift (8:00 AM to 8:00 PM)</option>
                    <option value="night">Night Shift (8:00 PM to 8:00 AM)</option>
                </select>

                <label for="residentName" class="required">Resident Name:</label>
                <input type="text" id="residentName" name="residentName" required placeholder="John Doe">

                <input type="hidden" id="logDate" name="logDate">
                <input type="hidden" id="logTime" name="logTime">

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

                <button type="submit">Submit Log</button>
            </form>
        </div>

        <!-- Section 2: Table Section -->
        <div class="section2 gradient-two p-6 rounded-lg shadow-lg overflow-auto">
            <div class="mew">
                <h1 class="text-2xl font-semibold mb-4">Latest Log Entry</h1>
                <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-md">
                    <tbody>
                        <tr>
                            <th class="p-4 text-left text-sm font-medium text-gray-700"></th>
                            <th class="p-4 text-left text-sm font-medium text-gray-700"></th>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Employee Type:</td>
                            <td class="p-4 text-sm text-gray-900" id="employeeType">MCLS Employee</td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Full Name:</td>
                            <td class="p-4 text-sm text-gray-900" id="fullName">John Doe</td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Site of Work:</td>
                            <td class="p-4 text-sm text-gray-900" id="site">Site A</td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Shift:</td>
                            <td class="p-4 text-sm text-gray-900" id="shift">Morning Shift</td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Activities of Daily Living:</td>
                            <td class="p-4 text-sm text-gray-900" id="adls">Brushed teeth at 8 AM...</td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Medical and Health Information:</td>
                            <td class="p-4 text-sm text-gray-900" id="medical">Took prescribed medication at 9 AM...</td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Behavior and Emotional Well-being:</td>
                            <td class="p-4 text-sm text-gray-900" id="behavior">KN was happy and engaged during activities...</td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Activities and Engagement:</td>
                            <td class="p-4 text-sm text-gray-900" id="activities">Participated in group art session...</td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Nutritional Intake:</td>
                            <td class="p-4 text-sm text-gray-900" id="nutrition">Ate oatmeal and fruit for breakfast...</td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Sleep Patterns:</td>
                            <td class="p-4 text-sm text-gray-900" id="sleep">Went to bed at 9 PM and woke up at 7 AM...</td>
                        </tr>
                        <tr>
                            <td class="p-4 text-sm font-semibold text-gray-700">Additional Notes:</td>
                            <td class="p-4 text-sm text-gray-900" id="notes">No concerns noted today...</td>
                        </tr>
                    </tbody>
                </table>
                <!-- <button class="mt-6 px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-75 transition-all duration-200" onclick="window.print()">Print Log</button> -->
            </div>
        </div>  
    </div>
    <script>
        document.getElementById("employeeType").addEventListener("change", function() {
            document.getElementById("mclsFields").style.display = this.value === "mcls" ? "block" : "none";
            document.getElementById("agencyFields").style.display = this.value === "agency" ? "block" : "none";
        });

        document.getElementById("logForm").addEventListener("submit", function(event) {
            event.preventDefault();
            document.getElementById("logDate").value = new Date().toISOString().split("T")[0];
            document.getElementById("logTime").value = new Date().toLocaleTimeString();
            
            const formData = new FormData(this);
            const logData = {};
            formData.forEach((value, key) => logData[key] = value);
            
            console.log("Submitted Log:", logData);
            alert("Daily log submitted successfully!");
            this.reset();
        });
    </script>
</x-app-layout>
