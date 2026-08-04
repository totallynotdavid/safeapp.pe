{{--
    Shared read-only stages for the nota de cargo móvil lifecycle. The caller
    supplies the official detail-page shell; these cards follow the Section /
    VStack / MetadataList composition in packages/cli/templates/pages/
    detail-page/page.tsx and packages/core/src/MetadataList/ at
    9da0792516e4a551494e2d212f0a03909a8ffd71.

    Expects: $cargo with cliente, ejecutivo, supervisor, validador, region and
    cartera loaded. Set $mostrarSeguimientoEjecutivo=false when the registro
    page renders those request-owned values as an editable form. Set
    $mostrarSeguimientoBack=false when back office renders its values as a form.
--}}
@php($mostrarSeguimientoEjecutivo = $mostrarSeguimientoEjecutivo ?? true)
@php($mostrarSeguimientoBack = $mostrarSeguimientoBack ?? true)

<x-ui.card padding="6">
    <x-ui.stack direction="vertical" gap="4">
        <x-ui.stack direction="vertical" gap="0.5">
            <x-ui.heading level="2">Registro de la oportunidad</x-ui.heading>
            <x-ui.text type="supporting" color="secondary">Identidad, servicio y responsables definidos al registrar la nota de cargo.</x-ui.text>
        </x-ui.stack>

        <x-ui.metadata-list columns="multi">
            @include('partials.campo', ['label' => 'Fecha de ingreso', 'value' => $cargo->fecha_ingreso?->format('d/m/Y')])
            @include('partials.campo', ['label' => 'Región', 'value' => $cargo->region?->nombre])
            @include('partials.campo', ['label' => 'Modalidad', 'value' => \App\Support\EtiquetasDeDominio::texto($cargo->modalidad, 'modalidad')])
            @include('partials.campo', ['label' => 'Tipo de producto', 'value' => \App\Support\EtiquetasDeDominio::texto($cargo->tipo_producto, 'tipo_producto')])
            @include('partials.campo', ['label' => 'Contacto', 'value' => $cargo->contacto])
            @include('partials.campo', ['label' => 'Ejecutivo', 'value' => $cargo->ejecutivo?->personal])
            @include('partials.campo', ['label' => 'Supervisor', 'value' => $cargo->supervisor?->nombre])
            @include('partials.campo', ['label' => 'Ejecutivo TDP', 'value' => $cargo->cartera?->propietario])
            @include('partials.campo', ['label' => 'Cartera', 'value' => $cargo->cartera?->nombre])
        </x-ui.metadata-list>
    </x-ui.stack>
</x-ui.card>

@if ($mostrarSeguimientoEjecutivo)
    <x-ui.card padding="6">
        <x-ui.stack direction="vertical" gap="4">
            <x-ui.stack direction="vertical" gap="0.5">
                <x-ui.heading level="2">Negociación del ejecutivo</x-ui.heading>
                <x-ui.text type="supporting" color="secondary">Alcance económico, contacto y avance comercial informados por el ejecutivo.</x-ui.text>
            </x-ui.stack>

            <x-ui.metadata-list columns="multi">
                @include('partials.campo', ['label' => 'Líneas', 'value' => $cargo->q_lineas])
                @include('partials.campo', ['label' => 'Cargo fijo', 'value' => $cargo->cargo_fijo])
                @include('partials.campo', ['label' => 'Teléfono principal', 'value' => $cargo->telefono1])
                @include('partials.campo', ['label' => 'Teléfono adicional', 'value' => $cargo->telefono2])
                @include('partials.campo', ['label' => 'Fase', 'value' => \App\Support\EtiquetasDeDominio::texto($cargo->etapa, 'etapa')])
                @include('partials.campo', ['label' => 'Probabilidad de cierre', 'value' => filled($cargo->nivel_negociacion) ? $cargo->nivel_negociacion.' %' : null])
                @include('partials.campo', ['label' => 'Fecha estimada de cierre', 'value' => $cargo->fecha_estimada?->format('d/m/Y')])
                @include('partials.campo', ['label' => 'Oportunidad', 'value' => $cargo->oportunidad])
            </x-ui.metadata-list>

            <x-ui.divider />
            <x-ui.metadata-list>
                @include('partials.campo', ['label' => 'Comentario del ejecutivo', 'value' => $cargo->comentario_ejecutivo])
            </x-ui.metadata-list>
        </x-ui.stack>
    </x-ui.card>
@endif

@if ($cargo->fecha_validacion)
    <x-ui.card padding="6">
        <x-ui.stack direction="vertical" gap="4">
            <x-ui.stack direction="vertical" gap="0.5">
                <x-ui.heading level="2">Validación</x-ui.heading>
                <x-ui.text type="supporting" color="secondary">Decisión registrada por el equipo validador.</x-ui.text>
            </x-ui.stack>

            <x-ui.metadata-list columns="multi">
                @include('partials.campo', ['label' => 'Decisión', 'value' => \App\Support\EtiquetasDeDominio::texto($cargo->validacion, 'validacion')])
                @include('partials.campo', ['label' => 'Fecha de validación', 'value' => $cargo->fecha_validacion?->format('d/m/Y')])
                @include('partials.campo', ['label' => 'Validador', 'value' => $cargo->validador?->personal])
            </x-ui.metadata-list>

            <x-ui.divider />
            <x-ui.metadata-list>
                @include('partials.campo', ['label' => 'Comentario del validador', 'value' => $cargo->comentario_validador])
            </x-ui.metadata-list>
        </x-ui.stack>
    </x-ui.card>
@endif

@if ($cargo->fecha_actualizacion && $mostrarSeguimientoBack)
    <x-ui.card padding="6">
        <x-ui.stack direction="vertical" gap="4">
            <x-ui.stack direction="vertical" gap="0.5">
                <x-ui.heading level="2">Seguimiento de back office</x-ui.heading>
                <x-ui.text type="supporting" color="secondary">Estado operativo y referencias registradas durante la atención.</x-ui.text>
            </x-ui.stack>

            <x-ui.metadata-list columns="multi">
                @include('partials.campo', ['label' => 'Estado', 'value' => \App\Support\EtiquetasDeDominio::texto($cargo->estado, 'estado')])
                @include('partials.campo', ['label' => 'Fecha de actualización', 'value' => $cargo->fecha_actualizacion?->format('d/m/Y')])
                @include('partials.campo', ['label' => 'Nodo', 'value' => $cargo->nodo])
                @include('partials.campo', ['label' => 'Caso FWA', 'value' => $cargo->casofwa])
                @include('partials.campo', ['label' => 'Ciclo', 'value' => $cargo->ciclo])
                @include('partials.campo', ['label' => 'Cuenta financiera', 'value' => $cargo->cuenta_financiera])
            </x-ui.metadata-list>

            <x-ui.divider />
            <x-ui.metadata-list>
                @include('partials.campo', ['label' => 'Comentario de back office', 'value' => $cargo->comentario_back])
            </x-ui.metadata-list>
        </x-ui.stack>
    </x-ui.card>
@endif
