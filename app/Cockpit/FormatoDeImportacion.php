<?php

namespace App\Cockpit;

use App\Models\Dato;

/**
 * What a row in an import file looks like: which columns it carries, what each
 * one must contain, and which table each belongs to.
 *
 * Separate from ImportacionDeClientes, which owns reading a file and writing the
 * rows, because this is the contract an operator builds their file against. The
 * screen renders the template from here, so the instructions on screen cannot
 * drift from the rules the upload enforces.
 *
 * There is no longer a list of "columns a load may overwrite". One existed to
 * keep a reload from resetting ownership and progress, and it had to be
 * maintained by hand: forget an entry and a load could take a record from the
 * agent working it. Those columns no longer live on the tables a load writes, so
 * the guarantee is structural instead of remembered. A load reaches identity and
 * campaign facts, and nothing else.
 */
final class FormatoDeImportacion
{
    /**
     * Every column a file may carry, in the order the operator sees them.
     *
     * The file format is unchanged by the split: an operator's existing
     * spreadsheet still loads, it just lands in two tables instead of one.
     *
     * @return list<string>
     */
    public static function columnas(): array
    {
        return [...self::identidad(), ...self::campana()];
    }

    /**
     * Columns a file cannot omit. The rest may be absent entirely.
     *
     * @return list<string>
     */
    public static function obligatorias(): array
    {
        return ['ruc', 'tipo_data', 'origen'];
    }

    /**
     * @return array<string, string>
     */
    public static function reglas(): array
    {
        return [
            'ruc' => 'required|digits:11',
            'razon_social' => 'nullable|string|max:250',
            // Decides which queue works the record, so only a segment that has a
            // screen is loadable.
            'tipo_data' => 'required|in:'.implode(',', Dato::TIPOS_DATA),
            // Required because the pool groups by origen: a record loaded
            // without one can never be drawn by any agent. Production carries 17
            // distinct values, the longest 25 characters.
            'origen' => 'required|string|max:100',
            'zonal' => 'nullable|string|max:100',
            'nodo' => 'nullable|string',
            ...array_fill_keys(self::carriers(), 'nullable|integer|min:0'),
        ];
    }

    /**
     * Who the row is about. Permanent, and shared by every campaign that ever
     * delivers this customer.
     *
     * @return list<string>
     */
    public static function identidad(): array
    {
        return ['ruc', 'razon_social'];
    }

    /**
     * What this campaign says about them. True of one load, not of the customer.
     *
     * The carrier counts are derived so a change to the carrier catalog reaches
     * the import template without anyone remembering this list exists.
     *
     * @return list<string>
     */
    public static function campana(): array
    {
        return ['origen', 'tipo_data', 'zonal', 'nodo', ...self::carriers()];
    }

    /**
     * The identity half of a row, reduced to exactly the columns `cliente`
     * takes, so a stray column in the file can never reach the insert.
     *
     * @param  array<string, string>  $fila
     * @return array<string, mixed>
     */
    public static function valoresIdentidad(array $fila): array
    {
        return [
            'ruc' => $fila['ruc'],
            'razon_social' => self::texto($fila, 'razon_social'),
        ];
    }

    /**
     * The campaign half of a row, reduced to exactly the columns `datos` takes.
     *
     * @param  array<string, string>  $fila
     * @return array<string, mixed>
     */
    public static function valoresCampana(array $fila, int $idCliente): array
    {
        $valores = [
            'id_cliente' => $idCliente,
            'origen' => $fila['origen'],
            'tipo_data' => $fila['tipo_data'],
            'zonal' => self::texto($fila, 'zonal'),
            'nodo' => self::texto($fila, 'nodo'),
        ];

        foreach (self::carriers() as $columna) {
            $valores[$columna] = (int) ($fila[$columna] ?? 0);
        }

        return $valores;
    }

    /**
     * @param  array<string, string>  $fila
     */
    private static function texto(array $fila, string $columna): ?string
    {
        $valor = trim((string) ($fila[$columna] ?? ''));

        return $valor === '' ? null : $valor;
    }

    /**
     * @return list<string>
     */
    private static function carriers(): array
    {
        return array_map(Operadores::columna(...), Operadores::TODOS);
    }
}
