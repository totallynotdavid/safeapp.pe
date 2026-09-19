@props([
    'density' => 'balanced',
    'dividers' => false,
])

<ul {{ $attributes->class(['list'])->merge([
    'role' => 'list',
    'data-density' => $density,
    'data-dividers' => $dividers ? 'true' : null,
]) }}>
    {{ $slot }}
</ul>
