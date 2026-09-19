@props(['title', 'subtitle' => null, 'backHref' => null, 'backLabel' => null, 'contentWidth' => 1200])

<div class="page-layout layout" data-height="fill" data-content-width="{{ $contentWidth }}">
    <div class="page-layout__inner">
        <header class="layout-header" data-divider="true">
            <div class="layout-header__inner">
                <x-ui.stack direction="vertical" gap="3">
                    <x-ui.stack direction="horizontal" gap="4" v-align="start">
                        <div data-theme="stack-item" data-size="fill">
                            <x-ui.stack direction="vertical" gap="0">
                                <x-ui.heading level="1">{{ $title }}</x-ui.heading>
                                @if ($subtitle || isset($metadata))
                                    <x-ui.stack direction="horizontal" gap="1" v-align="center" wrap="wrap">
                                        @if ($subtitle)<x-ui.text type="body">{{ $subtitle }}</x-ui.text>@endif
                                        @isset($metadata){{ $metadata }}@endisset
                                    </x-ui.stack>
                                @endif
                            </x-ui.stack>
                        </div>
                        @if (($backHref && $backLabel) || isset($actions))
                            <x-ui.stack direction="horizontal" gap="2" v-align="center">
                                @if ($backHref && $backLabel)
                                    <x-ui.icon-button :href="$backHref" label="{{ $backLabel }}" variant="ghost" size="sm">
                                        <x-ui.icon name="arrow-left" size="sm" />
                                    </x-ui.icon-button>
                                @endif
                                @isset($actions){{ $actions }}@endisset
                            </x-ui.stack>
                        @endif
                    </x-ui.stack>
                </x-ui.stack>
            </div>
        </header>
        <div class="page-layout__middle">
            <div class="page-layout__content-slot">
                <main class="layout-content" data-padding="4">
                    <x-ui.stack direction="vertical" gap="4">{{ $slot }}</x-ui.stack>
                </main>
            </div>
        </div>
    </div>
</div>
