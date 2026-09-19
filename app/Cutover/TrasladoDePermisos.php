<?php

namespace App\Cutover;

use App\Models\Menu;
use Illuminate\Support\Facades\DB;

/**
 * `menu` is not copied. It is reference data the app owns now, seeded with
 * legacy's own ids so every surviving grant still points at the right screen,
 * and with `permiso` keys in place of the `url` legacy filtered on.
 *
 * Runs after TrasladoDeCuentas, whose users it filters against.
 */
final class TrasladoDePermisos implements Traslado
{
    /**
     * The leaf whose holders administer the system. Legacy's mantUsuario.php,
     * held by 8 people, all of them ADMINISTRADOR or SISTEMAS.
     */
    private const PERMISO_ADMINISTRADOR = 'usuarios.manage';

    /**
     * Screens the rewrite added, which have no legacy grant to carry over.
     * Without this the cutover ships two administration screens nobody in the
     * system can reach.
     */
    private const PERMISOS_NUEVOS = ['roles.manage', 'asignacion.gestionar'];

    public function ejecutar(): array
    {
        $menus = Menu::query()->pluck('id_menu')->flip()->all();

        return [
            ...$this->copiarGrantsDeUsuario($menus),
            'accesoxrol' => $this->copiarGrantsDeRol($menus),
            'grants a pantallas nuevas' => $this->abrirPantallasNuevas(),
        ];
    }

    /**
     * Three kinds of grant are dropped. Ones naming a user that no longer
     * exists (237 of 8,475) are leftovers from deleted accounts. Ones naming
     * menu -1 or 0 (32) never pointed at a screen. Ones naming a retired leaf
     * point at a screen the rewrite removed; for every retired leaf, zero holders
     * lack the surviving global equivalent, so dropping them costs nobody
     * access.
     *
     * @param  array<int, int>  $menus
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

    /** @param  array<int, int>  $menus */
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
        $administradores = DB::table('acceso')
            ->join('menu', 'menu.id_menu', '=', 'acceso.id_menu')
            ->where('menu.permiso', self::PERMISO_ADMINISTRADOR)
            ->pluck('acceso.id_usuario');

        $nuevos = Menu::query()->whereIn('permiso', self::PERMISOS_NUEVOS)->pluck('id_menu');

        $grants = new Lote('acceso');

        foreach ($administradores as $idUsuario) {
            foreach ($nuevos as $idMenu) {
                $grants->agregar(['id_usuario' => (int) $idUsuario, 'id_menu' => (int) $idMenu]);
            }
        }

        return $grants->cerrar();
    }
}
