<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

/**
 * Explicit id_menu values keep the id_ref parent links stable across reseeds.
 *
 * permiso convention: section headers are null. A leaf owned by a ported module
 * gets a designed key (usuarios.manage). Every other leaf keeps a provisional
 * slug until its module is ported and renames it.
 */
class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // id, descripcion, icono, id_ref, permiso
            [1,  'FUNNEL',                 'fa fa-briefcase',  0,  null],
            [2,  'COBRANZA',               'fa fa-money',      0,  null],
            [3,  'GESTION MOVIL',          'fa fa-phone',      1,  'gestion.movil'],
            [4,  'HISTORICO',              'fa fa-download',   1,  'historico.ver'],
            [5,  'MANTENIMIENTO',          'fa fa-cogs',       0,  null],
            [6,  'USUARIO',                'fa fa-users',      5,  'usuarios.manage'],
            [7,  'GESTION FIJA',           'fa fa-wifi',       1,  'gestion.fija'],
            [8,  'REPORTES',               'fa fa-download',   0,  null],
            [9,  'GESTION DIARIA',         'fa fa-area-chart', 8,  'repseguimiento'],
            [10, 'AGREGAR CLIENTE',        'fa fa-plus',       1,  'clientes.crear'],
            [11, 'CARGAR DATOS',           'fa fa-database',   5,  'mantDatos'],
            [12, 'NOTA DE CARGO',          'fa fa-file',       0,  null],
            [13, 'FIJA',                   'fa fa-wifi',       12, 'notacargo_fija'],
            [14, 'VALIDACION FIJA',        'fa fa-wifi',       12, 'notacargo_fija_validacion'],
            [15, 'BACK FIJA',              'fa fa-wifi',       12, 'notacargo_fija_back'],
            [16, 'MOVIL',                  'fa fa-mobile',     12, 'notacargo_movil'],
            [17, 'VALIDACION MOVIL',       'fa fa-mobile',     12, 'notacargo_movil_validacion'],
            [18, 'BACK MOVIL',             'fa fa-mobile',     12, 'notacargo_movil_back'],
            [19, 'REPORTE BACK FIJA',      'fa fa-wifi',       8,  'repbackfija'],
            [20, 'REPORTE BACK MOVIL',     'fa fa-mobile',     8,  'repbackmovil'],
            [21, 'BACK FIJA GLOBAL',       'fa fa-wifi',       12, 'notacargo_fija_back_global'],
            [22, 'VALIDACION FIJA GLOBAL', 'fa fa-wifi',       12, 'notacargo_fija_validacion_global'],
            [23, 'AVANZADO',               'fa fa-wifi',       12, 'notacargo_fija_avanzado'],
            [24, 'VALIDACION AVZ GLOBAL',  'fa fa-wifi',       12, 'notacargo_fija_avanzado_validacion_global'],
            [25, 'BACK AVZ GLOBAL',        'fa fa-wifi',       12, 'notacargo_fija_avanzado_back_global'],
            [26, 'REPORTE BACK AVZ',       'fa fa-wifi',       8,  'repbackavanzado'],
            [27, 'REPORTE FIJA GLOBAL',    'fa fa-wifi',       8,  'repbackfijaglobal'],
            [28, 'BACK MOVIL GLOBAL',      'fa fa-mobile',     12, 'notacargo_movil_back_global'],
            [29, 'VALIDA MOVIL GLOBAL',    'fa fa-mobile',     12, 'notacargo_movil_validacion_global'],
            [30, 'REPORTE MOVIL GLOBAL',   'fa fa-mobile',     8,  'repbackmovilglobal'],
            [31, 'DASHBOARD EJE REAL',     'fa fa-download',   8,  'dashboard'],
            [32, 'AVERIAS',                'fa fa-file',       0,  null],
            [33, 'REGISTRO',               'fa fa-area-chart', 32, 'quiebre.registrar'],
            [34, 'SEGUIMIENTO',            'fa fa-area-chart', 32, 'quiebre.validar'],
            [35, 'PERSONAL',               'fa fa-users',      0,  null],
            [36, 'DATOS PERSONAL',         'fa fa-users',      35, 'personal.gestionar'],
            [40, 'ASIGNACION DE TIENDA',   'fa fa-bank',       35, 'asignacion.gestionar'],
            [37, 'LIQUIDACION',            'fa fa-money',      0,  null],
            [38, 'LIQUIDACION',            'fa fa-money',      37, 'liquidacion.gestionar'],
            // New screen the legacy app never had a working equivalent of: its
            // rol page (vista/usuario.php) was an off-sidebar, broken orphan.
            // This is the permissions-admin surface, not a legacy remap.
            [39, 'ROLES Y PERMISOS',       'fa fa-key',        5,  'roles.manage'],
        ];

        $menus = array_map(static fn (array $row): array => [
            'id_menu' => $row[0],
            'descripcion' => $row[1],
            'icono' => $row[2],
            'estado' => 0,
            'id_ref' => $row[3],
            'permiso' => $row[4],
        ], $rows);

        Menu::insert($menus);
    }
}
