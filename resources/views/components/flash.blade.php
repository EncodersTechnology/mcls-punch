@if (session('success') || session('error') || $errors->any())
    <div class="mb-5 space-y-3">
        @if (session('success'))<x-alert>{{ session('success') }}</x-alert>@endif
        @if (session('error'))<x-alert type="error">{{ session('error') }}</x-alert>@endif
        @if ($errors->any())
            <x-alert type="error">
                <ul class="space-y-1">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </x-alert>
        @endif
    </div>
@endif
