@props([
    'id' => null,
    'name',
    'label',
    'type' => 'text',
    'value' => '',
    'description' => null,
    'error' => null,
    'size' => 'md',
    'required' => false,
    'optional' => false,
    'disabled' => false,
    'readonly' => false,
    'autofocus' => false,
])

@php
    $inputId = $id ?? $name;
    $descriptionId = $description ? $inputId.'-desc' : null;
    $statusId = $error ? $inputId.'-status' : null;
    $describedBy = collect([$descriptionId, $statusId])->filter()->implode(' ') ?: null;
@endphp

<div class="field">
    <label class="field-label" for="{{ $inputId }}" @if ($disabled) data-disabled="true" @endif>
        {{ $label }}
        @if ($optional || $required)
            <span class="field-label__requirement"><span aria-hidden="true"> ∙ </span>{{ $optional ? 'Opcional' : 'Obligatorio' }}</span>
        @endif
    </label>

    @if ($description)
        <span id="{{ $descriptionId }}" class="field-description">
            {{ $description }}
        </span>
    @endif

    <div class="field-input-status-wrapper">
        <div @class(['text-input', 'error' => filled($error)]) data-size="{{ $size }}"
            @if ($error) data-status="error" @endif @if ($disabled) data-disabled="true" @endif>
            <input
                id="{{ $inputId }}"
                name="{{ $name }}"
                type="{{ $type }}"
                value="{{ $value }}"
                @if ($required) required aria-required="true" @endif
                @if ($disabled) disabled @endif
                @if ($readonly) readonly @endif
                @if ($autofocus) autofocus data-autofocus="true" @endif
                @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
                @if ($error) aria-invalid="true" @endif
                {{ $attributes->class(['text-input__control']) }}
            >
            @if ($error)
                <x-ui.icon name="exclamation-circle" class="text-input__status-icon" data-type="error" />
            @endif
        </div>

        @if ($error)
            <x-ui.field-status :id="$statusId" type="error" variant="attached">{{ $error }}</x-ui.field-status>
        @endif
    </div>
</div>
