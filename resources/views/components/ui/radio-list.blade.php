@props([
    'id' => null,
    'name',
    'label',
    'options' => [],
    'value' => '',
    'description' => null,
    'error' => null,
    'orientation' => 'vertical',
    'required' => false,
    'optional' => false,
    'disabled' => false,
    'size' => 'md',
])

@php
    $inputId = $id ?? $name;
    $labelId = $inputId.'-label';
    $descriptionId = $description ? $inputId.'-desc' : null;
    $statusId = $error ? $inputId.'-status' : null;
    $describedBy = collect([$descriptionId, $statusId])->filter()->implode(' ') ?: null;
@endphp

<div class="field">
    <span id="{{ $labelId }}" class="field-label" @if ($disabled) data-disabled="true" @endif>
        {{ $label }}
        @if ($optional || $required)
            <span class="field-label__requirement"><span aria-hidden="true"> ∙ </span>{{ $optional ? 'Opcional' : 'Obligatorio' }}</span>
        @endif
    </span>
    @if ($description)<span id="{{ $descriptionId }}" class="field-description">{{ $description }}</span>@endif

    <div class="radio-list" role="radiogroup" aria-labelledby="{{ $labelId }}"
        data-orientation="{{ $orientation }}" data-size="{{ $size }}"
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        @if ($required) aria-required="true" @endif @if ($error) aria-invalid="true" @endif>
        @foreach ($options as $index => $option)
            @php
                $option = is_array($option) ? $option : ['value' => $option, 'label' => $option];
                $optionId = $inputId.'-option-'.$index;
                $optionDisabled = $disabled || (bool) ($option['disabled'] ?? false);
            @endphp
            <div class="radio-list-item">
                <div class="radio-list-item__item">
                    <div class="radio-list-item__media">
                        <div class="radio-list-item__control-wrapper">
                            <input class="radio-list-item__control" id="{{ $optionId }}" type="radio"
                                name="{{ $name }}" value="{{ $option['value'] }}"
                                @checked((string) $value === (string) $option['value']) @disabled($optionDisabled)
                                @if ($required) required @endif>
                            <div class="radio" aria-hidden="true">
                                <div class="radio-dot"></div>
                            </div>
                        </div>
                    </div>
                    <div class="radio-list-item__content">
                        <label class="radio-list-item__label" for="{{ $optionId }}">{{ $option['label'] }}</label>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if ($error)<x-ui.field-status :id="$statusId" type="error" variant="detached">{{ $error }}</x-ui.field-status>@endif
</div>
