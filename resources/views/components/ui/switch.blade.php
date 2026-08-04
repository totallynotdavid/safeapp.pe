{{--
    A Switch represents a boolean that saves immediately. Forms that contain
    one opt into data-preference-form, which turns a change into the
    corresponding PATCH request without introducing a separate Save action.
--}}
@props([
    'id' => null,
    'name',
    'label',
    'description' => null,
    'checked' => false,
    'disabled' => false,
    'labelPosition' => 'end',
    'labelSpacing' => 'hug',
])

@php
    $inputId = $id ?? $name;
    $descriptionId = $description ? $inputId.'-description' : null;
@endphp

<div {{ $attributes->class('switch-field')->merge([
    'data-label-position' => $labelPosition,
    'data-label-spacing' => $labelSpacing,
    'data-switch' => '',
]) }}>
    <div class="switch-field__row">
        @if ($labelPosition === 'start')
            <div class="switch-field__label">
                <label class="field-label" for="{{ $inputId }}" @if ($disabled) data-disabled="true" @endif>{{ $label }}</label>
                @if ($description)<span id="{{ $descriptionId }}" class="field-description">{{ $description }}</span>@endif
            </div>
        @endif

        <span class="switch">
            <input id="{{ $inputId }}" name="{{ $name }}" type="checkbox" role="switch"
                class="switch__input" value="1" @checked($checked) @disabled($disabled)
                @if ($descriptionId) aria-describedby="{{ $descriptionId }}" @endif>
            <span class="switch__track" aria-hidden="true"><span class="switch__thumb"></span></span>
        </span>

        @if ($labelPosition !== 'start')
            <div class="switch-field__label">
                <label class="field-label" for="{{ $inputId }}" @if ($disabled) data-disabled="true" @endif>{{ $label }}</label>
                @if ($description)<span id="{{ $descriptionId }}" class="field-description">{{ $description }}</span>@endif
            </div>
        @endif
    </div>
</div>
