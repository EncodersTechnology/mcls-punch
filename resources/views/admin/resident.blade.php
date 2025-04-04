<x-app-layout>
    <x-slot name="header">

    </x-slot>
    <link href="https://fonts.googleapis.com/css2?family=Muli&family=Rubik:wght@500&display=swap" rel="stylesheet">

    <div class="container mx-auto mt-8">
        <!-- Table Header with Search and Export options -->
        <!-- <div class="flex justify-between items-center mb-4">
            <div class="flex items-center space-x-4">

                <input type="text" id="searchInput" placeholder="Search..." class="px-4 py-2 border rounded-md w-72 focus:outline-none focus:ring-2 focus:ring-blue-500">

                <button class="px-4 py-2 bg-blue-500 text-white rounded-md focus:outline-none hover:bg-blue-600 transition duration-200">Export</button>
            </div>
        </div> -->

        <!-- Demo Table -->
        <div class="shadow-md bg-white">
            <table class="min-w-full bg-white border border-gray-300 text-center">
                <thead class="text-back bg-gray-300">
                    <tr>
                        <th class="py-3 px-4 border-b">Employee Type</th>
                        <th class="py-3 px-4 border-b">Resident Name</th>
                        <th class="py-3 px-4 border-b">Site of Work</th>
                        <th class="py-3 px-4 border-b">Shift</th>
                        <th class="py-3 px-4 border-b">Activities of Daily Living</th>
                        <th class="py-3 px-4 border-b">Medical & Health Info</th>
                        <th class="py-3 px-4 border-b">Behavior & Emotional Well-being</th>
                        <th class="py-3 px-4 border-b">Activities & Engagement</th>
                        <th class="py-3 px-4 border-b">Nutritional Intake</th>
                        <th class="py-3 px-4 border-b">Sleep Patterns</th>
                        <th class="py-3 px-4 border-b">Additional Notes</th>
                        <th class="py-3 px-4 border-b">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr class="text-gray-700">
                        <td class="py-3 px-4 border-b">MCLS Employee</td>
                        <td class="py-3 px-4 border-b">John Doe</td>
                        <td class="py-3 px-4 border-b">Site A</td>
                        <td class="py-3 px-4 border-b">Morning Shift</td>
                        <td class="py-3 px-4 border-b">Brushed teeth at 8 AM</td>
                        <td class="py-3 px-4 border-b">Took prescribed medication at 9 AM</td>
                        <td class="py-3 px-4 border-b">SM was happy and engaged during activities</td>
                        <td class="py-3 px-4 border-b">Participated in group art session</td>
                        <td class="py-3 px-4 border-b">Ate oatmeal and fruit for breakfast</td>
                        <td class="py-3 px-4 border-b">Went to bed at 9 PM and woke up at 7 AM</td>
                        <td class="py-3 px-4 border-b">No concerns noted today</td>
                        <td class="py-3 px-4 border-b">
                            <button class="px-3 py-1 text-white bg-green-500 rounded-md hover:bg-green-600 transition duration-200">View</button>
                            <button class="px-3 py-1 text-white bg-yellow-500 rounded-md hover:bg-yellow-600 transition duration-200">Edit</button>
                            <button class="px-3 py-1 text-white bg-red-500 rounded-md hover:bg-red-600 transition duration-200">Delete</button>
                        </td>
                    </tr>
                    <tr class="text-gray-700">
                        <td class="py-3 px-4 border-b">Agency Employee</td>
                        <td class="py-3 px-4 border-b">John Doe</td>
                        <td class="py-3 px-4 border-b">Site A</td>
                        <td class="py-3 px-4 border-b">Morning Shift</td>
                        <td class="py-3 px-4 border-b">Brushed teeth at 8 AM</td>
                        <td class="py-3 px-4 border-b">Took prescribed medication at 9 AM</td>
                        <td class="py-3 px-4 border-b">SM was happy and engaged during activities</td>
                        <td class="py-3 px-4 border-b">Participated in group art session</td>
                        <td class="py-3 px-4 border-b">Ate oatmeal and fruit for breakfast</td>
                        <td class="py-3 px-4 border-b">Went to bed at 9 PM and woke up at 7 AM</td>
                        <td class="py-3 px-4 border-b">No concerns noted today</td>
                        <td class="py-3 px-4 border-b">
                            <button class="px-3 py-1 text-white bg-green-500 rounded-md hover:bg-green-600 transition duration-200">View</button>
                            <button class="px-3 py-1 text-white bg-yellow-500 rounded-md hover:bg-yellow-600 transition duration-200">Edit</button>
                            <button class="px-3 py-1 text-white bg-red-500 rounded-md hover:bg-red-600 transition duration-200">Delete</button>
                        </td>
                    </tr>
                    <tr class="text-gray-700">
                        <td class="py-3 px-4 border-b">MCLS Employee</td>
                        <td class="py-3 px-4 border-b">John Doe</td>
                        <td class="py-3 px-4 border-b">Site A</td>
                        <td class="py-3 px-4 border-b">Morning Shift</td>
                        <td class="py-3 px-4 border-b">Brushed teeth at 8 AM</td>
                        <td class="py-3 px-4 border-b">Took prescribed medication at 9 AM</td>
                        <td class="py-3 px-4 border-b">SM was happy and engaged during activities</td>
                        <td class="py-3 px-4 border-b">Participated in group art session</td>
                        <td class="py-3 px-4 border-b">Ate oatmeal and fruit for breakfast</td>
                        <td class="py-3 px-4 border-b">Went to bed at 9 PM and woke up at 7 AM</td>
                        <td class="py-3 px-4 border-b">No concerns noted today</td>
                        <td class="py-3 px-4 border-b">
                            <button class="px-3 py-1 text-white bg-green-500 rounded-md hover:bg-green-600 transition duration-200">View</button>
                            <button class="px-3 py-1 text-white bg-yellow-500 rounded-md hover:bg-yellow-600 transition duration-200">Edit</button>
                            <button class="px-3 py-1 text-white bg-red-500 rounded-md hover:bg-red-600 transition duration-200">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <!-- <div class="flex justify-center mt-4">
            <nav aria-label="Table pagination">
                <ul class="inline-flex items-center space-x-4">
                    <li><button class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 transition duration-200">Previous</button></li>
                    <li><button class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 transition duration-200">1</button></li>
                    <li><button class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 transition duration-200">2</button></li>
                    <li><button class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 transition duration-200">3</button></li>
                    <li><button class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 transition duration-200">Next</button></li>
                </ul>
            </nav>
        </div> -->
    </div>

    <script>
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        searchInput.addEventListener('input', function() {
            const query = searchInput.value.toLowerCase();
            const rows = document.querySelectorAll('#tableBody tr');
            rows.forEach(row => {
                const cells = row.getElementsByTagName('td');
                let isMatch = false;
                Array.from(cells).forEach(cell => {
                    if (cell.innerText.toLowerCase().includes(query)) {
                        isMatch = true;
                    }
                });
                row.style.display = isMatch ? '' : 'none';
            });
        });

        // Export functionality (basic placeholder)
        document.querySelector("button.bg-blue-500").addEventListener("click", function() {
            alert("Export feature is coming soon!");
        });
    </script>
</x-app-layout>
