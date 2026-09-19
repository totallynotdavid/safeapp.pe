@props([
    'direction' => 'vertical',
    'hAlign' => null,
    'vAlign' => null,
    'justify' => null,
    'align' => null,
    'gap' => null,
    'padding' => null,
    'paddingInline' => null,
    'paddingBlock' => null,
    'width' => null,
    'height' => null,
    'maxWidth' => null,
    'minHeight' => null,
    'wrap' => null,
    'isScrollable' => false,
    'as' => 'div',
])

@php
    $resolvedHAlign = $hAlign ?? ($direction === 'horizontal' ? $justify : $align);
    $resolvedVAlign = $vAlign ?? ($direction === 'horizontal' ? $align : $justify);
    $resolvedPaddingInline = $paddingInline ?? $padding;
    $resolvedPaddingBlock = $paddingBlock ?? $padding;

    $sizeStyle = collect([
        'width' => $width,
        'height' => $height,
        'max-width' => $maxWidth,
        'min-height' => $minHeight,
    ])->filter(static fn ($value) => $value !== null)
        ->map(static fn ($value, $property) => $property.': '.(is_numeric($value) ? $value.'px' : $value))
        ->implode('; ');
@endphp

<{{ $as }} {{ $attributes->class(['stack'])->merge([
    'data-direction' => $direction,
    'data-h-align' => $resolvedHAlign,
    'data-v-align' => $resolvedVAlign,
    'data-gap' => $gap,
    'data-padding-inline' => $resolvedPaddingInline,
    'data-padding-block' => $resolvedPaddingBlock,
    'data-wrap' => $wrap,
    'data-scrollable' => $isScrollable ? 'true' : null,
    'style' => $sizeStyle !== '' ? $sizeStyle : null,
]) }}>{{ $slot }}</{{ $as }}>
