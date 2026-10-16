<?php

namespace App\Cutover;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

/**
 * Ids are assigned here rather than read back after insert. The table is empty
 * when this runs, and holding the map is what lets later traslados resolve a RUC
 * without a query per row.
 */
final class MapaDeClientes
{
    private const LOTE = 2_000;

    // Least to most trusted, because the last write wins the name.
    private const FUENTES = [
        ['tabla' => 'quiebre', 'orden' => 'id_quiebre'],
        ['tabla' => 'nc_fija', 'orden' => 'id_cargo'],
        ['tabla' => 'nc_movil', 'orden' => 'id_cargo'],
        ['tabla' => 'llamada', 'orden' => 'fecha'],
        ['tabla' => 'datos', 'orden' => 'id_cliente'],
    ];

    /** @var array<string, int> ruc => id_cliente */
    private array $ids = [];

    private int $descartados = 0;

    /** @return array{clientes: int, rucs_descartados: int} */
    public function construir(): array
    {
        $nombres = $this->recolectar();

        $filas = [];
        $id = 0;

        foreach ($nombres as $ruc => $razonSocial) {
            $ruc = (string) $ruc;
            $this->ids[$ruc] = ++$id;
            $filas[] = ['id_cliente' => $id, 'ruc' => $ruc, 'razon_social' => $razonSocial];

            if (count($filas) >= self::LOTE) {
                DB::table('cliente')->insert($filas);
                $filas = [];
            }
        }

        if ($filas !== []) {
            DB::table('cliente')->insert($filas);
        }

        return ['clientes' => $id, 'rucs_descartados' => $this->descartados];
    }

    /** Null for a junk RUC. The caller drops the row and reports it. */
    public function resolver(mixed $ruc): ?int
    {
        $ruc = self::normalizar($ruc);

        return $ruc === null ? null : $this->ids[$ruc] ?? null;
    }

    /** @return array<array-key, string|null> ruc => razon_social. PHP turns an all-digit ruc into an int key. */
    private function recolectar(): array
    {
        $nombres = [];

        foreach (self::FUENTES as $fuente) {
            DB::connection('legacy')
                ->table($fuente['tabla'])
                ->select('ruc', 'razon_social')
                ->orderBy($fuente['orden'])
                ->chunk(
                    20_000,
                    /** @param Collection<int, stdClass> $filas */ function (Collection $filas) use (&$nombres): void {
                        foreach ($filas as $fila) {
                            $ruc = self::normalizar($fila->ruc);

                            if ($ruc !== null) {
                                $nombres[$ruc] = Saneamiento::texto($fila->razon_social, 250);

                                continue;
                            }

                            // A blank RUC is an empty row. Only a non-blank one that is
                            // not digits is worth reporting.
                            if (trim((string) $fila->ruc) !== '') {
                                $this->descartados++;
                            }
                        }
                    },
                );
        }

        return $nombres;
    }

    /** A key only has to identify a customer consistently, so any all-digit value is kept, whatever its length. */
    private static function normalizar(mixed $ruc): ?string
    {
        $ruc = trim((string) $ruc);

        return $ruc !== '' && ctype_digit($ruc) ? $ruc : null;
    }
}
