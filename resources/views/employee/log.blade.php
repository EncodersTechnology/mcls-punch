<x-app-layout>
    <x-slot name="header">
        <form method="GET" action="{{ route('employee.log.data') }}" class="flex justify-center items-center mb-6 gap-4 flex-wrap">
            <select style="width: 200px;" name="site_id" id="site-filter" class="w-full px-3 py-2 border rounded-md">
                <option value="" {{  old('site_id', $site_id) == '' ? 'selected' : '' }}>
                    All
                </option>
                @foreach (Auth::user()->getAccessibleSites() as $accessibleSite)
                <option value="{{ $accessibleSite->id }}" {{  old('site_id', $site_id) == $accessibleSite->id ? 'selected' : '' }}>
                    {{ $accessibleSite->name }}
                </option>
                @endforeach
            </select>
            <input type="date" name="from_date" value="{{ old('from_date', $from_date) }}"
                class="px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-gray-700" />
            <input type="date" name="to_date" value="{{ old('to_date', $to_date) }}"
                class="px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-gray-700" />
            <input type="text" name="search" value="{{ old('search', $search) }}" placeholder="Search by name"
                class="px-4 py-2 border rounded-md w-72 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-gray-700 placeholder-gray-500" />
            <button type="submit"
                class="px-6 py-2 bg-gradient-to-r from-green-400 to-blue-500 text-white rounded-md hover:from-blue-500 hover:to-green-400 transition duration-300 ease-in-out">
                Filter
            </button>
            <a href="{{ route('employee.log.data') }}"
                class="px-6 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 transition duration-300 ease-in-out">
                Reset
            </a>
            @if(Auth::user()->usertype != 'employee')
            <button class="px-6 py-2 bg-gradient-to-r from-green-400 to-blue-500 text-white rounded-md hover:from-blue-500 hover:to-green-400 transition" id="exportBtn">
                Export
            </button>
            @endif
        </form>
        @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <ul class="list-disc ml-5">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        @if (session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
        @endif
    </x-slot>

    <link href="https://fonts.googleapis.com/css2?family=Muli&family=Rubik:wght@500&display=swap" rel="stylesheet">

    <div class="container mx-auto mt-8 px-6">
        <div class="overflow-x-auto shadow-lg bg-white rounded-lg">
            <table class="min-w-full bg-white border border-gray-300 text-left rounded-md" id="residentLogTable">
                <thead class="text-white bg-gradient-to-r from-blue-500 to-purple-600">
                    <tr>
                        <th colspan="11">
                            Log data for site(s)
                            @if (is_a($site, 'Illuminate\Database\Eloquent\Collection'))
                            {{ $site->pluck('name')->implode(', ') }}
                            @else
                            {{ $site ? $site->name : 'N/A' }}
                            @endif
                        </th>
                    </tr>
                </thead>
                <thead class="text-white bg-gradient-to-r from-blue-500 to-purple-600">
                    <tr>
                        <th class="py-3 px-6 border-b text-sm font-bold">Employee Type</th>
                        <th class="py-3 px-6 border-b text-sm font-bold">Name</th>
                        <th class="py-3 px-6 border-b text-sm font-bold">Site of Work</th>
                        <th class="py-3 px-6 border-b text-sm font-bold">Resident Name</th>
                        <th class="py-3 px-6 border-b text-sm font-bold">Shift Date</th>
                        <th class="py-3 px-6 border-b text-sm font-bold">Shift</th>
                        <th class="py-3 px-6 border-b text-sm font-bold">Medical & Health Info</th>
                        <th class="py-3 px-6 border-b text-sm font-bold">Behavior & Emotional Well-being</th>
                        <th class="py-3 px-6 border-b text-sm font-bold">Activities & Engagement</th>
                        <th class="py-3 px-6 border-b text-sm font-bold">Nutritional Intake</th>
                        <th class="py-3 px-6 border-b text-sm font-bold">Sleep Patterns</th>
                        <th class="py-3 px-6 border-b text-sm font-bold">Additional Notes</th>
                        <th class="py-3 px-6 border-b text-sm font-bold">Temperature(°C)</th>
                        <th class="py-3 px-6 border-b text-sm font-bold">Entry Time</th>
                        <th class="py-3 px-6 border-b text-sm font-bold">Action</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @foreach ($datas as $data)
                    <tr class="text-gray-700 hover:bg-gray-100">
                        <td class="py-3 px-6 border-b text-sm">{{ $data->employee_type }}</td>
                        <td class="py-3 px-6 border-b text-sm">
                            {{ $data->mcls_name ? $data->mcls_name : $data->agency_employee_name }}
                        </td>
                        <td class="py-3 px-6 border-b text-sm">{{ $data->site->name }}</td>
                        <td class="py-3 px-6 border-b text-sm">{{ $data->resident->name }}</td>
                        <td class="py-3 px-6 border-b text-sm">{{ $data->log_date }}</td>
                        <td class="py-3 px-6 border-b text-sm">{{ $data->shift }}</td>
                        <td class="py-3 px-6 border-b text-sm">{{ $data->medical }}</td>
                        <td class="py-3 px-6 border-b text-sm">{{ $data->behavior }}</td>
                        <td class="py-3 px-6 border-b text-sm">{{ $data->activities }}</td>
                        <td class="py-3 px-6 border-b text-sm">{{ $data->nutrition }}</td>
                        <td class="py-3 px-6 border-b text-sm">{{ $data->sleep }}</td>
                        <td class="py-3 px-6 border-b text-sm">{{ $data->notes }}</td>
                        <td class="py-3 px-6 border-b text-sm">{{ $data->temperature }}</td>
                        <td class="py-3 px-6 border-b text-xs">{{ $data->created_at }}</td>
                        <td>
                            <button type="button"
                                class="bg-blue-500 text-white px-3 py-1 text-sm rounded hover:bg-blue-600"
                                onclick="openModal('modal-{{ $data->id }}')">View</button>
                            <button type="button"
                                class="bg-yellow-500 text-white px-3 py-1 text-sm rounded hover:bg-yellow-600"
                                onclick="openEditModal('edit-modal-{{ $data->id }}')">Edit</button>

                            @include('admin.logPopUp', ['data' => $data])

                            <!-- Edit Modal -->
                            <div id="edit-modal-{{ $data->id }}" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
                                <div class="bg-white p-6 rounded-lg w-full max-w-2xl max-h-[80vh] overflow-y-auto">
                                    <h2 class="text-xl font-bold mb-4">Edit Log Data ({{ ucfirst($data->employee_type) }})</h2>
                                    <form method="POST" action="{{ route('log.update', $data->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Site</label>
                                                <select name="site_id" id="site_id_{{ $data->id }}" class="w-full px-3 py-2 border rounded-md" required>
                                                    @foreach (Auth::user()->getAccessibleSites() as $accessibleSite)
                                                    <option value="{{ $accessibleSite->id }}" {{ $data->site_id == $accessibleSite->id ? 'selected' : '' }}>
                                                        {{ $accessibleSite->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Resident</label>
                                                <select name="resident_id" id="resident_id_{{ $data->id }}" class="w-full px-3 py-2 border rounded-md" required>
                                                    @foreach (App\Models\Resident::where('site_id', $data->site_id)->get() as $resident)
                                                    <option value="{{ $resident->id }}" {{ $data->resident_id == $resident->id ? 'selected' : '' }}>
                                                        {{ $resident->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Employee Type</label>
                                                <select name="employee_type" id="employee_type_{{ $data->id }}" class="w-full px-3 py-2 border rounded-md" required>
                                                    <option value="mcls" {{ $data->employee_type == 'mcls' ? 'selected' : '' }}>MCLS Employee</option>
                                                    <option value="agency" {{ $data->employee_type == 'agency' ? 'selected' : '' }}>Agency Employee</option>
                                                </select>
                                            </div>
                                            <div id="mcls_fields_{{ $data->id }}" class="{{ $data->employee_type == 'mcls' ? '' : 'hidden' }}">
                                                <label class="block text-sm font-medium text-gray-700">MCLS Name</label>
                                                <input type="text" name="mcls_name" value="{{ $data->mcls_name }}" class="w-full px-3 py-2 border rounded-md">
                                                <label class="block text-sm font-medium text-gray-700 mt-2">MCLS Email</label>
                                                <input type="email" name="mcls_email" value="{{ $data->mcls_email ?? '' }}" class="w-full px-3 py-2 border rounded-md">
                                            </div>
                                            <div id="agency_fields_{{ $data->id }}" class="{{ $data->employee_type == 'agency' ? '' : 'hidden' }}">
                                                <label class="block text-sm font-medium text-gray-700">Agency Name</label>
                                                <input type="text" name="agency_name" value="{{ $data->agency_name ?? '' }}" class="w-full px-3 py-2 border rounded-md">
                                                <label class="block text-sm font-medium text-gray-700 mt-2">Agency Employee Name</label>
                                                <input type="text" name="agency_employee_name" value="{{ $data->agency_employee_name }}" class="w-full px-3 py-2 border rounded-md">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Date</label>
                                                <input type="date" name="log_date" value="{{ $data->log_date }}" class="w-full px-3 py-2 border rounded-md" required>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Shift</label>
                                                <input type="text" name="shift" value="{{ $data->shift }}" class="w-full px-3 py-2 border rounded-md" required>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Medical & Health Info</label>
                                                <textarea name="medical" class="w-full px-3 py-2 border rounded-md">{{ $data->medical }}</textarea>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Behavior & Emotional Well-being</label>
                                                <textarea name="behavior" class="w-full px-3 py-2 border rounded-md">{{ $data->behavior }}</textarea>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Activities & Engagement</label>
                                                <textarea name="activities" class="w-full px-3 py-2 border rounded-md">{{ $data->activities }}</textarea>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Nutritional Intake</label>
                                                <textarea name="nutrition" class="w-full px-3 py-2 border rounded-md">{{ $data->nutrition }}</textarea>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Sleep Patterns</label>
                                                <textarea name="sleep" class="w-full px-3 py-2 border rounded-md">{{ $data->sleep }}</textarea>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Additional Notes</label>
                                                <textarea name="notes" class="w-full px-3 py-2 border rounded-md">{{ $data->notes }}</textarea>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Temperature (°C)</label>
                                                <input type="text" name="temperature" value="{{ $data->temperature }}" class="w-full px-3 py-2 border rounded-md">
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Save</button>
                                            <button type="button" onclick="closeModal('edit-modal-{{ $data->id }}')" class="px-4 py-2 bg-gray-400 text-white rounded-md hover:bg-gray-500">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script>
        document.getElementById('exportBtn').addEventListener('click', function() {
            const originalTable = document.getElementById('residentLogTable');
            const clonedTable = originalTable.cloneNode(true);

            // Remove "Action" column from header
            clonedTable.querySelectorAll('thead tr').forEach(tr => {
                tr.removeChild(tr.lastElementChild);
            });

            // Remove "Action" column from body rows
            clonedTable.querySelectorAll('tbody tr').forEach(tr => {
                tr.removeChild(tr.lastElementChild);
            });

            // Export from cleaned table
            const wb = XLSX.utils.table_to_book(clonedTable, {
                sheet: "Resident Log Data"
            });
            const ws = wb.Sheets["Resident Log Data"];
            const range = XLSX.utils.decode_range(ws['!ref']);

            // Make header bold
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

            // Auto column widths
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

            // Save
            XLSX.writeFile(wb, "resident_log_data.xlsx");
        });


        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }

        function openEditModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        // Toggle MCLS/Agency fields based on employee type
        document.querySelectorAll('select[name="employee_type"]').forEach(select => {
            select.addEventListener('change', function() {
                const modalId = this.closest('.fixed').id;
                const mclsFields = document.getElementById(`mcls_fields_${modalId.replace('edit-modal-', '')}`);
                const agencyFields = document.getElementById(`agency_fields_${modalId.replace('edit-modal-', '')}`);
                if (this.value === 'mcls') {
                    mclsFields.classList.remove('hidden');
                    agencyFields.classList.add('hidden');
                } else {
                    mclsFields.classList.add('hidden');
                    agencyFields.classList.remove('hidden');
                }
            });
        });

        // Update resident dropdown based on site selection
        document.querySelectorAll('select[name="site_id"]').forEach(select => {
            select.addEventListener('change', function() {
                const modalId = this.closest('.fixed').id;
                const residentSelect = document.getElementById(`resident_id_${modalId.replace('edit-modal-', '')}`);
                const siteId = this.value;
                if (siteId) {
                    fetch(`/residents?site_id=${siteId}`)
                        .then(response => response.json())
                        .then(data => {
                            residentSelect.innerHTML = '<option value="">Select Resident</option>';
                            data.forEach(resident => {
                                residentSelect.innerHTML += `<option value="${resident.id}">${resident.name}</option>`;
                            });
                        })
                        .catch(error => console.error('Error fetching residents:', error));
                } else {
                    residentSelect.innerHTML = '<option value="">Select Resident</option>';
                }
            });
        });
    </script>
</x-app-layout>