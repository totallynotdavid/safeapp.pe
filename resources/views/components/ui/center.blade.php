@props(['axis' => 'both', 'as' => 'div'])

@if ($as === 'main')
    <main {{ $attributes->class(['center'])->merge(['data-axis' => $axis]) }}>{{ $slot }}</main>
@else
    <div {{ $attributes->class(['center'])->merge(['data-axis' => $axis]) }}>{{ $slot }}</div>
@endif
