@props([
    'id' => null,
    'name',
    'label',
    'description' => null,
    'error' => null,
    'accept' => null,
    'placeholder' => 'Arrastre el archivo aquí o haga clic para buscarlo',
    'required' => false,
    'optional' => false,
    'disabled' => false,
    'mode' => 'dropzone',
])

@php
    $inputId = $id ?? $name;
    $descriptionId = $description ? $inputId.'-desc' : null;
    $statusId = $error ? $inputId.'-status' : null;
    $describedBy = collect([$descriptionId, $statusId])->filter()->implode(' ') ?: null;
@endphp

<div class="field" data-file-input>
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
        <div class="file-input" data-mode="{{ $mode }}" role="button" tabindex="{{ $disabled ? '-1' : '0' }}"
            aria-label="{{ $label }}" @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            @if ($required) aria-required="true" @endif @if ($error) aria-invalid="true" data-status="error" @endif
            @if ($disabled) data-disabled="true" @endif>
            <input id="{{ $inputId }}" name="{{ $name }}" type="file" class="file-input__native"
                @if ($accept) accept="{{ $accept }}" @endif @required($required) @disabled($disabled)
                aria-hidden="true" tabindex="-1" {{ $attributes }}>
            <x-ui.icon name="arrow-up-tray" class="file-input__upload-icon" />
            <span class="file-input__placeholder" data-file-placeholder>{{ $placeholder }}</span>
            <button type="button" class="file-input__clear" data-file-clear
                aria-label="Borrar {{ $label }}" hidden><x-ui.icon name="x-mark" size="sm" /></button>
        </div>
        <div class="visually-hidden" role="status" aria-live="polite" data-file-live></div>
        @if ($error)
            <x-ui.field-status :id="$statusId" type="error" variant="attached">{{ $error }}</x-ui.field-status>
        @endif
    </div>
</div>
