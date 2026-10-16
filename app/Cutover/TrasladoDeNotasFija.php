<?php

namespace App\Cutover;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

final class TrasladoDeNotasFija implements Traslado
{
    // Listed rather than pattern-matched, so each spelling can be checked against
    // the export. FIBRA is read as FTTH, an inference from the spelling.
    private const TECNOLOGIAS = [
        'FTTH' => 'FTTH',
        'FFTH' => 'FTTH',
        'FTTHH' => 'FTTH',
        'FFTTH' => 'FTTH',
        'FTHH' => 'FTTH',
        'FTTTHH' => 'FTTH',
        'FFTT' => 'FTTH',
        'FFTTHH' => 'FTTH',
        'FTTTH' => 'FTTH',
        'FTH' => 'FTTH',
        'FTT' => 'FTTH',
        'FIBRA' => 'FTTH',
        'FIBRA OPTICA' => 'FTTH',
        'FIBFRA' => 'FTTH',
        'FTTH / FIBRA' => 'FTTH',
        'HFC' => 'HFC',
        'FHC' => 'HFC',
        'HCF' => 'HFC',
        'HF' => 'HFC',
        'GPON' => 'GPON',
    ];

    public function __construct(
        private readonly MapaDeClientes $clientes,
    ) {}

    public function ejecutar(): array
    {
        $casos = new Lote('nc_fija');
        $sinCliente = 0;
        $tecnologiaIlegible = 0;

        DB::connection('legacy')
            ->table('nc_fija')
            ->orderBy('id_cargo')
            ->chunk(
                5_000,
                /** @param Collection<int, stdClass> $filas */ function (Collection $filas) use (
                    $casos,
                    &$sinCliente,
                    &$tecnologiaIlegible,
                ): void {
                    foreach ($filas as $fila) {
                        $idCliente = $this->clientes->resolver($fila->ruc);

                        if ($idCliente === null) {
                            $sinCliente++;

                            continue;
                        }

                        $tecnologia = self::TECNOLOGIAS[mb_strtoupper(trim((string) $fila->tecnologia))] ?? null;
                        $tecnologiaIlegible += $tecnologia === null && trim((string) $fila->tecnologia) !== '' ? 1 : 0;

                        $casos->agregar([
                            'id_cargo' => (int) $fila->id_cargo,
                            'id_cliente' => $idCliente,
                            'estado' => Saneamiento::texto($fila->estado, 30) ?? 'XINGRESAR',
                            'validacion' => Saneamiento::texto($fila->validacion, 30) ?? 'PENDIENTE',
                            'id_usuario' => (int) $fila->id_usuario,
                            'id_supervisor' => (int) $fila->id_supervisor,
                            'fecha_ingreso' => Saneamiento::fecha($fila->fecha_ingreso),
                            'subtipo' => Saneamiento::texto($fila->subtipo, 100),
                            'descripcion' => Saneamiento::texto($fila->descripcion, 100),
                            'cargo_fijo' => Saneamiento::monto($fila->cargo_fijo),
                            'modalidad' => Saneamiento::texto($fila->modalidad, 100),
                            'contacto' => Saneamiento::texto($fila->contacto, 100),
                            'telefono1' => Saneamiento::texto($fila->telefono1, 50),
                            'telefono2' => Saneamiento::texto($fila->telefono2, 50),
                            'direccion' => Saneamiento::texto($fila->direccion),
                            'coordenadas' => Saneamiento::texto($fila->coordenadas, 100),
                            'tecnologia' => $tecnologia,
                            // Back's correction wins.
                            'zonal' =>
                                Saneamiento::idEnTexto($fila->zonal_telefonica) ?? Saneamiento::idEnTexto($fila->zonal),
                            'comentario' => Saneamiento::texto($fila->comentario),
                            'id_validador' => (int) $fila->id_validador,
                            'fecha_validacion' => Saneamiento::fecha($fila->fecha_validacion),
                            'comentario_validador' => Saneamiento::texto($fila->comentario_validador),
                            'fecha_actualizacion' => Saneamiento::fecha($fila->fecha_actualizacion),
                            'fecha_liquidacion' => Saneamiento::fecha($fila->fecha_liquidacion),
                            'id_back' => (int) $fila->id_back,
                            'comentario_back' => Saneamiento::texto($fila->comentario_back),
                            'peticion' => Saneamiento::texto($fila->peticion, 100),
                            'contrata' => Saneamiento::texto($fila->contrata, 100),
                            'nasignado' => Saneamiento::texto($fila->nasignado, 100),
                            'caso_sf' => Saneamiento::texto($fila->caso_sf, 100),
                            'nodo' => Saneamiento::texto($fila->nodo, 100) ?? 'SINNODO',
                            'ncfinanciera' => Saneamiento::texto($fila->ncfinanciera, 100),
                            'ncfe' => Saneamiento::texto($fila->ncfe, 100),
                        ]);
                    }

                    $casos->vaciar();
                },
            );

        return [
            'nc_fija' => $casos->cerrar(),
            'casos sin cliente' => $sinCliente,
            'tecnologia ilegible' => $tecnologiaIlegible,
        ];
    }
}
