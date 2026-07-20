<?php

namespace App\Cutover;

use Illuminate\Support\Facades\DB;

/**
 * Copies the nota de cargo fija cases.
 *
 * Six legacy columns are not carried: `tipo` ('FIJA' in every row), `q_lineas`
 * (1 in every row), `agenda` (empty in every row), `id_cartera` (0 in every
 * row), `ejecutivo_telefonica` (set in one), and `zonal_telefonica`, which is
 * folded into `zonal` below. The reasoning for each is in the migration.
 */
final class TrasladoDeNotasFija implements Traslado
{
    /**
     * Three technologies spelled 28 ways, because legacy made this a free-text
     * input and the screen is a select now.
     *
     * Listed rather than pattern-matched: the data is fixed forever, a reviewer
     * can check every line against the export, and a rule clever enough to read
     * FFTTHH as FTTH would also read something else wrong. FIBRA is mapped to
     * FTTH, which is an inference from how the word is used locally and not
     * something the rows state. 'HFC/GPON' names two and is left unset, as are
     * the three rows holding GPS coordinates and the ones holding '1' or 'TV'.
     */
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
            ->chunk(5_000, function (iterable $filas) use ($casos, &$sinCliente, &$tecnologiaIlegible): void {
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
                        // Back's correction wins. Legacy wrote it to a second
                        // column and left the ejecutivo's original standing, and
                        // the two disagree in 1,835 rows. That zonal_telefonica
                        // is back's is not a guess: it is set in none of the 806
                        // cases the back office never touched and in 4,478 of
                        // the 5,164 it did.
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
            });

        return [
            'nc_fija' => $casos->cerrar(),
            'casos sin cliente' => $sinCliente,
            'tecnologia ilegible' => $tecnologiaIlegible,
        ];
    }
}
