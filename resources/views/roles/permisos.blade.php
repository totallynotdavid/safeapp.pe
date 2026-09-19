@php
    $sections = $menus->groupBy('id_ref');
    $checked = collect(old('menus', $selected))->map(static fn ($id): int => (int) $id)->all();
@endphp

<div class="permission-groups">
    @foreach ($sections->get(0) ?? collect() as $header)
        @php
            $children = $sections->get($header->id_menu) ?? collect();
            $sectionId = 'permission-section-'.$header->id_menu;
        @endphp
        @if ($children->isNotEmpty())
            <section class="form-section" aria-labelledby="{{ $sectionId }}">
                <x-ui.stack direction="vertical" gap="3">
                    <x-ui.heading level="3" :id="$sectionId">{{ $header->descripcion }}</x-ui.heading>
                    <x-ui.grid :min-width="240" gap="3" role="group" :aria-labelledby="$sectionId">
                        @foreach ($children as $leaf)
                            <x-ui.checkbox-input
                                :id="'menu-'.$leaf->id_menu"
                                name="menus[]"
                                :value="$leaf->id_menu"
                                :label="$leaf->descripcion"
                                :checked="in_array($leaf->id_menu, $checked, true)"
                            />
                        @endforeach
                    </x-ui.grid>
                </x-ui.stack>
            </section>
        @endif
    @endforeach

    @error('menus')
        <x-ui.field-status id="menus-status" type="error" variant="detached">{{ $message }}</x-ui.field-status>
    @enderror
</div>
