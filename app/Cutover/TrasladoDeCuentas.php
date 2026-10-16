<?php

namespace App\Cutover;

use Illuminate\Support\Facades\DB;

final class TrasladoDeCuentas implements Traslado
{
    // Roles of modules the rewrite retired. BACK OFFICE AVANZADO (10) has no users
    // and no grants.
    private const ROLES_RETIRADOS = [10];

    public function ejecutar(): array
    {
        return [
            ...$this->copiarRoles(),
            ...$this->copiarUsuarios(),
        ];
    }

    /** @return array<string, int> */
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

    /** @return array<string, int> */
    private function copiarUsuarios(): array
    {
        $usuarios = new Lote('usuario');
        $sinFecha = 0;

        foreach (DB::connection('legacy')->table('usuario')->orderBy('id_usuario')->get() as $fila) {
            $nacimiento = Saneamiento::fecha($fila->fecha_nacimiento);
            $sinFecha += $nacimiento === null ? 1 : 0;

            $usuarios->agregar([
                'id_usuario' => (int) $fila->id_usuario,
                'usuario' => Saneamiento::texto($fila->usuario, 50),
                // Legacy's hashes are unsalted MD5, so none is carried over.
                'clave' => null,
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
