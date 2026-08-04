{{--
    Shared read-only stages for the nota de cargo fija lifecycle. The caller
    supplies the official detail-page shell; these cards follow the Section /
    VStack / MetadataList composition in packages/cli/templates/pages/
    detail-page/page.tsx and packages/core/src/MetadataList/ at
    9da0792516e4a551494e2d212f0a03909a8ffd71.

    Expects: $cargo with cliente, ejecutivo, supervisor, validador and region
    loaded. Set $mostrarSeguimientoEjecutivo=false when registro renders its
    request-owned values as a form. Set $mostrarSeguimientoBack=false when back
    office renders its values as a form.
--}}
@php($mostrarSeguimientoEjecutivo = $mostrarSeguimientoEjecutivo ?? true)
@php($mostrarSeguimientoBack = $mostrarSeguimientoBack ?? true)

<x-ui.card padding="6">
    <x-ui.stack direction="vertical" gap="4">
        <x-ui.stack direction="vertical" gap="0.5">
            <x-ui.heading level="2">Registro de la solicitud</x-ui.heading>
            <x-ui.text type="supporting" color="secondary">Identidad, servicio y responsables definidos al registrar la nota de cargo.</x-ui.text>
        </x-ui.stack>

        <x-ui.metadata-list columns="multi">
            @include('partials.campo', ['label' => 'Fecha de ingreso', 'value' => $cargo->fecha_ingreso?->format('d/m/Y')])
            @include('partials.campo', ['label' => 'Región', 'value' => $cargo->region?->nombre])
            @include('partials.campo', ['label' => 'Modalidad', 'value' => \App\Support\EtiquetasDeDominio::texto($cargo->modalidad, 'modalidad')])
            @include('partials.campo', ['label' => 'Producto', 'value' => $cargo->subtipo])
            @include('partials.campo', ['label' => 'Velocidad', 'value' => $cargo->descripcion])
            @include('partials.campo', ['label' => 'Cargo fijo', 'value' => filled($cargo->cargo_fijo) ? 'S/ '.number_format((float) $cargo->cargo_fijo, 2) : null])
            @include('partials.campo', ['label' => 'Ejecutivo', 'value' => $cargo->ejecutivo?->personal])
            @include('partials.campo', ['label' => 'Supervisor', 'value' => $cargo->supervisor?->nombre])
        </x-ui.metadata-list>
    </x-ui.stack>
</x-ui.card>

@if ($mostrarSeguimientoEjecutivo)
    <x-ui.card padding="6">
        <x-ui.stack direction="vertical" gap="4">
            <x-ui.stack direction="vertical" gap="0.5">
                <x-ui.heading level="2">Coordinación del ejecutivo</x-ui.heading>
                <x-ui.text type="supporting" color="secondary">Contacto y ubicación informados para coordinar la instalación.</x-ui.text>
            </x-ui.stack>

            <x-ui.metadata-list columns="multi">
                @include('partials.campo', ['label' => 'Contacto', 'value' => $cargo->contacto])
                @include('partials.campo', ['label' => 'Teléfono principal', 'value' => $cargo->telefono1])
                @include('partials.campo', ['label' => 'Teléfono adicional', 'value' => $cargo->telefono2])
                @include('partials.campo', ['label' => 'Tecnología', 'value' => \App\Support\EtiquetasDeDominio::texto($cargo->tecnologia, 'tecnologia')])
                @include('partials.campo', ['label' => 'Dirección', 'value' => $cargo->direccion])
                @include('partials.campo', ['label' => 'Coordenadas', 'value' => $cargo->coordenadas])
            </x-ui.metadata-list>

            <x-ui.divider />
            <x-ui.metadata-list>
                @include('partials.campo', ['label' => 'Comentario del ejecutivo', 'value' => $cargo->comentario])
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
                <x-ui.text type="supporting" color="secondary">Estado operativo y referencias registradas durante la instalación.</x-ui.text>
            </x-ui.stack>

            <x-ui.metadata-list columns="multi">
                @include('partials.campo', ['label' => 'Estado', 'value' => \App\Support\EtiquetasDeDominio::texto($cargo->estado, 'estado')])
                @include('partials.campo', ['label' => 'Fecha de actualización', 'value' => $cargo->fecha_actualizacion?->format('d/m/Y')])
                @include('partials.campo', ['label' => 'Fecha de liquidación', 'value' => $cargo->fecha_liquidacion?->format('d/m/Y')])
                @include('partials.campo', ['label' => 'Nodo', 'value' => $cargo->nodo])
                @include('partials.campo', ['label' => 'Petición', 'value' => $cargo->peticion])
                @include('partials.campo', ['label' => 'Número asignado', 'value' => $cargo->nasignado])
                @include('partials.campo', ['label' => 'Contrata', 'value' => $cargo->contrata])
                @include('partials.campo', ['label' => 'Caso SF', 'value' => $cargo->caso_sf])
                @include('partials.campo', ['label' => 'NC financiera', 'value' => $cargo->ncfinanciera])
                @include('partials.campo', ['label' => 'NCFE', 'value' => $cargo->ncfe])
            </x-ui.metadata-list>

            <x-ui.divider />
            <x-ui.metadata-list>
                @include('partials.campo', ['label' => 'Comentario de back office', 'value' => $cargo->comentario_back])
            </x-ui.metadata-list>
        </x-ui.stack>
    </x-ui.card>
@endif
