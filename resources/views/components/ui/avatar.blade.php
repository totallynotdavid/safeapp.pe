@props(['name', 'size' => 'small'])

@php
    $words = preg_split('/\s+/', trim($name)) ?: [];

    if (count($words) > 1) {
        $initials = mb_substr($words[0], 0, 1)
            .mb_substr($words[array_key_last($words)], 0, 1);
    } else {
        $initials = mb_substr($words[0] ?? '', 0, 1);
    }

    $initials = mb_strtoupper($initials);
@endphp

<div
    role="img"
    aria-label="{{ $name }}"
    {{ $attributes->class('avatar')->merge(['data-size' => $size]) }}
>
    <div class="avatar__content">
        <div class="avatar__fallback">{{ $initials }}</div>
    </div>
</div>
