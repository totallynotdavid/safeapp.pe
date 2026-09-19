<?php

namespace App\Cutover;

use Illuminate\Support\Facades\DB;

/**
 * Production data, so these are copied rather than seeded: the seeders build
 * invented rows for a dev database, and the real ones (7 stores, 15 supervisors,
 * 22 executives, 86 products) are not something to retype.
 *
 * `respuesta` is seeded instead: its catalog is fixed, the app owns the SI/NO to
 * boolean normalisation, and the seeder already carries legacy's ids.
 */
final class TrasladoDeCatalogos implements Traslado
{
    public function ejecutar(): array
    {
        return [
            'tienda' => $this->copiarTiendas(),
            ...$this->copiarAsignacionesDeTienda(),
            'supervisor' => $this->copiarSupervisores(),
            'cartera' => $this->copiarCarteras(),
            'producto' => $this->copiarProductos(),
        ];
    }

    private function copiarTiendas(): int
    {
        $tiendas = new Lote('tienda');

        foreach (DB::connection('legacy')->table('tienda')->orderBy('id_tienda')->get() as $fila) {
            $tiendas->agregar([
                'id_tienda' => (int) $fila->id_tienda,
                'nombre' => Saneamiento::texto($fila->nombre, 50) ?? 'SIN NOMBRE',
            ]);
        }

        return $tiendas->cerrar();
    }

    /**
     * The table has no key of its own, so a row naming a deleted account is
     * invisible junk rather than a broken link, and 23 of the 631 rows are
     * exactly that.
     *
     * @return array<string, int>
     */
    private function copiarAsignacionesDeTienda(): array
    {
        $usuarios = DB::table('usuario')->pluck('id_usuario')->flip()->all();

        $asignaciones = new Lote('asignacion_tienda');
        $huerfanas = 0;

        foreach (DB::connection('legacy')->table('asigna')->get() as $fila) {
            $idUsuario = (int) $fila->id_usuario;

            if (! array_key_exists($idUsuario, $usuarios)) {
                $huerfanas++;

                continue;
            }

            $asignaciones->agregar([
                'id_tienda' => (int) $fila->id_tienda,
                'id_usuario' => $idUsuario,
                // 0 is the active assignment here, the opposite polarity to
                // cartera.estado below. Both are legacy's and both are kept.
                'estado' => (int) $fila->estado,
            ]);
        }

        return [
            'asignacion_tienda' => $asignaciones->cerrar(),
            'asignaciones de usuarios borrados' => $huerfanas,
        ];
    }

    private function copiarSupervisores(): int
    {
        $supervisores = new Lote('supervisor');

        foreach (DB::connection('legacy')->table('supervisor')->orderBy('id_supervisor')->get() as $fila) {
            $supervisores->agregar([
                'id_supervisor' => (int) $fila->id_supervisor,
                'nombre' => Saneamiento::texto($fila->nombre, 100) ?? 'SIN NOMBRE',
                'estado' => (int) $fila->estado,
            ]);
        }

        return $supervisores->cerrar();
    }

    private function copiarCarteras(): int
    {
        $carteras = new Lote('cartera');

        foreach (DB::connection('legacy')->table('cartera')->orderBy('id_cartera')->get() as $fila) {
            $carteras->agregar([
                'id_cartera' => (int) $fila->id_cartera,
                'nombre' => Saneamiento::texto($fila->nombre, 100) ?? '',
                'propietario' => Saneamiento::texto($fila->propietario, 150) ?? '',
                'id_tienda' => (int) $fila->id_tienda,
                'estado' => (int) $fila->estado,
                'DNI' => Saneamiento::texto($fila->DNI, 20),
            ]);
        }

        return $carteras->cerrar();
    }

    private function copiarProductos(): int
    {
        $productos = new Lote('producto');

        foreach (DB::connection('legacy')->table('producto')->orderBy('id')->get() as $fila) {
            $productos->agregar([
                'id' => (int) $fila->id,
                'tipo' => Saneamiento::texto($fila->tipo, 30) ?? '',
                'subtipo' => Saneamiento::texto($fila->subtipo, 100) ?? '',
                'descripcion' => Saneamiento::texto($fila->descripcion, 100) ?? '',
                'cargo_fijo' => Saneamiento::monto($fila->cf),
                'estado' => (int) $fila->estado,
            ]);
        }

        return $productos->cerrar();
    }
}
