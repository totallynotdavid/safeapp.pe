@props(['level' => 2])

@switch((int) $level)
    @case(1)
        <h1 {{ $attributes->class(['heading'])->merge(['data-level' => '1']) }}>{{ $slot }}</h1>
        @break
    @case(2)
        <h2 {{ $attributes->class(['heading'])->merge(['data-level' => '2']) }}>{{ $slot }}</h2>
        @break
    @case(3)
        <h3 {{ $attributes->class(['heading'])->merge(['data-level' => '3']) }}>{{ $slot }}</h3>
        @break
    @default
        <h4 {{ $attributes->class(['heading'])->merge(['data-level' => '4']) }}>{{ $slot }}</h4>
@endswitch
