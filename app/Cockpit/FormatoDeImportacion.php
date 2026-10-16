<?php

namespace App\Cockpit;

use App\Models\Dato;

final class FormatoDeImportacion
{
    /**
     * The order is the template's column order.
     *
     * @return list<string>
     */
    public static function columnas(): array
    {
        return [...self::identidad(), ...self::campana()];
    }

    /**
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
            'tipo_data' => 'required|in:'.implode(',', Dato::TIPOS_DATA),
            // Required because the pool groups by origen: a record loaded without
            // one can never be drawn by any agent.
            'origen' => 'required|string|max:100',
            'zonal' => 'nullable|string|max:100',
            'nodo' => 'nullable|string',
            ...array_fill_keys(self::carriers(), 'nullable|integer|min:0'),
        ];
    }

    /**
     * @return list<string>
     */
    public static function identidad(): array
    {
        return ['ruc', 'razon_social'];
    }

    /**
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
        $valor = trim($fila[$columna] ?? '');

        return $valor === '' ? null : $valor;
    }

    /** @return list<string> */
    private static function carriers(): array
    {
        return array_map(Operadores::columna(...), Operadores::TODOS);
    }
}
