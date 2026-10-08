{{-- The original file wrapped an <img> inside an <svg> with a broken assets() call. --}}
<img src="{{ asset('logo.png') }}" alt="{{ config('app.name') }}" {{ $attributes->merge(['class' => 'h-9 w-auto']) }}>
