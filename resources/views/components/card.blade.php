@props(['title' => null, 'subtitle' => null, 'pad' => true])
<div {{ $attributes->merge(['class' => 'card']) }}>
    @if ($title || isset($actions))
        <div class="card-head">
            <div class="min-w-0">
                @if ($title)<h2 class="truncate text-base font-bold text-ink-900 dark:text-white">{{ $title }}</h2>@endif
                @if ($subtitle)<p class="mt-0.5 text-sm text-ink-500">{{ $subtitle }}</p>@endif
            </div>
            @isset($actions)<div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>@endisset
        </div>
    @endif
    <div class="{{ $pad ? 'card-pad' : '' }}">{{ $slot }}</div>
</div>
