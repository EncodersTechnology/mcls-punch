<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manage Sites and Residents</h2>
    </x-slot>

    <link href="https://fonts.googleapis.com/css2?family=Muli&family=Rubik:wght@500&display=swap" rel="stylesheet">
    <style>
        .active-tab {
            background-color: #3490DC;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
            font-family: 'Rubik', sans-serif;
            text-align: center;
        }
    </style>
    <div class="container mx-auto mt-8">
        @if (session('success'))
        <div class="bg-green-500 text-white p-4 mb-4 rounded">
            {{ session('success') }}
        </div>
        @endif

        <div class="tabs">
            <div class="flex space-x-4 border-b-2 mb-4 pb-3">
                <button id="sites-tab" class="text-lg py-2 px-4 focus:outline-none tab-button active-tab text-white">Sites</button>
                <button id="residents-tab" class="text-lg py-2 px-4 focus:outline-none tab-button text-white">Residents</button>
            </div>

            <!-- Sites Tab -->
            <div id="sites-content" class="tab-content">
                <div class="flex justify-between mb-4">
                    <button id="add-site-btn" class="bg-blue-500 text-white px-4 py-2 rounded">Add Site</button>
                </div>
                <table class="min-w-full bg-gray-800 text-white border border-gray-700">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b text-left">ID</th>
                            <th class="py-2 px-4 border-b text-left">Name</th>
                            <th class="py-2 px-4 border-b text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sites as $site)
                        <tr class="odd:bg-gray-700 even:bg-gray-600">
                            <td class="py-2 px-4 border-b">{{ $site->id }}</td>
                            <td class="py-2 px-4 border-b">{{ $site->name }}</td>
                            <td class="py-2 px-4 border-b">
                                <button data-id="{{ $site->id }}" data-name="{{ $site->name }}" class="edit-site-btn text-blue-400 hover:text-blue-600">Edit</button> |
                                <form action="{{ route('sites.destroy', $site) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>

            <!-- Residents Tab -->
            <div id="residents-content" class="tab-content hidden">
                <div class="flex justify-between mb-4">
                    <button id="add-resident-btn" class="bg-blue-500 text-white px-4 py-2 rounded">Add Resident</button>
                </div>
                <table class="min-w-full bg-gray-800 text-white border border-gray-700">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 text-left">ID</th>
                            <th class="py-2 px-4 text-left">Name</th>
                            <th class="py-2 px-4 text-left">Site</th>
                            <th class="py-2 px-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($residents as $resident)
                        <tr class="odd:bg-gray-700 even:bg-gray-600">
                            <td class="py-2 px-4 border-b">{{ $resident->id }}</td>
                            <td class="py-2 px-4 border-b">{{ $resident->name }}</td>
                            <td class="py-2 px-4 border-b">{{ $resident->site->name }}</td>
                            <td class="py-2 px-4 border-b">
                                <button data-id="{{ $resident->id }}" data-name="{{ $resident->name }}" data-site="{{ $resident->site->id }}" class="edit-resident-btn text-blue-400 hover:text-blue-600">Edit</button> |
                                <form action="{{ route('residents.destroy', $resident) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal for adding/editing Sites -->
        <div id="site-modal" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden flex justify-center items-center">
            <div class="bg-gray-900 p-6 rounded-lg shadow-lg w-1/3">
                <h3 class="text-lg font-semibold mb-4 text-white">Add Site</h3>
                <form id="site-form" action="{{ route('sites.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="site-name" class="block text-sm font-medium text-gray-300">Site Name</label>
                        <input type="text" name="name" id="site-name" class="mt-1 block w-full border-gray-500 text-white bg-gray-800 rounded-md shadow-sm" required>
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Save Site</button>
                </form>
                <button id="close-site-modal" class="mt-4 text-red-500 hover:text-red-700">Cancel</button>
            </div>
        </div>

        <!-- Modal for adding/editing Residents -->
        <div id="resident-modal" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden flex justify-center items-center">
            <div class="bg-gray-900 p-6 rounded-lg shadow-lg w-1/3">
                <h3 class="text-lg font-semibold mb-4 text-white">Add Resident</h3>
                <form id="resident-form" action="{{ route('residents.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="resident-name" class="block text-sm font-medium text-gray-300">Resident Name</label>
                        <input type="text" name="name" id="resident-name" class="mt-1 block w-full border-gray-500 text-white bg-gray-800 rounded-md shadow-sm" required>
                    </div>
                    <div class="mb-4">
                        <label for="site-id" class="block text-sm font-medium text-gray-300">Select Site</label>
                        <select name="site_id" id="site-id" class="mt-1 block w-full border-gray-500 text-white bg-gray-800 rounded-md shadow-sm" required>
                            @foreach ($sites as $site)
                            <option value="{{ $site->id }}">{{ $site->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Save Resident</button>
                </form>
                <button id="close-resident-modal" class="mt-4 text-red-500 hover:text-red-700">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('sites-tab').addEventListener('click', () => {
            document.getElementById('sites-content').classList.remove('hidden');
            document.getElementById('residents-content').classList.add('hidden');
            document.getElementById('sites-tab').classList.add('active-tab');
            document.getElementById('residents-tab').classList.remove('active-tab');
        });
        document.getElementById('residents-tab').addEventListener('click', () => {
            document.getElementById('residents-content').classList.remove('hidden');
            document.getElementById('sites-content').classList.add('hidden');
            document.getElementById('residents-tab').classList.add('active-tab');
            document.getElementById('sites-tab').classList.remove('active-tab');
        });

        document.getElementById('add-site-btn').addEventListener('click', () => {
            document.getElementById('site-modal').classList.remove('hidden');
        });

        document.getElementById('add-resident-btn').addEventListener('click', () => {
            document.getElementById('resident-modal').classList.remove('hidden');
        });

        document.getElementById('close-site-modal').addEventListener('click', () => {
            document.getElementById('site-modal').classList.add('hidden');
        });

        document.getElementById('close-resident-modal').addEventListener('click', () => {
            document.getElementById('resident-modal').classList.add('hidden');
        });

        document.querySelectorAll('.edit-site-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                const siteId = e.target.dataset.id;
                const siteName = e.target.dataset.name;

                document.getElementById('site-name').value = siteName;

                document.getElementById('site-form').action = `/sites/${siteId}`;

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PUT';
                document.getElementById('site-form').appendChild(methodInput);

                document.getElementById('site-modal').classList.remove('hidden');
            });
        });

        document.querySelectorAll('.edit-resident-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                const residentId = e.target.dataset.id;
                const residentName = e.target.dataset.name;
                const siteId = e.target.dataset.site;

                // Set the resident name and site ID in the form inputs
                document.getElementById('resident-name').value = residentName;
                document.getElementById('site-id').value = siteId;

                // Set the form action URL
                document.getElementById('resident-form').action = `/residents/${residentId}`;

                // Add hidden input for the PUT method
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PUT';
                document.getElementById('resident-form').appendChild(methodInput);

                // Show the modal
                document.getElementById('resident-modal').classList.remove('hidden');
            });
        });
    </script>
</x-app-layout>