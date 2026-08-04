@props(['name', 'size' => 'small'])
@php
    $words = preg_split('/\s+/', trim($name)) ?: [];
    $initials = count($words) > 1
        ? mb_strtoupper(mb_substr($words[0], 0, 1).mb_substr($words[array_key_last($words)], 0, 1))
        : mb_strtoupper(mb_substr($words[0] ?? '', 0, 1));
@endphp

<div role="img" aria-label="{{ $name }}"
    {{ $attributes->class('avatar')->merge(['data-size' => $size]) }}>
    <div class="avatar__content">
        <div class="avatar__fallback">{{ $initials }}</div>
    </div>
</div>
