<?php

namespace App\Cutover;

use Illuminate\Support\Facades\DB;

/**
 * Copies the accounts and the roles they hold.
 *
 * The roles are production's ten, not the three the dev seeder invents, because
 * usuario.id_rol points at them and 570 of the 605 accounts are EJECUTIVO
 * COMERCIAL. What each role may reach is TrasladoDePermisos, which runs next.
 */
final class TrasladoDeCuentas implements Traslado
{
    public function ejecutar(): array
    {
        return [
            'rol' => $this->copiarRoles(),
            ...$this->copiarUsuarios(),
        ];
    }

    private function copiarRoles(): int
    {
        $roles = new Lote('rol');

        foreach (DB::connection('legacy')->table('rol')->orderBy('id_rol')->get() as $fila) {
            $roles->agregar([
                'id_rol' => (int) $fila->id_rol,
                'nombre_rol' => Saneamiento::texto($fila->nombre_rol, 50) ?? 'SIN ROL',
            ]);
        }

        return $roles->cerrar();
    }

    /**
     * @return array<string, int>
     */
    private function copiarUsuarios(): array
    {
        $usuarios = new Lote('usuario');
        $sinFecha = 0;

        foreach (DB::connection('legacy')->table('usuario')->orderBy('id_usuario')->get() as $fila) {
            // Every one of the 605 rows carries '0000-00-00' here: the personnel
            // screen that fills it postdates the accounts.
            $nacimiento = Saneamiento::fecha($fila->fecha_nacimiento);
            $sinFecha += $nacimiento === null ? 1 : 0;

            $usuarios->agregar([
                'id_usuario' => (int) $fila->id_usuario,
                'usuario' => Saneamiento::texto($fila->usuario, 50),
                // Carried verbatim. The hashes are legacy's and the login path
                // already reads them; rehashing would lock everyone out.
                'clave' => (string) $fila->clave,
                'id_rol' => (int) $fila->id_rol,
                'personal' => Saneamiento::texto($fila->personal, 50),
                'estado' => (int) $fila->estado,
                'sexo' => Saneamiento::texto($fila->sexo, 1) ?? '',
                'id_supervisor' => (int) $fila->id_supervisor,
                'fecha_nacimiento' => $nacimiento,
                'telefono' => Saneamiento::texto($fila->telefono),
                'correo' => Saneamiento::texto($fila->correo),
            ]);
        }

        return [
            'usuario' => $usuarios->cerrar(),
            'fecha_nacimiento vaciadas' => $sinFecha,
        ];
    }
}
