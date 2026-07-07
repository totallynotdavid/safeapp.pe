@php
    // Two-level menu tree: section headers (id_ref 0) with their leaves under
    // them. Only leaves are grantable; the controller re-adds parent headers on
    // save so the granted sections actually render in the sidebar.
    $sections = $menus->groupBy('id_ref');
    $checked = collect(old('menus', $selected))->map(static fn ($id): int => (int) $id)->all();
@endphp

<div class="space-y-3">
    @foreach ($sections->get(0) ?? collect() as $header)
        @php $children = $sections->get($header->id_menu) ?? collect(); @endphp
        @if ($children->isNotEmpty())
            <fieldset class="rounded-md border border-gray-200 p-3">
                <legend class="px-1 text-xs font-semibold uppercase tracking-wider text-gray-500">{{ $header->descripcion }}</legend>
                <div class="grid grid-cols-1 gap-1.5 sm:grid-cols-2">
                    @foreach ($children as $leaf)
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="menus[]" value="{{ $leaf->id_menu }}"
                                @checked(in_array($leaf->id_menu, $checked, true))
                                class="rounded border-gray-300">
                            {{ $leaf->descripcion }}
                        </label>
                    @endforeach
                </div>
            </fieldset>
        @endif
    @endforeach
</div>
