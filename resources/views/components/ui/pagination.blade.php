@props(['paginator', 'label' => 'Paginación', 'size' => 'md', 'siblingCount' => 1])

@php
    $currentPage = $paginator->currentPage();
    $totalPages = $paginator->lastPage();
    $totalSlots = 5 + (2 * $siblingCount);

    if ($totalPages <= $totalSlots) {
        $pageRange = range(1, $totalPages);
    } else {
        $leftSibling = max($currentPage - $siblingCount, 1);
        $rightSibling = min($currentPage + $siblingCount, $totalPages);
        $showLeftEllipsis = $leftSibling > 2;
        $showRightEllipsis = $rightSibling < $totalPages - 1;

        if (! $showLeftEllipsis && $showRightEllipsis) {
            $leftRange = 3 + (2 * $siblingCount);
            $pageRange = array_merge(range(1, $leftRange), ['…', $totalPages]);
        } elseif ($showLeftEllipsis && ! $showRightEllipsis) {
            $rightRange = 3 + (2 * $siblingCount);
            $pageRange = array_merge([1, '…'], range($totalPages - $rightRange + 1, $totalPages));
        } else {
            $pageRange = array_merge([1, '…'], range($leftSibling, $rightSibling), ['…', $totalPages]);
        }
    }
@endphp

@if ($totalPages > 1)
    <nav {{ $attributes->class('pagination')->merge(['aria-label' => $label, 'data-variant' => 'pages', 'data-size' => $size]) }}>
        <div class="pagination__controls">
            @if ($paginator->onFirstPage())
                <x-ui.button variant="ghost" :size="$size" :icon-only="true" :disabled="true">
                    <x-slot:icon><x-ui.icon name="chevron-left" size="sm" /></x-slot:icon>
                    Ir a la página anterior
                </x-ui.button>
            @else
                <x-ui.button href="{{ $paginator->previousPageUrl() }}" variant="ghost" :size="$size" :icon-only="true" rel="prev">
                    <x-slot:icon><x-ui.icon name="chevron-left" size="sm" /></x-slot:icon>
                    Ir a la página anterior
                </x-ui.button>
            @endif

            @foreach ($pageRange as $item)
                @if ($item === '…')
                    <span class="pagination__ellipsis" @if ($size === 'sm') data-size="sm" @endif aria-hidden="true">…</span>
                @else
                    <x-ui.button
                        href="{{ $paginator->url($item) }}"
                        variant="ghost"
                        :size="$size"
                        class="{{ $item === $currentPage ? 'pagination__active' : '' }}"
                        aria-label="Ir a la página {{ $item }}"
                        aria-current="{{ $item === $currentPage ? 'page' : null }}"
                    >{{ $item }}</x-ui.button>
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <x-ui.button href="{{ $paginator->nextPageUrl() }}" variant="ghost" :size="$size" :icon-only="true" rel="next">
                    <x-slot:icon><x-ui.icon name="chevron-right" size="sm" /></x-slot:icon>
                    Ir a la página siguiente
                </x-ui.button>
            @else
                <x-ui.button variant="ghost" :size="$size" :icon-only="true" :disabled="true">
                    <x-slot:icon><x-ui.icon name="chevron-right" size="sm" /></x-slot:icon>
                    Ir a la página siguiente
                </x-ui.button>
            @endif
        </div>
    </nav>
@endif
