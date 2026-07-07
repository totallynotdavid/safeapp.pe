{{-- Read-only labelled value for a detail grid. --}}
<div>
    <dt class="text-xs font-medium uppercase tracking-wider text-gray-500">{{ $label }}</dt>
    <dd class="mt-0.5 text-sm text-gray-800">{{ filled($value) ? $value : '—' }}</dd>
</div>
