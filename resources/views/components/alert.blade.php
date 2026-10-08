@props(['type' => 'success'])
<div class="alert {{ $type === 'error' ? 'alert-error' : 'alert-success' }}" role="status">
    <x-icon :name="$type === 'error' ? 'alert' : 'check'" class="mt-0.5 h-4 w-4 shrink-0" />
    <div class="min-w-0">{{ $slot }}</div>
</div>
