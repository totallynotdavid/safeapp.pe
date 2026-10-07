<?php

namespace App\Cockpit;

use App\Models\Cliente;
use App\Models\Dato;

final class CargaDeLote
{
    /**
     * @param  list<array<string, string>>  $lote
     * @return array{nuevos: int, actualizados: int}
     */
    public static function registrar(array $lote): array
    {
        $campana = self::registrosDeCampana($lote);
        $repetidos = self::yaCargados($campana);

        // Do not update estado. Reloading a file must preserve progress.
        Dato::query()->upsert(
            $campana,
            ['id_cliente', 'origen', 'tipo_data'],
            [
                'zonal',
                'nodo',
                ...array_map(
                    Operadores::columna(...),
                    Operadores::TODOS,
                ),
            ],
        );

        return ['nuevos' => count($campana) - $repetidos, 'actualizados' => $repetidos];
    }

    /** @param  list<array<string, mixed>>  $campana */
    private static function yaCargados(array $campana): int
    {
        $existentes = [];

        $datos = Dato::query()
            ->whereIn('id_cliente', array_column($campana, 'id_cliente'))
            ->get(['id_cliente', 'origen', 'tipo_data']);

        foreach ($datos as $dato) {
            $existentes[self::claveDeDato($dato->id_cliente, $dato->origen, $dato->tipo_data)] = true;
        }

        $repetidos = 0;

        foreach ($campana as $registro) {
            $clave = self::claveDeDato($registro['id_cliente'], $registro['origen'], $registro['tipo_data']);

            if (array_key_exists($clave, $existentes)) {
                $repetidos++;
            }
        }

        return $repetidos;
    }

    /** Returns the upsert conflict key in a comparable form. */
    private static function claveDeDato(mixed $cliente, mixed $origen, mixed $tipo): string
    {
        return json_encode([$cliente, $origen, $tipo], JSON_THROW_ON_ERROR);
    }

    /**
     * @param  list<array<string, string>>  $lote
     * @return list<array<string, mixed>>
     */
    private static function registrosDeCampana(array $lote): array
    {
        // The import file is authoritative for razon_social.
        Cliente::query()->upsert(
            array_map(FormatoDeImportacion::valoresIdentidad(...), $lote),
            ['ruc'],
            ['razon_social'],
        );

        $ids = Cliente::query()
            ->whereIn('ruc', array_column($lote, 'ruc'))
            ->pluck('id_cliente', 'ruc');

        return array_map(
            static fn (array $fila): array => FormatoDeImportacion::valoresCampana(
                $fila,
                intval($ids[$fila['ruc']]),
            ),
            $lote,
        );
    }
}
