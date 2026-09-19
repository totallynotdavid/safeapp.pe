<div class="settings-nav">
    <x-ui.heading level="2" class="settings-nav__heading">Configuración</x-ui.heading>

    @foreach ($settingsSections as $section)
        <x-ui.list density="spacious" aria-label="{{ $section['title'] }}">
            @foreach ($section['items'] as $item)
                <x-ui.list-item :label="$item['label']" :href="$item['url']" :selected="$item['active']">
                    <x-slot:start><x-ui.icon :name="$item['icon']" /></x-slot:start>
                    <x-slot:end><x-ui.icon name="chevron-right" size="sm" color="secondary" class="settings-nav__mobile-chevron" /></x-slot:end>
                </x-ui.list-item>
            @endforeach
        </x-ui.list>
        @unless ($loop->last)<x-ui.divider />@endunless
    @endforeach
</div>
