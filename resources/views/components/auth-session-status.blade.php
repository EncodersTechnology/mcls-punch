@props(['status'])
@if ($status)
    <div {{ $attributes->merge(['class' => 'alert alert-success']) }}>
        <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0" />
        <span>{{ $status }}</span>
    </div>
@endif
