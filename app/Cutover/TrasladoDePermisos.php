<?php

namespace App\Cutover;

use App\Models\Menu;
use Illuminate\Support\Facades\DB;

/** Runs after TrasladoDeCuentas, whose users it filters against. */
final class TrasladoDePermisos implements Traslado
{
    private const PERMISO_ADMINISTRADOR = 'usuarios.manage';

    // Screens the rewrite added have no legacy grant, so their administrators get them.
    private const PERMISOS_NUEVOS = ['roles.manage', 'asignacion.gestionar'];

    public function ejecutar(): array
    {
        $menus = [];
        foreach (Menu::query()->get(['id_menu']) as $menu) {
            $menus[$menu->id_menu] = true;
        }

        return [
            ...$this->copiarGrantsDeUsuario($menus),
            'accesoxrol' => $this->copiarGrantsDeRol($menus),
            'grants a pantallas nuevas' => $this->abrirPantallasNuevas(),
        ];
    }

    /**
     * @param  array<int, true>  $menus  existing menu ids
     * @return array<string, int>
     */
    private function copiarGrantsDeUsuario(array $menus): array
    {
        $usuarios = DB::table('usuario')->pluck('id_usuario')->flip()->all();

        $accesos = new Lote('acceso');
        $huerfanos = 0;
        $retirados = 0;

        foreach (DB::connection('legacy')->table('acceso')->get() as $fila) {
            $idUsuario = (int) $fila->id_usuario;
            $idMenu = (int) $fila->id_menu;

            if (! array_key_exists($idUsuario, $usuarios)) {
                $huerfanos++;

                continue;
            }

            if (! array_key_exists($idMenu, $menus)) {
                $retirados++;

                continue;
            }

            $accesos->agregar(['id_usuario' => $idUsuario, 'id_menu' => $idMenu]);
        }

        return [
            'acceso' => $accesos->cerrar(),
            'grants de usuarios borrados' => $huerfanos,
            'grants de menus retirados' => $retirados,
        ];
    }

    /** @param  array<int, true>  $menus  existing menu ids */
    private function copiarGrantsDeRol(array $menus): int
    {
        $roles = DB::table('rol')->pluck('id_rol')->flip()->all();

        $porRol = new Lote('accesoxrol');

        foreach (DB::connection('legacy')->table('accesoxrol')->get() as $fila) {
            $idRol = (int) $fila->id_rol;
            $idMenu = (int) $fila->id_menu;

            if (array_key_exists($idRol, $roles) && array_key_exists($idMenu, $menus)) {
                $porRol->agregar(['id_rol' => $idRol, 'id_menu' => $idMenu]);
            }
        }

        return $porRol->cerrar();
    }

    private function abrirPantallasNuevas(): int
    {
        $administradores = array_map(
            intval(...),
            DB::table('acceso')
                ->join('menu', 'menu.id_menu', '=', 'acceso.id_menu')
                ->where('menu.permiso', self::PERMISO_ADMINISTRADOR)
                ->pluck('acceso.id_usuario')
                ->all(),
        );

        $nuevos = Menu::query()->whereIn('permiso', self::PERMISOS_NUEVOS)->get(['id_menu']);

        $grants = new Lote('acceso');

        foreach ($administradores as $idUsuario) {
            foreach ($nuevos as $menu) {
                $grants->agregar(['id_usuario' => $idUsuario, 'id_menu' => $menu->id_menu]);
            }
        }

        return $grants->cerrar();
    }
}
