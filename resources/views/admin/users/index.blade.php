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

        {{-- Error Message --}}
        @if (session('error'))
        <div class="bg-red-500 text-white p-4 mb-4 rounded">
            {{ session('error') }}
        </div>
        @endif

        {{-- Validation Errors --}}
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
        </div>
        <table class="min-w-full bg-gray-800 text-white border border-gray-700">
            <thead>
                <tr>
                    <th class="py-2 px-4 border-b text-left">SN</th>
                    <th class="py-2 px-4 border-b text-left">Name</th>
                    <th class="py-2 px-4 border-b text-left">Email</th>
                    <th class="py-2 px-4 border-b text-left">Site</th>
                    <th class="py-2 px-4 border-b text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                @php
                $count = 1;
                @endphp
                @foreach ($users as $user)
                <tr class="odd:bg-gray-700 even:bg-gray-600">
                    <td class="py-2 px-4 border-b">{{ $count }}</td>
                    <td class="py-2 px-4 border-b">{{ $user->name }}</td>
                    <td class="py-2 px-4 border-b">{{ $user->email }}</td>
                    <td class="py-2 px-4 border-b">{{ $user->site ? $user->site->name : ''}}</td>
                    <td class="flex gap-2">
                        <!-- Edit Button -->
                        <button type="button"
                            class="bg-blue-500 text-white px-3 py-1 text-sm rounded hover:bg-blue-600"
                            data-id="{{ $user->id }}" data-name="{{ $user->name }}"
                            data-email="{{ $user->email }}" data-site_id="{{ $user->site->id }}"
                            onclick="openEditModal(this)">
                            Edit
                        </button>

                        <!-- Delete Form -->
                        <form action="{{ route('user.destroy', $user->id) }}" method="POST"
                            onsubmit="return confirm('Are you sure You want to delete this user?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="bg-red-500 text-white px-3 py-1 text-sm rounded hover:bg-red-600">
                                Delete
                            </button>
                        </form>
                    </td>

                </tr>
                @php
                $count++;
                @endphp
                @endforeach
            </tbody>
        </table>

        <!-- Modal for adding/editing Sites -->
        <div id="site-modal" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden flex justify-center items-center">
            <div class="bg-gray-900 p-6 rounded-lg shadow-lg w-1/3">
                <h3 class="text-lg font-semibold mb-4 text-white">Add User</h3>
                <form id="site-form" action="{{ route('user.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="site-name" class="block text-sm font-medium text-gray-300">Name</label>
                        <input type="text" name="name" id="site-name"
                            class="mt-1 block w-full border-gray-500 text-white bg-gray-800 rounded-md shadow-sm"
                            required>
                    </div>
                    <div class="mb-4">
                        <label for="site-email" class="block text-sm font-medium text-gray-300">Email</label>
                        <input type="email" name="email" id="site-email"
                            class="mt-1 block w-full border-gray-500 text-white bg-gray-800 rounded-md shadow-sm"
                            required>
                    </div>
                    <div class="mb-4">
                        <label for="site-password" class="block text-sm font-medium text-gray-300">Password</label>
                        <input type="password" name="password" id="site-password"
                            class="mt-1 block w-full border-gray-500 text-white bg-gray-800 rounded-md shadow-sm"
                            required>
                    </div>
                    <div class="mb-4">
                        <label for="site-password-confirm" class="block text-sm font-medium text-gray-300">Confirm
                            Password</label>
                        <input type="password" name="password_confirmation" id="site-password-confirm"
                            class="mt-1 block w-full border-gray-500 text-white bg-gray-800 rounded-md shadow-sm"
                            required>
                    </div>
                    <div class="mb-4">
                        <label for="site-site" class="block text-sm font-medium text-gray-300">Site</label>
                        <select name="site_id" id="site-site" required
                            class="mt-1 block w-full border border-gray-500 text-white bg-gray-800 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="" disabled selected>Select Site</option>
                            @foreach ($sites as $site)
                            <option value="{{ $site->id }}">{{ $site->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Save
                        User</button>
                </form>
                <button id="close-site-modal" class="mt-4 text-red-500 hover:text-red-700">Cancel</button>
            </div>
        </div>

        @if(count($users) > 0)
        {{-- edit user --}}
        <div id="edit-site-modal"
            class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden flex justify-center items-center">
            <div class="bg-gray-900 p-6 rounded-lg shadow-lg w-1/3">
                <h3 class="text-lg font-semibold mb-4 text-white">Edit User</h3>
                <form id="edit-site-form" action="{{ route('user.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="user-id">
                    <div class="mb-4">
                        <label for="edit-name" class="block text-sm font-medium text-gray-300">Name</label>
                        <input type="text" name="name" id="edit-name"
                            class="mt-1 block w-full border-gray-500 text-white bg-gray-800 rounded-md shadow-sm"
                            required>
                    </div>
                    <div class="mb-4">
                        <label for="edit-email" class="block text-sm font-medium text-gray-300">Email</label>
                        <input type="email" name="email" id="edit-email"
                            class="mt-1 block w-full border-gray-500 text-white bg-gray-800 rounded-md shadow-sm"
                            required>
                    </div>
                    <div class="mb-4">
                        <label for="edit-password" class="block text-sm font-medium text-gray-300">Password</label>
                        <input type="password" name="password" id="edit-password"
                            class="mt-1 block w-full border-gray-500 text-white bg-gray-800 rounded-md shadow-sm">
                    </div>
                    <div class="mb-4">
                        <label for="edit-password-confirm" class="block text-sm font-medium text-gray-300">Confirm
                            Password</label>
                        <input type="password" name="password_confirmation" id="edit-password-confirm"
                            class="mt-1 block w-full border-gray-500 text-white bg-gray-800 rounded-md shadow-sm">
                    </div>
                    <div class="mb-4">
                        <label for="edit-site" class="block text-sm font-medium text-gray-300">Site</label>
                        <select name="site_id" id="edit-site" required
                            class="mt-1 block w-full border border-gray-500 text-white bg-gray-800 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="" disabled>Select Site</option>
                            @foreach ($sites as $site)
                            <option value="{{ $site->id }}" @if ($site->id == old('site_id', $site->site_id)) selected @endif>
                                {{ $site->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Save
                        User</button>
                </form>
                <button id="close-edit-site-modal" class="mt-4 text-red-500 hover:text-red-700"
                    onclick="closeModal()">Cancel</button>
            </div>
        </div>
        @endif
    </div>

    <script>
        document.getElementById('add-site-btn').addEventListener('click', () => {
            document.getElementById('site-modal').classList.remove('hidden');
        });

        document.getElementById('close-site-modal').addEventListener('click', () => {
            document.getElementById('site-modal').classList.add('hidden');
        });

        function openEditModal(button) {
            // Get data from the clicked button
            const userId = button.getAttribute('data-id');
            const userName = button.getAttribute('data-name');
            const userEmail = button.getAttribute('data-email');
            const userSiteId = button.getAttribute('data-site_id');

            // Set values to the modal form fields
            document.getElementById('user-id').value = userId;
            document.getElementById('edit-name').value = userName;
            document.getElementById('edit-email').value = userEmail;
            document.getElementById('edit-site').value = userSiteId;

            // Show the modal
            document.getElementById('edit-site-modal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('edit-site-modal').classList.add('hidden');
        }
    </script>
</x-app-layout>