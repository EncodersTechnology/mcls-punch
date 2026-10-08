@props(['type'])
@php
$map = [
    'admin'      => ['pill-danger',  'Admin'],
    'siteadmin'  => ['pill-warn',    'Site Admin'],
    'director'   => ['pill-violet',  'Director'],
    'manager'    => ['pill-info',    'Manager'],
    'supervisor' => ['pill-brand',   'Supervisor'],
    'employee'   => ['pill-neutral', 'Site User'],
];
[$cls, $label] = $map[$type] ?? ['pill-neutral', ucfirst((string) $type ?: 'Unknown')];
@endphp
<span class="pill {{ $cls }}">{{ $label }}</span>
