{{--
    What a nota de cargo fija currently says, read-only, grouped by the stage
    that owns each block. Every screen downstream of registro opens with this,
    so the three of them cannot drift into showing different subsets of the same
    case the way the legacy modals did.

    The validación and back blocks only appear once that stage has run, so an
    undecided case does not display an empty ruling.

    Expects: $cargo (an NcFija with cliente, ejecutivo, supervisor, validador and
    region loaded).
--}}
<div class="rounded-lg border border-gray-200 bg-white p-6">
    <h2 class="text-lg font-semibold text-gray-800">{{ $cargo->cliente?->razon_social }}</h2>
    <p class="mb-4 text-sm text-gray-500">RUC {{ $cargo->cliente?->ruc }}</p>

    <dl class="grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-3">
        @include('partials.campo', ['label' => 'Fecha ingreso', 'value' => $cargo->fecha_ingreso?->format('Y-m-d')])
        @include('partials.campo', ['label' => 'Estado', 'value' => $cargo->estado])
        @include('partials.campo', ['label' => 'Región', 'value' => $cargo->region?->nombre])
        @include('partials.campo', ['label' => 'Modalidad', 'value' => $cargo->modalidad])
        @include('partials.campo', ['label' => 'Producto', 'value' => $cargo->subtipo])
        @include('partials.campo', ['label' => 'Velocidad', 'value' => $cargo->descripcion])
        @include('partials.campo', ['label' => 'Cargo fijo', 'value' => $cargo->cargo_fijo])
        @include('partials.campo', ['label' => 'Tecnología', 'value' => $cargo->tecnologia])
        @include('partials.campo', ['label' => 'Contacto', 'value' => $cargo->contacto])
        @include('partials.campo', ['label' => 'Teléfonos', 'value' => trim($cargo->telefono1.' '.$cargo->telefono2)])
        @include('partials.campo', ['label' => 'Ejecutivo', 'value' => $cargo->ejecutivo?->personal])
        @include('partials.campo', ['label' => 'Supervisor', 'value' => $cargo->supervisor?->nombre])
    </dl>

    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
        @include('partials.campo', ['label' => 'Dirección', 'value' => $cargo->direccion])
        @include('partials.campo', ['label' => 'Coordenadas', 'value' => $cargo->coordenadas])
    </div>

    <div class="mt-4">
        @include('partials.campo', ['label' => 'Comentario del ejecutivo', 'value' => $cargo->comentario])
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
                @include('partials.campo', ['label' => 'Fecha liquidación', 'value' => $cargo->fecha_liquidacion?->format('Y-m-d')])
                @include('partials.campo', ['label' => 'Nodo', 'value' => $cargo->nodo])
                @include('partials.campo', ['label' => 'Petición', 'value' => $cargo->peticion])
                @include('partials.campo', ['label' => 'Contrata', 'value' => $cargo->contrata])
                @include('partials.campo', ['label' => 'N° asignado', 'value' => $cargo->nasignado])
                @include('partials.campo', ['label' => 'Caso SF', 'value' => $cargo->caso_sf])
                @include('partials.campo', ['label' => 'Cuenta financiera', 'value' => $cargo->ncfinanciera])
                @include('partials.campo', ['label' => 'NC facturación', 'value' => $cargo->ncfe])
            </dl>
            <div class="mt-4">
                @include('partials.campo', ['label' => 'Comentario del back', 'value' => $cargo->comentario_back])
            </div>
        </div>
    @endif
</div>
