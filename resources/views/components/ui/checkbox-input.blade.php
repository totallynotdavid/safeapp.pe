@props([
    'id',
    'name',
    'label',
    'value' => 'on',
    'description' => null,
    'error' => null,
    'checked' => false,
    'disabled' => false,
    'required' => false,
    'optional' => false,
    'size' => 'md',
])

@php
    $descriptionId = $description ? $id.'-desc' : null;
    $statusId = $error ? $id.'-status' : null;
    $describedBy = collect([$descriptionId, $statusId])->filter()->implode(' ') ?: null;
@endphp

<div class="checkbox-input" data-size="{{ $size }}">
    <div class="checkbox-input__row">
        <div class="checkbox-input__control-wrapper">
            <input
                {{ $attributes->class(['checkbox-input__control']) }}
                id="{{ $id }}"
                type="checkbox"
                name="{{ $name }}"
                value="{{ $value }}"
                @checked($checked)
                @disabled($disabled)
                @if ($required) required aria-required="true" @endif
                @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
                @if ($error) aria-invalid="true" @endif
            >
            <div class="checkbox" data-size="{{ $size }}" @if ($checked) data-checked="checked" @endif
                @if ($disabled) data-disabled="disabled" @endif aria-hidden="true">
                <svg class="checkbox__checkmark" viewBox="0 0 10 10">
                    <path d="M8.5 2.5L4 7.5L1.5 5" stroke="currentColor" stroke-width="1.5" fill="none"
                        stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
        </div>
        <div class="checkbox-input__label-wrapper">
            <label class="field-label" for="{{ $id }}" @if ($disabled) data-disabled="true" @endif>
                {{ $label }}
                @if ($optional || $required)
                    <span class="field-label__requirement"><span aria-hidden="true"> ∙ </span>{{ $optional ? 'Opcional' : 'Obligatorio' }}</span>
                @endif
            </label>
            @if ($description)
                <span id="{{ $descriptionId }}" class="field-description">{{ $description }}</span>
            @endif
        </div>
    </div>
    @if ($error)
        <x-ui.field-status :id="$statusId" type="error" variant="detached">{{ $error }}</x-ui.field-status>
    @endif
</div>
