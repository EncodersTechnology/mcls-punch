<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-center items-center mb-6">
            <div class="flex items-center space-x-4">
                <input type="text" id="searchInput" placeholder="Search..."
                    class="px-4 py-2 border rounded-md w-72 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-300 ease-in-out text-gray-700 placeholder-gray-500">
                <button
                    class="px-6 py-2 bg-gradient-to-r from-green-400 to-blue-500 text-white rounded-md hover:from-blue-500 hover:to-green-400 transition duration-300 ease-in-out"
                    id="exportBtn">Export</button>
            </div>
        </div>
    </x-slot>

    <link href="https://fonts.googleapis.com/css2?family=Muli&family=Rubik:wght@500&display=swap" rel="stylesheet">

    <div class="container mx-auto mt-8 px-6">

        <!-- Demo Table -->
        <div class="overflow-x-auto shadow-lg bg-white rounded-lg">
            <table class="min-w-full bg-white border border-gray-300 text-left rounded-md" id="residentLogTable">
                <thead class="text-white bg-gradient-to-r from-blue-500 to-purple-600">
                    <tr>
                        <th class="py-3 px-6 border-b text-sm font-bold">Employee Type</th>
                        <th class="py-3 px-6 border-b text-sm font-bold">Resident Name</th>
                        <th class="py-3 px-6 border-b text-sm font-bold">Site of Work</th>
                        <th class="py-3 px-6 border-b text-sm font-bold">Shift</th>
                        <th class="py-3 px-6 border-b text-sm font-bold">Medical & Health Info</th>
                        <th class="py-3 px-6 border-b text-sm font-bold">Behavior & Emotional Well-being</th>
                        <th class="py-3 px-6 border-b text-sm font-bold">Activities & Engagement</th>
                        <th class="py-3 px-6 border-b text-sm font-bold">Nutritional Intake</th>
                        <th class="py-3 px-6 border-b text-sm font-bold">Sleep Patterns</th>
                        <th class="py-3 px-6 border-b text-sm font-bold">Additional Notes</th>
                        <th class="py-3 px-6 border-b text-sm font-bold">Action</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @foreach ($datas as $data)
                        <tr class="text-gray-700 hover:bg-gray-100">
                            <td class="py-3 px-6 border-b text-sm">{{ $data->employee_type }}</td>
                            <td class="py-3 px-6 border-b text-sm">
                                {{ $data->mcls_name ? $data->mcls_name : $data->agency_employee_name }}</td>
                            <td class="py-3 px-6 border-b text-sm">{{ $data->site->name }}</td>
                            <td class="py-3 px-6 border-b text-sm">{{ $data->shift }}</td>
                            <td class="py-3 px-6 border-b text-sm">{{ $data->medical }}</td>
                            <td class="py-3 px-6 border-b text-sm">{{ $data->behavior }}</td>
                            <td class="py-3 px-6 border-b text-sm">{{ $data->activities }}</td>
                            <td class="py-3 px-6 border-b text-sm">{{ $data->nutrition }}</td>
                            <td class="py-3 px-6 border-b text-sm">{{ $data->sleep }}</td>
                            <td class="py-3 px-6 border-b text-sm">{{ $data->notes }}</td>
                            <td>
                                <button type="button"
                                    class="bg-blue-500 text-white px-3 py-1 text-sm rounded hover:bg-blue-600"
                                    onclick="openModal('modal-{{ $data->id }}')">View</button>

                                @include('admin.logPopUp', ['data' => $data])
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>

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

        document.getElementById('exportBtn').addEventListener('click', function() {
            const table = document.getElementById('residentLogTable');

            const wb = XLSX.utils.table_to_book(table, {
                sheet: "Resident Log Data"
            });

            const ws = wb.Sheets["Resident Log Data"];

            const range = XLSX.utils.decode_range(ws['!ref']);
            for (let col = range.s.c; col <= range.e.c; col++) {
                const cell_address = {
                    r: 0,
                    c: col
                };
                const cell_ref = XLSX.utils.encode_cell(cell_address);
                if (!ws[cell_ref]) continue;
                ws[cell_ref].s = {
                    font: {
                        bold: true
                    }
                };
            }

            const colWidths = [];
            for (let col = range.s.c; col <= range.e.c; col++) {
                let maxWidth = 0;
                for (let row = range.s.r; row <= range.e.r; row++) {
                    const cell_address = {
                        r: row,
                        c: col
                    };
                    const cell_ref = XLSX.utils.encode_cell(cell_address);
                    const cell = ws[cell_ref];
                    if (cell && cell.v) {
                        maxWidth = Math.max(maxWidth, cell.v.toString().length);
                    }
                }
                colWidths.push({
                    wpx: maxWidth * 10
                });
            }

            ws['!cols'] = colWidths;

            XLSX.writeFile(wb, "resident_log_data.xlsx");
        });

        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }
    </script>
</x-app-layout>
