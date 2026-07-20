<?php

namespace App\Cutover;

use Illuminate\Support\Facades\DB;

/**
 * Copies the accounts and the roles they hold.
 *
 * The roles are production's, not the three the dev seeder invents, because
 * usuario.id_rol points at them and 570 of the 605 accounts are EJECUTIVO
 * COMERCIAL. What each role may reach is TrasladoDePermisos, which runs next.
 */
final class TrasladoDeCuentas implements Traslado
{
    /**
     * Roles for modules that were retired rather than ported, dropped for the
     * same reason their menus are.
     *
     * Only BACK OFFICE AVANZADO (10) qualifies: nc_avanzado has never held a
     * row and no AVANZADO product exists, so the role has 0 users and 0 rows in
     * accesoxrol and would arrive as an empty entry in the new ROLES screen.
     * VALIDADOR MOVIL (8) and VALIDADOR FIJA (9) are also userless but their
     * screens exist and were ported, so they are kept.
     */
    private const ROLES_RETIRADOS = [10];

    public function ejecutar(): array
    {
        return [
            ...$this->copiarRoles(),
            ...$this->copiarUsuarios(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function copiarRoles(): array
    {
        $roles = new Lote('rol');
        $retirados = 0;

        foreach (DB::connection('legacy')->table('rol')->orderBy('id_rol')->get() as $fila) {
            $idRol = (int) $fila->id_rol;

            if (in_array($idRol, self::ROLES_RETIRADOS, true)) {
                $retirados++;

                continue;
            }

            $roles->agregar([
                'id_rol' => $idRol,
                'nombre_rol' => Saneamiento::texto($fila->nombre_rol, 50) ?? 'SIN ROL',
            ]);
        }

        return [
            'rol' => $roles->cerrar(),
            'roles de modulos retirados' => $retirados,
        ];
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
