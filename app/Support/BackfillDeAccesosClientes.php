<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class BackfillDeAccesosClientes
{
    public static function ejecutar(): void
    {
        foreach (self::fuentes() as $consulta) {
            $consulta->chunk(1_000, static function ($filas): void {
                $accesos = $filas
                    ->map(static fn (object $fila): array => [
                        'id_cliente' => $fila->id_cliente,
                        'id_usuario' => $fila->id_usuario,
                    ])
                    ->all();

                if ($accesos !== []) {
                    DB::table('cliente_acceso')->insertOrIgnore($accesos);
                }
            });
        }
    }

    /** @return list<Builder> */
    private static function fuentes(): array
    {
        return [
            self::fuente(
                'asignacion_datos',
                'asignacion_datos.id_dato',
                'datos',
            ),
            self::fuente('llamada', 'llamada.id_cliente'),
            self::fuente('nc_movil', 'nc_movil.id_cliente'),
            self::fuente('nc_fija', 'nc_fija.id_cliente'),
            self::fuente('quiebre', 'quiebre.id_cliente'),
        ];
    }

    private static function fuente(
        string $tabla,
        string $columnaCliente,
        ?string $tablaDato = null,
    ): Builder {
        $consulta = DB::table($tabla)
            ->join(
                'usuario',
                'usuario.id_usuario',
                '=',
                $tabla.'.id_usuario',
            );

        if ($tablaDato !== null) {
            $consulta->join(
                $tablaDato,
                $tablaDato.'.id_dato',
                '=',
                $columnaCliente,
            );

            $columnaCliente = $tablaDato.'.id_cliente';
        }

        return $consulta
            ->select(
                $columnaCliente.' as id_cliente',
                $tabla.'.id_usuario',
            )
            ->distinct()
            ->orderBy('id_cliente')
            ->orderBy($tabla.'.id_usuario');
    }
}
