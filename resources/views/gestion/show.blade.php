@extends('layouts.app')

@section('title', $cliente->razon_social)

@section('page', true)

@section('content')
    @php
        $respuestaOptions = $respuestas->map(static fn ($opciones, $canal): array => [
            'title' => $canal,
            'options' => $opciones->map(static fn ($respuesta): array => [
                'value' => $respuesta->id_respuesta,
                'label' => $respuesta->descripcion,
            ])->all(),
        ])->values()->all();
    @endphp

    <x-ui.detail-page :title="$cliente->razon_social" subtitle="RUC {{ $cliente->ruc }}"
        :back-href="route($rutaBase)" back-label="Volver a la cola">
        <x-slot:metadata>
            <x-ui.badge>{{ $dato->tipo_data }}</x-ui.badge>
        </x-slot:metadata>

        <x-ui.card padding="6">
            <x-ui.stack direction="vertical" gap="4">
                <x-ui.stack direction="vertical" gap="0.5">
                    <x-ui.heading level="2">Datos de la campaña</x-ui.heading>
                    <x-ui.text type="supporting" color="secondary">Información disponible para preparar el contacto.</x-ui.text>
                </x-ui.stack>

                <x-ui.metadata-list columns="multi">
                    @include('partials.campo', ['label' => 'Zonal', 'value' => $dato->zonal])
                    @include('partials.campo', ['label' => 'Origen', 'value' => $dato->origen])
                    @include('partials.campo', ['label' => 'Líneas '.\App\Cockpit\Operadores::PROPIO, 'value' => $mezcla->propias()])
                    @include('partials.campo', ['label' => 'Líneas competencia', 'value' => $mezcla->competencia()])
                </x-ui.metadata-list>

                <x-ui.divider />

                <x-ui.metadata-list columns="multi">
                    @foreach (\App\Cockpit\Operadores::TODOS as $operador)
                        @include('partials.campo', ['label' => $operador, 'value' => $mezcla->cantidad($operador)])
                    @endforeach
                </x-ui.metadata-list>
            </x-ui.stack>
        </x-ui.card>

        <x-ui.card padding="6">
            <form method="POST" action="{{ route('gestion.store', $dato) }}">
                @csrf
                <x-ui.form-layout>
                    <x-ui.stack direction="vertical" gap="0.5">
                        <x-ui.heading level="2">Registrar gestión</x-ui.heading>
                        <x-ui.text type="supporting" color="secondary">Registre el resultado del contacto y la mezcla de líneas confirmada.</x-ui.text>
                    </x-ui.stack>

                    <x-ui.grid min-width="240" repeat="fit" gap="4">
                        <x-ui.text-input name="telefono" label="Teléfono" :value="old('telefono')"
                            :error="$errors->first('telefono')" maxlength="30" required />
                        {{-- The response owns its channel; sections only expose that server-owned grouping. --}}
                        <x-ui.selector name="id_respuesta" label="Respuesta" :options="$respuestaOptions"
                            :value="old('id_respuesta')" :error="$errors->first('id_respuesta')"
                            placeholder="Elija una respuesta" required />
                    </x-ui.grid>
                    <x-ui.grid min-width="240" repeat="fit" gap="4">
                        <x-ui.text-input name="nombre_contacto" label="Nombre de contacto"
                            :value="old('nombre_contacto')" :error="$errors->first('nombre_contacto')"
                            maxlength="150" optional />
                        <x-ui.text-input name="correo" label="Correo" type="email" :value="old('correo')"
                            :error="$errors->first('correo')" maxlength="150" optional />
                    </x-ui.grid>

                    <x-ui.stack direction="vertical" gap="3">
                        <x-ui.stack direction="vertical" gap="0.5">
                            <x-ui.heading level="3">Líneas por operador</x-ui.heading>
                            <x-ui.text type="supporting" color="secondary">Actualice la cantidad con lo confirmado durante este contacto.</x-ui.text>
                        </x-ui.stack>
                        <x-ui.grid min-width="140" repeat="fit" gap="3">
                            @foreach (\App\Cockpit\Operadores::TODOS as $operador)
                                @php($columna = \App\Cockpit\Operadores::columna($operador))
                                <x-ui.text-input :name="$columna"
                                    :label="$operador === \App\Cockpit\Operadores::PROPIO ? $operador.' (nuestras)' : $operador"
                                    type="number" min="0" max="100000"
                                    :value="old($columna, $mezcla->cantidad($operador))"
                                    :error="$errors->first($columna)" />
                            @endforeach
                        </x-ui.grid>
                    </x-ui.stack>

                    <x-ui.text-area name="comentario" label="Comentario" :value="old('comentario')"
                        :error="$errors->first('comentario')" :max-length="1000" rows="3" optional />

                    <x-ui.stack direction="horizontal" gap="2" h-align="end" wrap="wrap">
                        <x-ui.button :href="route($rutaBase)" variant="secondary">Cancelar</x-ui.button>
                        <x-ui.button type="submit" variant="primary">Registrar gestión</x-ui.button>
                    </x-ui.stack>
                </x-ui.form-layout>
            </form>
        </x-ui.card>

        <x-ui.card padding="6">
            <x-ui.stack direction="vertical" gap="4">
                <x-ui.stack direction="horizontal" gap="2" v-align="center" wrap="wrap">
                    <div data-theme="stack-item" data-size="fill">
                        <x-ui.stack direction="vertical" gap="0.5">
                            <x-ui.heading level="2">Historial de contactos</x-ui.heading>
                            <x-ui.text type="supporting" color="secondary">Contactos del cliente en todas sus campañas.</x-ui.text>
                        </x-ui.stack>
                    </div>
                    <x-ui.badge>{{ $historial->count() }} registrados</x-ui.badge>
                </x-ui.stack>

                <x-ui.table label="Historial de contactos del cliente">
                    <colgroup>
                        <col class="col-date"><col class="col-agent"><col class="col-contact">
                        <col class="col-phone"><col class="col-response"><col class="col-comment">
                    </colgroup>
                    <thead><tr><th>Fecha</th><th>Agente</th><th>Contacto</th><th>Teléfono</th><th>Respuesta</th><th>Comentario</th></tr></thead>
                    <tbody>
                        @forelse ($historial as $llamada)
                            <tr>
                                <td>{{ $llamada->fecha?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td>{{ $llamada->agente?->personal ?? '—' }}</td>
                                <td>{{ $llamada->nombre_contacto ?? '—' }}</td>
                                <td>{{ $llamada->telefono }}</td>
                                <td>{{ $llamada->respuesta?->descripcion ?? '—' }}</td>
                                <td>{{ $llamada->comentario ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6">
                                <x-ui.empty-state title="Sin contactos registrados"
                                    description="La primera gestión aparecerá aquí." compact />
                            </td></tr>
                        @endforelse
                    </tbody>
                </x-ui.table>
            </x-ui.stack>
        </x-ui.card>

        <x-ui.card padding="6">
            <x-ui.stack direction="vertical" gap="4">
                <x-ui.stack direction="horizontal" gap="2" v-align="center" wrap="wrap">
                    <div data-theme="stack-item" data-size="fill">
                        <x-ui.stack direction="vertical" gap="0.5">
                            <x-ui.heading level="2">Teléfonos conocidos</x-ui.heading>
                            <x-ui.text type="supporting" color="secondary">Números usados previamente para contactar al cliente.</x-ui.text>
                        </x-ui.stack>
                    </div>
                    <x-ui.badge>{{ $telefonos->count() }} números</x-ui.badge>
                </x-ui.stack>

                <x-ui.table label="Teléfonos conocidos del cliente">
                    <thead><tr><th>Teléfono</th><th class="table__numeric">Contactos</th><th>Último uso</th></tr></thead>
                    <tbody>
                        @forelse ($telefonos as $telefono)
                            <tr>
                                <td class="table__primary">{{ $telefono->telefono }}</td>
                                <td class="table__numeric">{{ $telefono->veces }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($telefono->ultimo_contacto)->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3">
                                <x-ui.empty-state title="Sin teléfonos registrados"
                                    description="Los números usados en una gestión aparecerán aquí." compact />
                            </td></tr>
                        @endforelse
                    </tbody>
                </x-ui.table>
            </x-ui.stack>
        </x-ui.card>
    </x-ui.detail-page>
@endsection
