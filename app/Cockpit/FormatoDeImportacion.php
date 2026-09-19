<?php

namespace App\Cockpit;

use App\Models\Dato;

/**
 * The contract an operator builds an import file against. The screen renders
 * its template from here, so the instructions cannot drift from the rules the
 * upload enforces.
 *
 * A load reaches identity and campaign columns and nothing else. Ownership and
 * progress live on tables it never writes, so a reload cannot take a record from
 * the agent working it and no hand-kept list of overwritable columns exists to
 * forget an entry in.
 */
final class FormatoDeImportacion
{
    /**
     * The order is the template's column order. The file format did not change
     * with the identity/campaign split: an existing spreadsheet still loads, into
     * two tables instead of one.
     *
     * @return list<string>
     */
    public static function columnas(): array
    {
        return [...self::identidad(), ...self::campana()];
    }

    /**
     * Header columns that must be present. Every other column may be absent.
     *
     * @return list<string>
     */
    public static function obligatorias(): array
    {
        return ['ruc', 'tipo_data', 'origen'];
    }

    /** @return array<string, string> */
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
     * Permanent facts about the customer, shared by every campaign that delivers
     * them.
     *
     * @return list<string>
     */
    public static function identidad(): array
    {
        return ['ruc', 'razon_social'];
    }

    /**
     * Facts true of one load, not of the customer. The carrier counts are derived
     * so a change to the carrier catalog reaches the template automatically.
     *
     * @return list<string>
     */
    public static function campana(): array
    {
        return ['origen', 'tipo_data', 'zonal', 'nodo', ...self::carriers()];
    }

    /**
     * Reduced to exactly the columns `cliente` takes, so a stray column in the
     * file never reaches the insert.
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

    /** @param  array<string, string>  $fila */
    private static function texto(array $fila, string $columna): ?string
    {
        $valor = trim((string) ($fila[$columna] ?? ''));

        return $valor === '' ? null : $valor;
    }

    /** @return list<string> */
    private static function carriers(): array
    {
        return array_map(Operadores::columna(...), Operadores::TODOS);
    }
}
