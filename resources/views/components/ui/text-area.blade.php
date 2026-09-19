@props([
    'id' => null,
    'name',
    'label',
    'value' => '',
    'description' => null,
    'error' => null,
    'rows' => 3,
    'required' => false,
    'optional' => false,
    'disabled' => false,
    'maxLength' => null,
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
        <span id="{{ $descriptionId }}" class="field-description">{{ $description }}</span>
    @endif
    <div class="field-input-status-wrapper">
        <div class="text-area" @if ($error) data-status="error" @endif @if ($disabled) data-disabled="true" @endif>
            <textarea id="{{ $inputId }}" name="{{ $name }}" rows="{{ $rows }}"
                class="text-area__control" @required($required) @disabled($disabled)
                @if ($maxLength) maxlength="{{ $maxLength }}" @endif
                @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
                @if ($error) aria-invalid="true" @endif {{ $attributes }}>{{ $value }}</textarea>
            @if ($error)
                <x-ui.icon name="exclamation-circle" class="text-area__status-icon" />
            @endif
        </div>
        @if ($error)
            <x-ui.field-status :id="$statusId" type="error" variant="attached">{{ $error }}</x-ui.field-status>
        @endif
    </div>
</div>
