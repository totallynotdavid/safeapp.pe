@extends('layouts.app')

@section('title', 'Agregar cliente')
@section('page', true)

@section('content')
    <x-ui.table-page title="Agregar cliente" content-width="900">
        <x-ui.card padding="8">
            <form method="POST" action="{{ route($rutaGuardar) }}">
                @csrf
                <x-ui.form-layout>
                    <x-ui.stack direction="vertical" gap="0.5">
                        <x-ui.heading level="2">Gestión {{ $tipo }}</x-ui.heading>
                        <x-ui.text type="supporting" color="secondary">
                            Este cliente se agregará directamente a su cola {{ $tipo }}.
                        </x-ui.text>
                    </x-ui.stack>

                    @if (session('error'))
                        <x-ui.banner status="error" title="No se pudo agregar el cliente" :description="session('error')" />
                    @endif

                    <x-ui.grid min-width="260" repeat="fit" gap="3">
                        <x-ui.text-input name="ruc" label="RUC" :value="old('ruc')" inputmode="numeric" maxlength="11"
                            :error="$errors->first('ruc')" required />
                        <x-ui.text-input name="razon_social" label="Razón social" :value="old('razon_social')" maxlength="255"
                            :error="$errors->first('razon_social')" required />
                    </x-ui.grid>

                    <x-ui.text-input name="zonal" label="Zonal" :value="old('zonal')" maxlength="100"
                        :error="$errors->first('zonal')" optional />

                    <x-ui.grid min-width="160" repeat="fit" gap="3">
                        @foreach (\App\Cockpit\Operadores::TODOS as $operador)
                            @php($columna = \App\Cockpit\Operadores::columna($operador))
                            <x-ui.text-input :name="$columna" :label="'Líneas '.$operador" type="number" min="0"
                                :value="old($columna, 0)" :error="$errors->first($columna)" optional />
                        @endforeach
                    </x-ui.grid>

                    <x-ui.stack direction="horizontal" gap="2">
                        <x-ui.button type="submit">Agregar a Gestión {{ $tipo }}</x-ui.button>
                        <x-ui.button :href="route($rutaBase)" variant="secondary">Cancelar</x-ui.button>
                    </x-ui.stack>
                </x-ui.form-layout>
            </form>
        </x-ui.card>
    </x-ui.table-page>
@endsection
