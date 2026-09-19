@props(['direction' => 'vertical'])

<div {{ $attributes->class(['form-layout'])->merge(['data-direction' => $direction]) }}>
    {{ $slot }}
</div>
