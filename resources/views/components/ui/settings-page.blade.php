@props(['title' => null, 'description' => null, 'maxWidth' => null])

<x-ui.stack direction="vertical" gap="6" :max-width="$maxWidth">
    @if ($title || isset($actions))
        <x-ui.stack direction="horizontal" h-align="between" v-align="start" gap="4">
            @if ($title)
                <x-ui.stack direction="vertical" gap="1">
                    {{--
                        The settings-sidebar template makes the toolbar title
                        the mobile detail title. Its in-content level-2 heading
                        is desktop-only, so keep this equivalent heading out of
                        the narrow layout rather than rendering it twice.
                    --}}
                    <x-ui.heading level="2" class="settings-page__heading">{{ $title }}</x-ui.heading>
                    @if ($description)<x-ui.text type="body" color="secondary">{{ $description }}</x-ui.text>@endif
                </x-ui.stack>
            @endif
            @isset($actions){{ $actions }}@endisset
        </x-ui.stack>
    @endif

    {{ $slot }}
</x-ui.stack>
