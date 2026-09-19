@props([
    'id',
    'label',
    'count',
    'columns',
    'expanded' => true,
])

<tbody class="table-group" data-table-group>
    <tr
        class="table-group__header"
        role="button"
        tabindex="0"
        aria-expanded="{{ $expanded ? 'true' : 'false' }}"
        aria-controls="{{ $id }}"
        data-table-group-toggle
    >
        <td colspan="{{ $columns }}" class="table-group__header-cell">
            <div class="stack" data-direction="horizontal" data-gap="2" data-v-align="center">
                <span class="table-group__chevron" aria-hidden="true">
                    <x-ui.icon name="chevron-down" size="sm" color="secondary" data-table-group-icon="expanded" />
                    <x-ui.icon name="chevron-right" size="sm" color="secondary" data-table-group-icon="collapsed" />
                </span>
                <x-ui.text type="body" weight="bold">{{ $label }}</x-ui.text>
                <x-ui.badge variant="neutral">{{ $count }}</x-ui.badge>
            </div>
        </td>
    </tr>
</tbody>
<tbody id="{{ $id }}" data-table-group-rows @unless($expanded) hidden @endunless>
    {{ $slot }}
</tbody>
