<x-app-layout>
    <div id="toast" class="fixed top-5 right-5 z-50 hidden px-4 py-2 rounded shadow-lg text-white text-sm"></div>
    <div class="container mx-auto p-4">
        <form method="GET" action="{{ route('admin.checklist.management') }}" class="mb-6">
            <label class="block text-sm font-medium text-gray-300 mb-1">Select Site</label>
            <select name="site_id" onchange="this.form.submit()" class="block w-full bg-gray-800 text-white border border-gray-500 rounded-md p-2">
                <option value="" disabled selected>Select a site</option>
                @foreach($sites as $site)
                <option value="{{ $site->id }}" @if($selectedSiteId==$site->id) selected @endif>{{ $site->name }}</option>
                @endforeach
            </select>
        </form>

        @if($selectedSiteId)
        @foreach (['DAY' => $day_shift_checklist, 'NIGHT' => $night_shift_checklist] as $shift => $checklists)
        <div class="mb-8">
            <h2 class="text-xl font-bold text-white mb-2">{{ $shift }} SHIFT CHECKLIST</h2>
            <table class="w-full bg-white text-black rounded shadow overflow-hidden">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-2 py-1 text-left">Task</th>
                        @foreach(['sun','mon','tue','wed','thu','fri','sat'] as $day)
                        <th class="px-2 py-1 uppercase">{{ strtoupper($day) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($checklists as $groupName => $rows)
                    <tr class="bg-gray-200">
                        <td colspan="8" class="font-bold px-2 py-1">{{ $groupName }}</td>
                    </tr>
                    @foreach ($rows as $row)
                    @php
                    $setting = \App\Models\SiteChecklistSetting::where('site_id', $selectedSiteId)
                    ->where('site_checklist_id', $row->id)
                    ->first();
                    @endphp
                    <tr>
                        <td class="px-2 py-1">{{ $row->task_name }}</td>
                        @foreach(['sun','mon','tue','wed','thu','fri','sat'] as $day)
                        <td class="text-center">
                            <input type="checkbox"
                                data-task-id="{{ $row->id }}"
                                data-day="{{ $day }}"
                                class="toggle-checkbox"
                                @if(optional($setting)->{$day.'_enabled_bool'}) checked @endif
                            >
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.toggle-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', async (e) => {
                    const taskId = e.target.dataset.taskId;
                    const day = e.target.dataset.day;
                    const isChecked = e.target.checked;
                    const siteId = "{{ $selectedSiteId }}";

                    @php
                    $toggleUrl = route('admin.settings.toggle');
                    if (app() -> environment('production')) {
                        $toggleUrl = str_replace('http://', 'https://', $toggleUrl);
                    }
                    @endphp

                    await fetch("{{ $toggleUrl }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                site_id: siteId,
                                task_id: taskId,
                                day: day,
                                enabled: isChecked
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                showToast("Setting saved successfully", "success");
                            } else {
                                showToast("Failed to save setting", "error");
                            }
                        })
                        .catch(() => {
                            showToast("An error occurred. Please try again.", "error");
                        });

                });
            });
        });

        function showToast(message, type = "success") {
            const toast = document.getElementById('toast');
            toast.className = `fixed top-5 right-5 z-50 px-4 py-2 rounded shadow-lg text-white text-sm ${
            type === "success" ? "bg-green-600" : "bg-red-600"
        }`;
            toast.textContent = message;
            toast.style.display = 'block';

            setTimeout(() => {
                toast.style.display = 'none';
            }, 3000);
        }
    </script>
</x-app-layout>