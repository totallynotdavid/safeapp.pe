@props(['title', 'contentWidth' => null])

<div class="page-layout layout" data-height="fill" @if ($contentWidth) data-content-width="{{ $contentWidth }}" @endif>
    <div class="page-layout__inner">
        <header class="layout-header" data-divider="true">
            <div class="layout-header__inner">
                <div class="stack table-page-header" data-direction="horizontal" data-gap="2" data-v-align="center">
                    <div data-theme="stack-item" data-size="fill">
                        <x-ui.heading level="1">{{ $title }}</x-ui.heading>
                    </div>
                    @isset($actions)
                        <div class="stack table-page-header__actions" data-direction="horizontal" data-gap="2" data-v-align="center">
                            {{ $actions }}
                        </div>
                    @endisset
                </div>
            </div>
        </header>

        <div class="page-layout__middle">
            <div class="page-layout__content-slot">
                <main class="layout-content" data-padding="3">
                    <x-ui.stack direction="vertical" gap="4">
                        {{ $slot }}
                    </x-ui.stack>
                </main>
            </div>
        </div>
    </div>
</div>
