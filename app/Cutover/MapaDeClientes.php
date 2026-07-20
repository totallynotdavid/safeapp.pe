<?php

namespace App\Cutover;

use Illuminate\Support\Facades\DB;

/**
 * Builds `cliente` from every RUC legacy ever recorded, and answers the one
 * question the rest of the transform keeps asking: which id_cliente is this RUC?
 *
 * Legacy had no customer table. Identity lived on the campaign row, so a
 * customer existed only while some list still carried them, and the other four
 * tables referred to them by copying the RUC and the name as free text. The
 * union of those five columns is therefore the real customer list, and it is
 * much larger than the campaign table: 146,736 of the 206,121 RUCs appear only
 * in `llamada`, meaning legacy had contacted them and then dropped every trace
 * of who they were when the list was replaced.
 *
 * Ids are assigned here rather than read back after insert. The table is empty
 * when this runs, so nothing else can be competing for them, and holding the
 * map in hand is what lets the later traslados resolve without a query per row.
 */
final class MapaDeClientes
{
    private const LOTE = 2_000;

    /**
     * The name is taken from the most trustworthy source that has one, which is
     * the order these are listed in: last write wins.
     *
     * `datos` is first because a campaign file is the closest thing legacy had
     * to a customer record. `llamada` outranks the case tables because an agent
     * who just spoke to the customer corrects the name; the case screens
     * prefilled it and then let a modal edit it, which is why the same RUC can
     * carry several spellings.
     */
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

    /**
     * @return array{clientes: int, rucs_descartados: int}
     */
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

    /**
     * The id for a legacy RUC, or null when the value was never a customer key.
     *
     * Callers drop the row rather than inventing a customer: a row whose RUC is
     * junk has no subject, and the transform reports how many it dropped.
     */
    public function resolver(mixed $ruc): ?int
    {
        $ruc = self::normalizar($ruc);

        return $ruc === null ? null : $this->ids[$ruc] ?? null;
    }

    /**
     * @return array<string, string|null> ruc => razon_social
     */
    private function recolectar(): array
    {
        $nombres = [];

        foreach (self::FUENTES as $fuente) {
            DB::connection('legacy')
                ->table($fuente['tabla'])
                ->select('ruc', 'razon_social')
                ->orderBy($fuente['orden'])
                ->chunk(20_000, function (iterable $filas) use (&$nombres): void {
                    foreach ($filas as $fila) {
                        $ruc = self::normalizar($fila->ruc);

                        if ($ruc !== null) {
                            $nombres[$ruc] = Saneamiento::texto($fila->razon_social, 250);

                            continue;
                        }

                        // A blank RUC is a row nobody filled in; a non-blank one
                        // that is not digits is the imported header row. Only
                        // the second is worth reporting.
                        if (trim((string) $fila->ruc) !== '') {
                            $this->descartados++;
                        }
                    }
                });
        }

        return $nombres;
    }

    /**
     * A RUC is digits. The export holds exactly one value that is not: `?ruc`,
     * carrying the razón social "razon_social", which is a CSV header row that
     * was imported as data. Nothing references it and it names no customer.
     *
     * The one 9-digit value (940891060, a mobile number an agent typed into the
     * RUC field) is kept: it is wrong, but it consistently identifies a real
     * customer with a real logged contact, which is all this key has to do.
     */
    private static function normalizar(mixed $ruc): ?string
    {
        $ruc = trim((string) $ruc);

        return $ruc !== '' && ctype_digit($ruc) ? $ruc : null;
    }
}
