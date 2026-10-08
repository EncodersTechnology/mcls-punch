@props(['icon' => 'inbox', 'title' => 'Nothing here yet', 'message' => null])
<div class="flex flex-col items-center justify-center px-6 py-14 text-center">
    <div class="mb-4 grid h-12 w-12 place-items-center rounded-full bg-ink-100 text-ink-400 dark:bg-ink-800">
        <x-icon :name="$icon" class="h-6 w-6" />
    </div>
    <p class="text-sm font-semibold text-ink-800 dark:text-ink-200">{{ $title }}</p>
    @if ($message)<p class="mt-1 max-w-sm text-sm text-ink-500">{{ $message }}</p>@endif
    @isset($actions)<div class="mt-4">{{ $actions }}</div>@endisset
</div>
