<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manage Users</h2>
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

        @if (session('error'))
        <div class="bg-red-500 text-white p-4 mb-4 rounded">
            {{ session('error') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="bg-red-500 text-white p-4 mb-4 rounded">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="flex justify-between mb-4">
            <button id="add-site-btn" class="bg-blue-500 text-white px-4 py-2 rounded">Add User</button>
            @if(in_array(auth()->user()->usertype, ['manager', 'director', 'siteadmin', 'admin']))
            <button id="transfer-sites-btn" class="bg-purple-500 text-white px-4 py-2 rounded">Transfer Sites</button>
            @endif
        </div>

        <table class="min-w-full border border-gray-700">
            <thead>
                <tr>
                    <th class="py-2 px-4 border-b text-left">SN</th>
                    <th class="py-2 px-4 border-b text-left">Name</th>
                    <th class="py-2 px-4 border-b text-left">Email</th>
                    <th class="py-2 px-4 border-b text-left">User Type</th>
                    <th class="py-2 px-4 border-b text-left">Manager</th>
                    <th class="py-2 px-4 border-b text-left">Sites</th>
                    <th class="py-2 px-4 border-b text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                @php $count = 1; @endphp
                @foreach ($users as $user)
                <tr>
                    <td class="py-2 px-4 border-b">{{ $count }}</td>
                    <td class="py-2 px-4 border-b">{{ $user->name }}</td>
                    <td class="py-2 px-4 border-b">{{ $user->email }}</td>
                    <td class="py-2 px-4 border-b">
                        <span class="px-2 py-1 text-xs rounded-full 
                            @if($user->usertype == 'director') bg-purple-100 text-purple-800
                            @elseif($user->usertype == 'manager') bg-blue-100 text-blue-800
                            @elseif($user->usertype == 'supervisor') bg-green-100 text-green-800
                            @elseif($user->usertype == 'admin') bg-red-100 text-red-800
                            @elseif($user->usertype == 'siteadmin') bg-orange-100 text-orange-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst($user->usertype) }}
                        </span>
                    </td>
                    <td class="py-2 px-4 border-b">{{ $user->manager ? $user->manager->name : 'N/A' }}</td>
                    <td class="py-2 px-4 border-b">
                        @if($user->sites->isNotEmpty())
                            {{ $user->sites->pluck('name')->implode(', ') }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td class="flex gap-2">
                        <button type="button"
                            class="bg-blue-500 text-white px-3 py-1 text-sm rounded hover:bg-blue-600"
                            data-id="{{ $user->id }}"
                            data-name="{{ $user->name }}"
                            data-email="{{ $user->email }}"
                            data-usertype="{{ $user->usertype }}"
                            data-manager_id="{{ $user->manager_id }}"
                            data-site_id="{{ $user->sites->first() ? $user->sites->first()->id : '' }}"
                            onclick="openEditModal(this)">
                            Edit
                        </button>

                        <form action="{{ route('user.destroy', $user->id) }}" method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this user?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="bg-red-500 text-white px-3 py-1 text-sm rounded hover:bg-red-600">
                                Delete
                            </button>
                        </form>

                        <a class="bg-green-500 text-white px-3 py-1 text-sm rounded hover:bg-green-600" href="{{ route('users.login', $user->id) }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="16" height="16" fill="currentColor">
                                <path d="M497 273L329 441c-15 15-41 4.5-41-17v-96H192c-13.3 0-24-10.7-24-24v-96c0-13.3 10.7-24 24-24h96v-96c0-21.5 25.9-32 41-17l168 168c9.4 9.4 9.4 24.6 0 34zM176 80v48c0 8.8-7.2 16-16 16s-16-7.2-16-16V80c0-8.8-7.2-16-16-16H80v384h48c8.8 0 16 7.2 16 16v48c0 8.8-7.2 16-16 16H64c-35.3 0-64-28.7-64-64V80C0 44.7 28.7 16 64 16h96c8.8 0 16 7.2 16 16z" />
                            </svg>
                        </a>
                    </td>
                </tr>
                @php $count++; @endphp
                @endforeach
            </tbody>
        </table>

        <!-- Add User Modal -->
        <div id="site-modal" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden flex justify-center items-center">
            <div class="bg-white p-6 rounded-lg shadow-lg w-1/3 max-h-screen overflow-y-auto">
                <h3 class="text-lg font-semibold mb-4 text-black">Add User</h3>
                <form id="site-form" action="{{ route('user.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="site-name" class="block text-sm font-medium text-gray-600">Name</label>
                        <input type="text" name="name" id="site-name"
                            class="mt-1 block w-full border-gray-500 rounded-md shadow-sm" required>
                    </div>

                    <div class="mb-4">
                        <label for="site-email" class="block text-sm font-medium text-gray-600">Email</label>
                        <input type="email" name="email" id="site-email"
                            class="mt-1 block w-full border-gray-500 rounded-md shadow-sm" required>
                    </div>

                    <div class="mb-4">
                        <label for="site-password" class="block text-sm font-medium text-gray-600">Password</label>
                        <input type="password" name="password" id="site-password"
                            class="mt-1 block w-full border-gray-500 rounded-md shadow-sm" required>
                    </div>

                    <div class="mb-4">
                        <label for="site-password-confirm" class="block text-sm font-medium text-gray-600">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="site-password-confirm"
                            class="mt-1 block w-full border-gray-500 rounded-md shadow-sm" required>
                    </div>

                    <div class="mb-4">
                        <label for="site-usertype" class="block text-sm font-medium text-gray-600">User Type</label>
                        <select name="usertype" id="site-usertype" required
                            class="mt-1 block w-full border border-gray-500 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="" disabled selected>Select User Type</option>
                            @foreach ($manageableUserTypes as $type)
                            <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4" id="manager-field" style="display: none;">
                        <label for="site-manager" class="block text-sm font-medium text-gray-600">Manager</label>
                        <select name="manager_id" id="site-manager"
                            class="mt-1 block w-full border border-gray-500 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Manager</option>
                            @foreach ($users->whereIn('usertype', ['director', 'manager']) as $manager)
                            <option value="{{ $manager->id }}">{{ $manager->name }} ({{ ucfirst($manager->usertype) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4" id="site-field" style="display: none;">
                        <label for="site-site" class="block text-sm font-medium text-gray-600">Site</label>
                        <select name="site_id" id="site-site"
                            class="mt-1 block w-full border border-gray-500 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="" disabled selected>Select Site</option>
                            @foreach ($sites as $site)
                            <option value="{{ $site->id }}">{{ $site->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Save User</button>
                </form>
                <button id="close-site-modal" class="mt-4 text-red-500 hover:text-red-700">Cancel</button>
            </div>
        </div>

        <!-- Edit User Modal -->
        @if(count($users) > 0)
        <div id="edit-site-modal" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden flex justify-center items-center">
            <div class="bg-white p-6 rounded-lg shadow-lg w-1/3 max-h-screen overflow-y-auto">
                <h3 class="text-lg font-semibold mb-4 text-black">Edit User</h3>
                <form id="edit-site-form" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="user-id">

                    <div class="mb-4">
                        <label for="edit-name" class="block text-sm font-medium text-gray-600">Name</label>
                        <input type="text" name="name" id="edit-name"
                            class="mt-1 block w-full border-gray-500 rounded-md shadow-sm" required>
                    </div>

                    <div class="mb-4">
                        <label for="edit-email" class="block text-sm font-medium text-gray-600">Email</label>
                        <input type="email" name="email" id="edit-email"
                            class="mt-1 block w-full border-gray-500 rounded-md shadow-sm" required>
                    </div>

                    <div class="mb-4">
                        <label for="edit-password" class="block text-sm font-medium text-gray-600">Password</label>
                        <input type="password" name="password" id="edit-password"
                            class="mt-1 block w-full border-gray-500 rounded-md shadow-sm">
                    </div>

                    <div class="mb-4">
                        <label for="edit-password-confirm" class="block text-sm font-medium text-gray-600">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="edit-password-confirm"
                            class="mt-1 block w-full border-gray-500 rounded-md shadow-sm">
                    </div>

                    <div class="mb-4">
                        <label for="edit-usertype" class="block text-sm font-medium text-gray-600">User Type</label>
                        <select name="usertype" id="edit-usertype" required
                            class="mt-1 block w-full border border-gray-500 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @foreach ($manageableUserTypes as $type)
                            <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4" id="edit-manager-field" style="display: none;">
                        <label for="edit-manager" class="block text-sm font-medium text-gray-600">Manager</label>
                        <select name="manager_id" id="edit-manager"
                            class="mt-1 block w-full border border-gray-500 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Manager</option>
                            @foreach ($users->whereIn('usertype', ['director', 'manager']) as $manager)
                            <option value="{{ $manager->id }}">{{ $manager->name }} ({{ ucfirst($manager->usertype) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4" id="edit-site-field" style="display: none;">
                        <label for="edit-site" class="block text-sm font-medium text-gray-600">Site</label>
                        <select name="site_id" id="edit-site"
                            class="mt-1 block w-full border border-gray-500 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Site</option>
                            @foreach ($sites as $site)
                            <option value="{{ $site->id }}">{{ $site->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Save User</button>
                </form>
                <button id="close-edit-site-modal" class="mt-4 text-red-500 hover:text-red-700" onclick="closeModal()">Cancel</button>
            </div>
        </div>
        @endif

        <!-- Transfer Sites Modal -->
        @if(in_array(auth()->user()->usertype, ['manager', 'director', 'siteadmin', 'admin']))
        <div id="transfer-sites-modal" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden flex justify-center items-center">
            <div class="bg-white p-6 rounded-lg shadow-lg w-1/2">
                <h3 class="text-lg font-semibold mb-4 text-black">Transfer Sites Between Supervisors</h3>
                <form action="{{ route('user.transfer-sites') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="from-supervisor" class="block text-sm font-medium text-gray-600">From Supervisor</label>
                        <select name="from_supervisor_id" id="from-supervisor" required
                            class="mt-1 block w-full border border-gray-500 rounded-md shadow-sm">
                            <option value="">Select Supervisor</option>
                            @foreach ($users->where('usertype', 'supervisor') as $supervisor)
                            <option value="{{ $supervisor->id }}">{{ $supervisor->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="to-supervisor" class="block text-sm font-medium text-gray-600">To Supervisor</label>
                        <select name="to_supervisor_id" id="to-supervisor" required
                            class="mt-1 block w-full border border-gray-500 rounded-md shadow-sm">
                            <option value="">Select Supervisor</option>
                            @foreach ($users->where('usertype', 'supervisor') as $supervisor)
                            <option value="{{ $supervisor->id }}">{{ $supervisor->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-600">Sites to Transfer</label>
                        <div id="sites-to-transfer" class="mt-2 max-h-40 overflow-y-auto border border-gray-300 rounded p-2">
                            <!-- Sites will be populated via JavaScript -->
                        </div>
                    </div>

                    <button type="submit" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">Transfer Sites</button>
                </form>
                <button id="close-transfer-modal" class="mt-4 text-red-500 hover:text-red-700">Cancel</button>
            </div>
        </div>
        @endif
    </div>

    <script>
        // Show/hide fields based on user type selection
        function toggleFieldsBasedOnUserType(usertypeSelect, managerField, siteField) {
            const usertype = usertypeSelect.value;
            const managerTypes = ['supervisor', 'manager'];
            const siteTypes = ['supervisor', 'employee'];

            if (managerTypes.includes(usertype)) {
                managerField.style.display = 'block';
            } else {
                managerField.style.display = 'none';
            }

            if (siteTypes.includes(usertype)) {
                siteField.style.display = 'block';
                siteField.querySelector('select').required = true;
            } else {
                siteField.style.display = 'none';
                siteField.querySelector('select').required = false;
            }
        }

        // Add User Modal
        document.getElementById('add-site-btn').addEventListener('click', () => {
            document.getElementById('site-modal').classList.remove('hidden');
        });

        document.getElementById('close-site-modal').addEventListener('click', () => {
            document.getElementById('site-modal').classList.add('hidden');
        });

        document.getElementById('site-usertype').addEventListener('change', function() {
            const managerField = document.getElementById('manager-field');
            const siteField = document.getElementById('site-field');
            toggleFieldsBasedOnUserType(this, managerField, siteField);
        });

        // Edit User Modal
        function openEditModal(button) {
            const userId = button.getAttribute('data-id');
            const userName = button.getAttribute('data-name');
            const userEmail = button.getAttribute('data-email');
            const userType = button.getAttribute('data-usertype');
            const managerId = button.getAttribute('data-manager_id');
            const userSiteId = button.getAttribute('data-site_id');

            document.getElementById('user-id').value = userId;
            document.getElementById('edit-name').value = userName;
            document.getElementById('edit-email').value = userEmail;
            document.getElementById('edit-usertype').value = userType;
            document.getElementById('edit-manager').value = managerId || '';
            document.getElementById('edit-site').value = userSiteId || '';

            // Update form action
            document.getElementById('edit-site-form').action = `/admin/user/update/${userId}`;

            // Toggle fields
            const managerField = document.getElementById('edit-manager-field');
            const siteField = document.getElementById('edit-site-field');
            const usertypeSelect = document.getElementById('edit-usertype');
            toggleFieldsBasedOnUserType(usertypeSelect, managerField, siteField);

            document.getElementById('edit-site-modal').classList.remove('hidden');
        }

        document.getElementById('edit-usertype').addEventListener('change', function() {
            const managerField = document.getElementById('edit-manager-field');
            const siteField = document.getElementById('edit-site-field');
            toggleFieldsBasedOnUserType(this, managerField, siteField);
        });

        function closeModal() {
            document.getElementById('edit-site-modal').classList.add('hidden');
        }

        // Transfer Sites Modal
        document.getElementById('transfer-sites-btn')?.addEventListener('click', () => {
            document.getElementById('transfer-sites-modal').classList.remove('hidden');
        });

        document.getElementById('close-transfer-modal')?.addEventListener('click', () => {
            document.getElementById('transfer-sites-modal').classList.add('hidden');
        });

        // Load sites when "from supervisor" is selected
        document.getElementById('from-supervisor')?.addEventListener('change', function() {
            const supervisorId = this.value;
            if (supervisorId) {
                // Fetch supervisor's sites via AJAX
                fetch(`/admin/user/supervisor-sites/${supervisorId}`)
                    .then(response => response.json())
                    .then(sites => {
                        const sitesContainer = document.getElementById('sites-to-transfer');
                        sitesContainer.innerHTML = '';

                        sites.forEach(site => {
                            const checkbox = document.createElement('div');
                            checkbox.innerHTML = `
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="site_ids[]" value="${site.id}" class="form-checkbox">
                                    <span>${site.name}</span>
                                </label>
                            `;
                            sitesContainer.appendChild(checkbox);
                        });
                    });
            }
        });
    </script>
</x-app-layout>