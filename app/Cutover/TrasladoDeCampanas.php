<?php

namespace App\Cutover;

use App\Models\AsignacionDato;
use App\Models\Dato;
use Illuminate\Support\Facades\DB;

/**
 * Splits legacy `datos` into the campaign record and the agent's hold on it.
 *
 * One legacy row becomes one `datos` row always, plus one open `asignacion_datos`
 * row when an agent was holding it. Identity has already left for `cliente`, so
 * what stays here is what the file said and what the agent has done with it.
 */
final class TrasladoDeCampanas implements Traslado
{
    /**
     * The segment a hand-registered client is filed under when nothing else
     * says otherwise.
     *
     * Legacy's agregarcliente wrote 'CONVERGENTE' into tipo_data, which is the
     * column the queue filters on, so all 2,677 of those rows became
     * unreachable. They are all estado 1 (already worked), so the segment is now
     * only a historical label, but it still has to be one of the two segments
     * that exist. What the agent did next is the best evidence there is: 1,600
     * of them went on to a móvil charge note against 138 to a fija one, so a
     * case decides it and the majority decides the rest.
     */
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
            ->chunk(5_000, function (iterable $filas): void {
                foreach ($filas as $fila) {
                    $this->trasladar($fila);
                }

                // Records before the holds that point at them. Both tables are
                // narrow enough that 5,000 rows stay under the placeholder
                // ceiling, so this boundary is the only place either is written
                // and this order is the order they reach the database in.
                $this->datos->vaciar();
                $this->asignaciones->vaciar();
            });

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

        // The natural key the new schema enforces. Legacy had no such index and
        // 28 groups of rows collide on it: 59 excess rows, every one an
        // agregarcliente double submit, identical to its twin apart from the
        // id. Lowest id wins because that is the one the agent's later work
        // referred to.
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
            // Legacy only began writing fecha_asignacion in May 2026, so 22,288
            // of the 24,106 open holds have no date. They are dated at the
            // cutover rather than left null, because a hold without a start
            // date is not a state the new schema should be able to represent.
            // It reads as "held since the cutover", which errs towards leaving
            // agents the records they have.
            'fecha_asignacion' => $fecha ?? $this->fechaDeCorte,
            'fecha_liberacion' => null,
            'activa' => AsignacionDato::ACTIVA,
        ]);
    }

    /**
     * The queue that works the record. Anything legacy already filed under a
     * real segment keeps it; everything else is one of the INHOUSE rows and is
     * placed by the case the agent went on to open.
     */
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

    /**
     * @return array<int, true> id_cliente => true
     */
    private function clientesConCaso(string $tabla): array
    {
        $clientes = [];

        DB::connection('legacy')
            ->table($tabla)
            ->select('ruc')
            ->orderBy('id_cargo')
            ->chunk(20_000, function (iterable $filas) use (&$clientes): void {
                foreach ($filas as $fila) {
                    $idCliente = $this->clientes->resolver($fila->ruc);

                    if ($idCliente !== null) {
                        $clientes[$idCliente] = true;
                    }
                }
            });

        return $clientes;
    }
}
