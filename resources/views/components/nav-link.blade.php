@props(['active' => false, 'icon' => null])
<a {{ $attributes->merge(['class' => 'navlink ' . ($active ? 'navlink-active' : '')]) }}>
    @if ($icon)<x-icon :name="$icon" />@endif
    <span class="truncate">{{ $slot }}</span>
</a>
