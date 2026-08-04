@props([
    'id' => null,
    'name',
    'label',
    'options' => [],
    'value' => '',
    'placeholder' => 'Seleccionar…',
    'description' => null,
    'error' => null,
    'required' => false,
    'optional' => false,
    'disabled' => false,
    'size' => 'md',
    'autoSubmit' => false,
])

@php
    $inputId = $id ?? $name;
    $triggerId = $inputId.'-trigger';
    $listboxId = $inputId.'-listbox';
    $descriptionId = $description ? $inputId.'-desc' : null;
    $statusId = $error ? $inputId.'-status' : null;
    $describedBy = collect([$descriptionId, $statusId])->filter()->implode(' ') ?: null;
    $normalizeOption = static function ($option): array {
        return is_array($option)
            ? ['value' => (string) $option['value'], 'label' => (string) $option['label'], 'disabled' => (bool) ($option['disabled'] ?? false)]
            : ['value' => (string) $option, 'label' => (string) $option, 'disabled' => false];
    };
    $normalizedOptions = collect($options)->map(static function ($option) use ($normalizeOption): array {
        if (is_array($option) && array_key_exists('options', $option)) {
            return [
                'type' => 'section',
                'title' => (string) ($option['title'] ?? ''),
                'options' => collect($option['options'])->map($normalizeOption)->all(),
            ];
        }

        return ['type' => 'option', ...$normalizeOption($option)];
    });
    $flatOptions = $normalizedOptions->flatMap(static fn (array $item): array => $item['type'] === 'section' ? $item['options'] : [$item]);
    $selected = $flatOptions->first(static fn (array $option): bool => $option['value'] === (string) $value);
    $anchorName = '--selector-'.preg_replace('/[^a-zA-Z0-9_-]/', '-', $inputId);
@endphp

<div class="field" data-selector>
    <label class="field-label" for="{{ $triggerId }}" @if ($disabled) data-disabled="true" @endif>
        {{ $label }}
        @if ($optional || $required)
            <span class="field-label__requirement"><span aria-hidden="true"> ∙ </span>{{ $optional ? 'Opcional' : 'Obligatorio' }}</span>
        @endif
    </label>
    @if ($description)
        <span id="{{ $descriptionId }}" class="field-description">{{ $description }}</span>
    @endif

    <div class="field-input-status-wrapper">
        <div class="selector" data-size="{{ $size }}" data-placeholder="{{ $selected ? 'false' : 'true' }}"
            @if ($error) data-status="error" @endif @if ($disabled) data-disabled="true" @endif
            style="anchor-name: {{ $anchorName }}">
            <button
                id="{{ $triggerId }}"
                type="button"
                role="combobox"
                class="selector__trigger"
                aria-haspopup="listbox"
                aria-expanded="false"
                aria-controls="{{ $listboxId }}"
                @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
                @if ($required) aria-required="true" @endif
                @if ($error) aria-invalid="true" @endif
                @disabled($disabled)
            >
                <span class="selector__label">{{ $selected['label'] ?? $placeholder }}</span>
            </button>
            <input type="hidden" name="{{ $name }}" value="{{ $selected['value'] ?? '' }}"
                @if ($autoSubmit) data-auto-submit @endif @disabled($disabled)>
            <span class="selector__icon" aria-hidden="true"><x-ui.icon name="chevron-down" size="sm" /></span>
        </div>

        <div id="{{ $listboxId }}" class="selector-popover" role="listbox"
            aria-labelledby="{{ $triggerId }}" popover="auto"
            style="position-anchor: {{ $anchorName }}; position-area: self-block-end span-self-inline-end; position-try-fallbacks: flip-block, flip-inline, flip-block flip-inline">
            @php($flatIndex = 0)
            @foreach ($normalizedOptions as $item)
                @if ($item['type'] === 'section')
                    @if ($item['title'] !== '')<x-ui.divider :label="$item['title']" class="selector__section-divider" />@endif
                    <div role="group" @if ($item['title'] !== '') aria-label="{{ $item['title'] }}" @endif>
                        @foreach ($item['options'] as $option)
                            @php($isSelected = $option['value'] === (string) ($selected['value'] ?? ''))
                            <div id="{{ $listboxId }}-option-{{ $flatIndex }}" class="selector__option" role="option"
                                data-value="{{ $option['value'] }}" aria-selected="{{ $isSelected ? 'true' : 'false' }}"
                                @if ($option['disabled']) aria-disabled="true" @endif>
                                <span class="selector__option-content">{{ $option['label'] }}</span>
                                <x-ui.icon name="check" size="sm" class="selector__check" />
                            </div>
                            @php($flatIndex++)
                        @endforeach
                    </div>
                @else
                    @php($isSelected = $item['value'] === (string) ($selected['value'] ?? ''))
                    <div id="{{ $listboxId }}-option-{{ $flatIndex }}" class="selector__option" role="option"
                        data-value="{{ $item['value'] }}" aria-selected="{{ $isSelected ? 'true' : 'false' }}"
                        @if ($item['disabled']) aria-disabled="true" @endif>
                        <span class="selector__option-content">{{ $item['label'] }}</span>
                        <x-ui.icon name="check" size="sm" class="selector__check" />
                    </div>
                    @php($flatIndex++)
                @endif
            @endforeach
        </div>

        @if ($error)
            <x-ui.field-status :id="$statusId" type="error" variant="attached">{{ $error }}</x-ui.field-status>
        @endif
    </div>
</div>
