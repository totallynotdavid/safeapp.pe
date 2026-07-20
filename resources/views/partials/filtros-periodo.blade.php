{{--
    Período and estado filter for a nota de cargo queue. Every registro and back
    screen uses it, over both productos: they list the same rows over the same
    window (NotaCargoBuilder::enPeriodo) and differ only in whose cases they show
    and which estado vocabulary they offer, which is what $estados carries.

    Expects: $ruta, $ano, $anos, $mes, $estado, $estados.
--}}
@php($control = 'mt-1 rounded-md border border-gray-300 px-3 py-1.5 text-sm focus:border-gray-500 focus:outline-none')
@php($meses = ['01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto', '09' => 'Setiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'])

<form method="GET" action="{{ route($ruta) }}" class="flex flex-wrap items-end gap-3">
    <div>
        <label for="ano" class="block text-xs font-medium text-gray-500">Año</label>
        <select id="ano" name="ano" class="{{ $control }}">
            @foreach ($anos as $opcion)
                <option value="{{ $opcion }}" @selected($ano === $opcion)>{{ $opcion }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="mes" class="block text-xs font-medium text-gray-500">Mes</label>
        <select id="mes" name="mes" class="{{ $control }}">
            @foreach ($meses as $valor => $nombre)
                <option value="{{ $valor }}" @selected($mes === (int) $valor)>{{ $nombre }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="estado" class="block text-xs font-medium text-gray-500">Estado</label>
        <select id="estado" name="estado" class="{{ $control }}">
            <option value="">Todos</option>
            @foreach ($estados as $opcion)
                <option value="{{ $opcion }}" @selected($estado === $opcion)>{{ $opcion }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="rounded-md bg-gray-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-gray-700">
        Filtrar
    </button>
</form>
