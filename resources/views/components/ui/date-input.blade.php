@props([
    'id' => null,
    'name',
    'label',
    'value' => '',
    'description' => null,
    'error' => null,
    'size' => 'md',
    'required' => false,
    'optional' => false,
    'disabled' => false,
    'min' => null,
    'max' => null,
    'hasClear' => false,
])

@php
    $inputId = $id ?? $name;
    $calendarId = $inputId.'-calendar';
    $descriptionId = $description ? $inputId.'-desc' : null;
    $statusId = $error ? $inputId.'-status' : null;
    $anchorName = '--date-'.preg_replace('/[^a-zA-Z0-9_-]/', '-', $inputId);
    $describedBy = collect([$descriptionId, $statusId])->filter()->implode(' ') ?: null;
    $isoValue = filled($value) ? (string) $value : '';
    $displayValue = '';

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $isoValue)) {
        try {
            $displayValue = \Carbon\CarbonImmutable::createFromFormat('Y-m-d', $isoValue)
                ->format('d/m/Y');
        } catch (\Throwable) {
            $displayValue = $isoValue;
        }
    }
@endphp

<div class="field" data-date-field>
    <label
        class="field-label"
        for="{{ $inputId }}"
        @if ($disabled) data-disabled="true" @endif
    >
        {{ $label }}

        @if ($optional || $required)
            <span class="field-label__requirement">
                <span aria-hidden="true"> ∙ </span>
                {{ $optional ? 'Opcional' : 'Obligatorio' }}
            </span>
        @endif
    </label>

    @if ($description)
        <span id="{{ $descriptionId }}" class="field-description">
            {{ $description }}
        </span>
    @endif

    <div class="field-input-status-wrapper">
        <div
            class="date-input"
            data-date-input
            data-size="{{ $size }}"
            data-value="{{ $isoValue }}"
            @if ($min) data-min="{{ $min }}" @endif
            @if ($max) data-max="{{ $max }}" @endif
            @if ($error) data-status="error" @endif
            @if ($disabled) data-disabled="true" @endif
            style="anchor-name: {{ $anchorName }}"
        >
            <button
                type="button"
                class="date-input__icon-button"
                data-date-toggle
                aria-label="Abrir calendario"
                @disabled($disabled)
            >
                <x-ui.icon name="calendar-days" size="sm" />
            </button>

            <input
                id="{{ $inputId }}"
                type="text"
                role="combobox"
                value="{{ $displayValue }}"
                placeholder="DD/MM/AAAA"
                class="date-input__control"
                aria-haspopup="dialog"
                aria-expanded="false"
                aria-autocomplete="none"
                autocomplete="off"
                @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
                @if ($required) aria-required="true" @endif
                @if ($error) aria-invalid="true" @endif
                @disabled($disabled)
                {{ $attributes }}
            >

            <span
                class="visually-hidden"
                role="alert"
                aria-live="assertive"
                data-date-alert
            ></span>

            @if ($hasClear && ! $disabled)
                <button
                    type="button"
                    class="date-input__icon-button"
                    data-date-clear
                    aria-label="Borrar {{ $label }}"
                    @if (blank($isoValue)) hidden @endif
                >
                    <x-ui.icon name="x-mark" size="sm" />
                </button>
            @endif

            @if ($error)
                <x-ui.icon
                    name="exclamation-circle"
                    class="text-input__status-icon"
                    data-type="error"
                />
            @endif

            <input
                type="hidden"
                name="{{ $name }}"
                value="{{ $isoValue }}"
                data-date-value
                @disabled($disabled)
            >

            <div
                id="{{ $calendarId }}"
                class="popover date-input__popover"
                role="dialog"
                aria-label="Elegir fecha"
                popover="auto"
                data-date-popover
                style="position-anchor: {{ $anchorName }}"
            >
                <div class="calendar" data-mode="single"></div>
            </div>
        </div>

        @if ($error)
            <x-ui.field-status
                :id="$statusId"
                type="error"
                variant="attached"
            >
                {{ $error }}
            </x-ui.field-status>
        @endif
    </div>
</div>
