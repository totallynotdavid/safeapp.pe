@extends('layouts.settings')

@section('title', 'Importar clientes')

@section('content')
    {{-- FileInput task flow, using the settings-sidebar content hierarchy. --}}
    <x-ui.stack direction="vertical" gap="6" max-width="760px">
        <x-ui.heading level="2" class="settings-page__heading">Importar clientes</x-ui.heading>

        @if (session('errores'))
            <x-ui.banner status="error" title="No se cargó ningún cliente" description="Corrige el archivo y vuelve a subirlo.">
                <x-ui.stack direction="vertical" gap="1">
                    @foreach (session('errores') as $error)
                        <x-ui.text type="supporting">• {{ $error }}</x-ui.text>
                    @endforeach
                </x-ui.stack>
            </x-ui.banner>
        @endif

        <form method="POST" action="{{ route('clientes.importar.store') }}" enctype="multipart/form-data" data-upload-form>
            @csrf
            <x-ui.stack direction="vertical" gap="4">
                <x-ui.file-input
                    name="archivo"
                    label="Archivo de clientes"
                    accept=".xlsx,.csv,.txt"
                    description="Sube una plantilla XLSX o CSV de hasta 20 MB. Completa ruc, tipo_data y origen."
                    :error="$errors->first('archivo')"
                    required />

                <x-ui.stack direction="horizontal" gap="2" wrap="wrap">
                    <x-ui.button type="submit" variant="primary">Importar clientes</x-ui.button>
                    <x-ui.button href="{{ route('clientes.importar.plantilla.xlsx') }}" variant="secondary">
                        <x-slot:icon><x-ui.icon name="arrow-down-tray" /></x-slot:icon>
                        Descargar plantilla XLSX
                    </x-ui.button>
                    <x-ui.link href="{{ route('clientes.importar.plantilla.csv') }}" standalone>Plantilla CSV</x-ui.link>
                </x-ui.stack>
            </x-ui.stack>
        </form>

        <x-ui.card variant="muted">
            <x-ui.stack direction="horizontal" gap="4" v-align="start">
                <x-ui.center class="settings-info-card__icon">
                    <x-ui.icon name="document-text" />
                </x-ui.center>
                <x-ui.stack direction="vertical" gap="1">
                    <x-ui.text type="body" weight="bold">Empieza con una plantilla</x-ui.text>
                    <x-ui.text type="body" color="secondary">La plantilla incluye todas las columnas aceptadas. La carga valida cada fila antes de importar; si encuentra un error, no realiza cambios parciales.</x-ui.text>
                </x-ui.stack>
            </x-ui.stack>
        </x-ui.card>
    </x-ui.stack>
@endsection
