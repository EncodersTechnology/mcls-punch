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
                    <td class="py-2 px-4 border-b">{{ $user->site->name }}</td>
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
                <form id="site-form" action="{{ route('sites.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="site-name" class="block text-sm font-medium text-gray-300">Name</label>
                        <input type="text" name="name" id="site-name" class="mt-1 block w-full border-gray-500 text-white bg-gray-800 rounded-md shadow-sm" required>
                    </div>
                    <div class="mb-4">
                        <label for="site-email" class="block text-sm font-medium text-gray-300">Email</label>
                        <input type="email" name="email" id="site-email" class="mt-1 block w-full border-gray-500 text-white bg-gray-800 rounded-md shadow-sm" required>
                    </div>
                    <div class="mb-4">
                        <label for="site-site" class="block text-sm font-medium text-gray-300">Site</label>
                        <select name="site_id" id="site-site" required
                            class="mt-1 block w-full border border-gray-500 text-white bg-gray-800 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="" disabled selected>Select Site</option>
                            @foreach($sites as $site)
                            <option value="{{ $site->id }}">{{ $site->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Save User</button>
                </form>
                <button id="close-site-modal" class="mt-4 text-red-500 hover:text-red-700">Cancel</button>
            </div>
        </div>

    </div>
</x-app-layout>

<script>
    document.getElementById('add-site-btn').addEventListener('click', () => {
        document.getElementById('site-modal').classList.remove('hidden');
    });

    document.getElementById('close-site-modal').addEventListener('click', () => {
            document.getElementById('site-modal').classList.add('hidden');
        });
</script>