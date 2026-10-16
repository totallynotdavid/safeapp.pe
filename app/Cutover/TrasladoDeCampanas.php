<?php

namespace App\Cutover;

use App\Models\AsignacionDato;
use App\Models\Dato;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

final class TrasladoDeCampanas implements Traslado
{
    // For a record whose customer opened no case of either kind.
    private const SEGMENTO_POR_DEFECTO = 'MOVIL';

    private const ORIGEN_INHOUSE = 'INHOUSE';

    private Lote $datos;

    private Lote $asignaciones;

    /** @var array<string, true> the natural keys already written */
    private array $vistos = [];

    /** @var array<int, true> id_cliente => true */
    private array $conFija = [];

    /** @var array<int, true> id_cliente => true */
    private array $conMovil = [];

    private int $idDato = 0;

    private int $idAsignacion = 0;

    private int $duplicados = 0;

    private int $sinCliente = 0;

    private int $sinFecha = 0;

    public function __construct(
        private readonly MapaDeClientes $clientes,
        private readonly string $fechaDeCorte,
    ) {
        $this->datos = new Lote('datos');
        $this->asignaciones = new Lote('asignacion_datos');
    }

    public function ejecutar(): array
    {
        $this->conFija = $this->clientesConCaso('nc_fija');
        $this->conMovil = $this->clientesConCaso('nc_movil');

        DB::connection('legacy')
            ->table('datos')
            ->orderBy('id_cliente')
            ->chunk(
                5_000,
                /** @param Collection<int, stdClass> $filas */ function (Collection $filas): void {
                    foreach ($filas as $fila) {
                        $this->trasladar($fila);
                    }

                    // Records before the holds that point at them. 5,000 rows of
                    // either table stay under the placeholder ceiling, so this is
                    // the only place either is written.
                    $this->datos->vaciar();
                    $this->asignaciones->vaciar();
                },
            );

        return [
            'datos' => $this->datos->cerrar(),
            'asignacion_datos' => $this->asignaciones->cerrar(),
            'duplicados descartados' => $this->duplicados,
            'filas sin cliente' => $this->sinCliente,
            'asignaciones sin fecha' => $this->sinFecha,
        ];
    }

    private function trasladar(object $fila): void
    {
        $idCliente = $this->clientes->resolver($fila->ruc);

        if ($idCliente === null) {
            $this->sinCliente++;

            return;
        }

        $origen = Saneamiento::texto($fila->origen, 100) ?? self::ORIGEN_INHOUSE;
        $tipoData = $this->segmento($fila->tipo_data, $idCliente);

        // The natural key the new schema enforces. The first row wins.
        $clave = $idCliente.'|'.$origen.'|'.$tipoData;

        if (array_key_exists($clave, $this->vistos)) {
            $this->duplicados++;

            return;
        }

        $this->vistos[$clave] = true;

        $this->datos->agregar([
            'id_dato' => ++$this->idDato,
            'id_cliente' => $idCliente,
            'origen' => $origen,
            'tipo_data' => $tipoData,
            'estado' => (int) $fila->estado === Dato::ESTADO_GESTIONADO
                ? Dato::ESTADO_GESTIONADO
                : Dato::ESTADO_PENDIENTE,
            'zonal' => Saneamiento::texto($fila->zonal, 100),
            'nodo' => Saneamiento::texto($fila->nodo),
            'q_movistar' => Saneamiento::conteo($fila->q_movistar),
            'q_entel' => Saneamiento::conteo($fila->q_entel),
            'q_claro' => Saneamiento::conteo($fila->q_claro),
            'q_bitel' => Saneamiento::conteo($fila->q_bitel),
        ]);

        if ((int) $fila->id_usuario !== 0) {
            $this->abrirHold($fila);
        }
    }

    private function abrirHold(object $fila): void
    {
        $fecha = Saneamiento::fecha($fila->fecha_asignacion);
        $this->sinFecha += $fecha === null ? 1 : 0;

        $this->asignaciones->agregar([
            'id_asignacion' => ++$this->idAsignacion,
            'id_dato' => $this->idDato,
            'id_usuario' => (int) $fila->id_usuario,
            'id_tienda' => (int) $fila->id_tienda,
            // A hold without a start date is not a state the schema represents, so
            // an undated one reads as held since the cutover.
            'fecha_asignacion' => $fecha ?? $this->fechaDeCorte,
            'fecha_liberacion' => null,
            'activa' => AsignacionDato::ACTIVA,
        ]);
    }

    /** A real segment is kept. Anything else is placed by the case the agent went on to open. */
    private function segmento(mixed $tipoData, int $idCliente): string
    {
        $tipoData = mb_strtoupper(trim((string) $tipoData));

        if (in_array($tipoData, Dato::TIPOS_DATA, true)) {
            return $tipoData;
        }

        if (array_key_exists($idCliente, $this->conMovil)) {
            return 'MOVIL';
        }

        if (array_key_exists($idCliente, $this->conFija)) {
            return 'FIJA';
        }

        return self::SEGMENTO_POR_DEFECTO;
    }

    /** @return array<int, true> id_cliente => true */
    private function clientesConCaso(string $tabla): array
    {
        $clientes = [];

        DB::connection('legacy')
            ->table($tabla)
            ->select('ruc')
            ->orderBy('id_cargo')
            ->chunk(
                20_000,
                /** @param Collection<int, stdClass> $filas */ function (Collection $filas) use (&$clientes): void {
                    foreach ($filas as $fila) {
                        $idCliente = $this->clientes->resolver($fila->ruc);

                        if ($idCliente !== null) {
                            $clientes[$idCliente] = true;
                        }
                    }
                },
            );

        return $clientes;
    }
}
