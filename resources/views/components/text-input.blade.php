@props(['disabled' => false])
<input {{ $disabled ? 'disabled' : '' }} {{ $attributes->merge(['class' => 'block w-full rounded-lg text-sm disabled:opacity-60']) }}>
