{{--
    What a nota de cargo móvil currently says, read-only, grouped by the stage
    that owns each block. Every screen downstream of registro opens with this,
    so the four of them cannot drift into showing different subsets of the same
    case the way the legacy modals did.

    The validación and back blocks only appear once that stage has run, so an
    undecided case does not display an empty ruling.

    Expects: $cargo (an NcMovil with cliente, ejecutivo, region, cartera loaded).
--}}
<div class="rounded-lg border border-gray-200 bg-white p-6">
    <h2 class="text-lg font-semibold text-gray-800">{{ $cargo->cliente?->razon_social }}</h2>
    <p class="mb-4 text-sm text-gray-500">RUC {{ $cargo->cliente?->ruc }}</p>

    <dl class="grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-3">
        @include('partials.campo', ['label' => 'Fecha ingreso', 'value' => $cargo->fecha_ingreso?->format('Y-m-d')])
        @include('partials.campo', ['label' => 'Estado', 'value' => $cargo->estado])
        @include('partials.campo', ['label' => 'Región', 'value' => $cargo->region?->nombre])
        @include('partials.campo', ['label' => 'Modalidad', 'value' => $cargo->modalidad])
        @include('partials.campo', ['label' => 'Q líneas', 'value' => $cargo->q_lineas])
        @include('partials.campo', ['label' => 'Cargo fijo', 'value' => $cargo->cargo_fijo])
        @include('partials.campo', ['label' => 'Tipo de producto', 'value' => $cargo->tipo_producto])
        @include('partials.campo', ['label' => 'Fase', 'value' => $cargo->etapa])
        @include('partials.campo', ['label' => 'Probabilidad de cierre', 'value' => $cargo->nivel_negociacion ? $cargo->nivel_negociacion.' %' : null])
        @include('partials.campo', ['label' => 'Fecha estimada de cierre', 'value' => $cargo->fecha_estimada?->format('Y-m-d')])
        @include('partials.campo', ['label' => 'Contacto', 'value' => $cargo->contacto])
        @include('partials.campo', ['label' => 'Teléfonos', 'value' => trim($cargo->telefono1.' '.$cargo->telefono2)])
        {{-- Two different people: the app user who registered the case, and the
             carrier account executive it is attributed to. --}}
        @include('partials.campo', ['label' => 'Ejecutivo', 'value' => $cargo->ejecutivo?->personal])
        @include('partials.campo', ['label' => 'Supervisor', 'value' => $cargo->supervisor?->nombre])
        @include('partials.campo', ['label' => 'Ejecutivo TDP', 'value' => $cargo->cartera?->propietario])
        @include('partials.campo', ['label' => 'Cartera', 'value' => $cargo->cartera?->nombre])
        @include('partials.campo', ['label' => 'Oportunidad', 'value' => $cargo->oportunidad])
    </dl>

    <div class="mt-4">
        @include('partials.campo', ['label' => 'Comentario del ejecutivo', 'value' => $cargo->comentario_ejecutivo])
    </div>

    @if ($cargo->fecha_validacion)
        <div class="mt-4 border-t border-gray-100 pt-4">
            <dl class="grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-3">
                @include('partials.campo', ['label' => 'Validación', 'value' => $cargo->validacion])
                @include('partials.campo', ['label' => 'Fecha validación', 'value' => $cargo->fecha_validacion?->format('Y-m-d')])
                @include('partials.campo', ['label' => 'Validador', 'value' => $cargo->validador?->personal])
            </dl>
            <div class="mt-4">
                @include('partials.campo', ['label' => 'Comentario del validador', 'value' => $cargo->comentario_validador])
            </div>
        </div>
    @endif

    @if ($cargo->fecha_actualizacion)
        <div class="mt-4 border-t border-gray-100 pt-4">
            <dl class="grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-3">
                @include('partials.campo', ['label' => 'Actualización', 'value' => $cargo->fecha_actualizacion?->format('Y-m-d')])
                @include('partials.campo', ['label' => 'Nodo', 'value' => $cargo->nodo])
                @include('partials.campo', ['label' => 'Caso FWA', 'value' => $cargo->casofwa])
                @include('partials.campo', ['label' => 'Ciclo', 'value' => $cargo->ciclo])
                @include('partials.campo', ['label' => 'Cuenta financiera', 'value' => $cargo->cuenta_financiera])
            </dl>
            <div class="mt-4">
                @include('partials.campo', ['label' => 'Comentario del back', 'value' => $cargo->comentario_back])
            </div>
        </div>
    @endif
</div>
