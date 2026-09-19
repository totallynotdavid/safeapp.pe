@extends('layouts.settings')

@section('title', 'Asignar tiendas')
@section('page', 'true')

@section('content')
    @php
        $selectedStores = collect(old('tiendas', $asignadas))
            ->map(static fn ($id): int => (int) $id)
            ->all();

        $activeStore = old('activa', $activa);

        $activeOptions = $tiendas
            ->map(static fn ($tienda): array => [
                'value' => $tienda->id_tienda,
                'label' => $tienda->nombre,
                'disabled' => ! in_array($tienda->id_tienda, $selectedStores, true),
            ])
            ->all();
    @endphp

    <x-ui.settings-page
        title="Asignar tiendas"
        :description="$usuario->personal . ' · ' . $usuario->usuario"
        max-width="720px"
    >
        <form
            method="POST"
            action="{{ route('asignacion.update', $usuario) }}"
            data-store-assignment
        >
            @csrf
            @method('PUT')

            <x-ui.form-layout>
                <x-ui.stack direction="vertical" gap="1">
                    <x-ui.heading level="3">Tiendas asignadas</x-ui.heading>

                    <x-ui.text type="supporting" color="secondary">
                        Selecciona todas las tiendas que este usuario puede consultar.
                    </x-ui.text>
                </x-ui.stack>

                @if ($tiendas->isEmpty())
                    <x-ui.empty-state title="No hay tiendas registradas" compact />
                @else
                    <x-ui.grid
                        :min-width="240"
                        gap="3"
                        role="group"
                        aria-label="Tiendas asignadas"
                    >
                        @foreach ($tiendas as $tienda)
                            <x-ui.checkbox-input
                                :id="'tienda-' . $tienda->id_tienda"
                                name="tiendas[]"
                                :value="$tienda->id_tienda"
                                :label="$tienda->nombre"
                                :checked="in_array($tienda->id_tienda, $selectedStores, true)"
                                data-store-checkbox
                            />
                        @endforeach
                    </x-ui.grid>

                    @error('tiendas')
                        <x-ui.field-status
                            id="tiendas-status"
                            type="error"
                            variant="detached"
                        >
                            {{ $message }}
                        </x-ui.field-status>
                    @enderror

                    <x-ui.radio-list
                        name="activa"
                        label="Tienda activa"
                        description="Elige la tienda de trabajo inicial entre las tiendas asignadas."
                        :options="$activeOptions"
                        :value="$activeStore"
                        :error="$errors->first('activa')"
                        optional
                    />
                @endif

                <div
                    class="stack"
                    data-direction="horizontal"
                    data-gap="2"
                    data-h-align="start"
                    data-v-align="center"
                >
                    <x-ui.button type="submit" variant="primary">
                        Guardar
                    </x-ui.button>

                    <x-ui.button
                        href="{{ route('asignacion.index') }}"
                        variant="secondary"
                    >
                        Cancelar
                    </x-ui.button>
                </div>
            </x-ui.form-layout>
        </form>
    </x-ui.settings-page>
@endsection
