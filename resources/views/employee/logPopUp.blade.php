<!-- components/resident-modal.blade.php -->
<div id="modal-{{ $data->id }}"
    class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white w-11/12 md:w-1/2 lg:w-1/3 rounded-lg shadow-lg p-6 relative">
        <button onclick="closeModal('modal-{{ $data->id }}')"
            class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-lg">&times;</button>
        <h2 class="text-xl font-semibold mb-4 text-center text-blue-600">Resident Log Details</h2>
        <div class="space-y-2 text-sm">
            <p><strong>Employee Type:</strong> {{ $data->employee_type }}</p>
            <p><strong>Resident Name:</strong> {{ $data->mcls_name ? $data->mcls_name : $data->agency_employee_name }}
            </p>
            <p><strong>Site:</strong> {{ $data->site->name }}</p>
            <p><strong>Shift:</strong> {{ $data->shift }}</p>
            <p><strong>Medical:</strong> {{ $data->medical }}</p>
            <p><strong>Behavior:</strong> {{ $data->behavior }}</p>
            <p><strong>Activities:</strong> {{ $data->activities }}</p>
            <p><strong>Nutrition:</strong> {{ $data->nutrition }}</p>
            <p><strong>Sleep:</strong> {{ $data->sleep }}</p>
            <p><strong>Notes:</strong> {{ $data->notes }}</p>
        </div>
    </div>
</div>
