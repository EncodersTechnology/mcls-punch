<!-- components/resident-modal.blade.php -->
<div id="modal-{{ $data->id }}"
    class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white w-11/12 md:w-1/2 lg:w-1/3 rounded-lg shadow-lg p-6 relative">
        <button onclick="closeModal('modal-{{ $data->id }}')"
            class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-lg">&times;</button>
        <div class="modal-content-to-pdf p-4">
            <h2 class="text-xl font-semibold mb-4 text-center text-blue-600">Resident Log Details</h2>
            <div class="space-y-2 text-sm">
                <p><strong>Employee Type:</strong> {{ $data->employee_type }}</p>
                <p><strong>Resident Name:</strong> <span class="resident-name-pdf">{{ $data->mcls_name ? $data->mcls_name : $data->agency_employee_name }}</span></p>
                <p><strong>Site:</strong> {{ $data->site->name }}</p>
                <p><strong>Shift:</strong> {{ $data->shift }}</p>
                <p><strong>Medical:</strong> {{ $data->medical }}</p>
                <p><strong>Behavior:</strong> {{ $data->behavior }}</p>
                <p><strong>Activities:</strong> {{ $data->activities }}</p>
                <p><strong>Nutrition:</strong> {{ $data->nutrition }}</p>
                <p><strong>Sleep:</strong> {{ $data->sleep }}</p>
                <p><strong>Notes:</strong> {{ $data->notes }}</p>
                <p><strong>Date:</strong> <span class="log-date-pdf">{{ $data->log_date }}</span></p>
                <p><strong>Time:</strong> {{ $data->log_time }}</p>
            </div>
        </div>
        <div class="mt-6 flex justify-center space-x-4 no-print">
            <button onclick="exportToPDF('modal-{{ $data->id }}')" 
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                Download PDF
            </button>
            <button onclick="window.print()" 
                class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition">
                Print
            </button>
        </div>
    </div>
</div>
