<?php

namespace App\Cockpit;

use App\Models\Cliente;

/**
 * What a client row in an import file looks like: which columns it carries, what
 * each one must contain, and which of them a load may overwrite on a client that
 * already exists.
 *
 * Separate from ImportacionDeClientes, which owns reading a file and writing the
 * rows, because this is the contract an operator builds their file against. The
 * screen renders the template from here, so the instructions on screen cannot
 * drift from the rules the upload enforces.
 */
final class FormatoDeImportacion
{
    /**
     * Columns a load may overwrite on a client that already exists.
     *
     * These are the descriptors the feed is authoritative for. Two groups are
     * deliberately absent, for the same reason stated two ways: a load never
     * overwrites what a person recorded. `id_usuario`, `id_tienda`, `estado` and
     * `fecha_asignacion` belong to DistribucionDeClientes and the gestión form.
     * The carrier counts are agent-editable on the gestión form, so a file sets
     * them when it creates a client and leaves them alone afterwards.
     *
     * @return list<string>
     */
    public static function actualizables(): array
    {
        return ['razon_social', 'tipo_data', 'origen', 'zonal', 'nodo'];
    }

    /**
     * Every column a file may carry, in the order the operator sees them.
     *
     * The carrier counts are derived so a change to the carrier catalog reaches
     * the import template without anyone remembering this list exists.
     *
     * @return list<string>
     */
    public static function columnas(): array
    {
        return ['ruc', ...self::actualizables(), ...self::carriers()];
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
            // Unique in the table and the key the load matches on.
            'ruc' => 'required|digits:11',
            'razon_social' => 'nullable|string|max:250',
            // Decides which queue works the client, so only a segment that has a
            // screen is loadable.
            'tipo_data' => 'required|in:'.implode(',', Cliente::TIPOS_DATA),
            // Required because the pool picker groups by origen and skips nulls:
            // a client loaded without one can never be drawn by any agent.
            'origen' => 'required|string|max:500',
            'zonal' => 'nullable|string|max:100',
            'nodo' => 'nullable|string',
            ...array_fill_keys(self::carriers(), 'nullable|integer|min:0'),
        ];
    }

    /**
     * The row reduced to exactly the columns the table takes, so a stray column
     * in the file can never reach the insert.
     *
     * @param  array<string, string>  $fila
     * @return array<string, mixed>
     */
    public static function valores(array $fila): array
    {
        $valores = [];

        foreach (self::columnas() as $columna) {
            $valores[$columna] = $fila[$columna] ?? null;
        }

        foreach (self::carriers() as $columna) {
            $valores[$columna] = (int) $valores[$columna];
        }

        return $valores;
    }

    /**
     * @return list<string>
     */
    private static function carriers(): array
    {
        return array_map(Operadores::columna(...), Operadores::TODOS);
    }
}
