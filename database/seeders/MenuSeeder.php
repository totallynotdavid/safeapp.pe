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
            [1,  'FUNNEL',               'fa fa-briefcase',  0,  null],
            [2,  'COBRANZA',             'fa fa-money',      0,  null],
            [3,  'GESTION MOVIL',        'fa fa-phone',      1,  'gestion.movil'],
            [4,  'HISTORICO',            'fa fa-download',   1,  'historico.ver'],
            [5,  'MANTENIMIENTO',        'fa fa-cogs',       0,  null],
            [6,  'USUARIO',              'fa fa-users',      5,  'usuarios.manage'],
            [7,  'GESTION FIJA',         'fa fa-wifi',       1,  'gestion.fija'],
            [8,  'REPORTES',             'fa fa-download',   0,  null],
            [9,  'GESTION DIARIA',       'fa fa-area-chart', 8,  'seguimiento.equipo'],
            [10, 'AGREGAR CLIENTE',      'fa fa-plus',       1,  'clientes.crear'],
            [11, 'CARGAR DATOS',         'fa fa-database',   5,  'mantDatos'],
            // The three AVANZADO leaves (legacy ids 23, 24, 25) and their report
            // (26) are not here. nc_avanzado has never held a row: its
            // AUTO_INCREMENT has never advanced, and the producto catalog the
            // registro form reads has no AVANZADO entry, so the form's product
            // dropdowns come up empty and the screen cannot be submitted. It is
            // not lightly used, it has never worked.
            [12, 'NOTA DE CARGO',        'fa fa-file',       0,  null],
            [13, 'FIJA',                 'fa fa-wifi',       12, 'notacargo.fija.registrar'],
            [16, 'MOVIL',                'fa fa-mobile',     12, 'notacargo.movil.registrar'],
            // The five REPORTE leaves under REPORTES (legacy ids 19, 20, 26, 27
            // and 30) are gone, and none of them was a report. Each embedded the
            // back-office edit modal and posted to the same agregarnc?back
            // endpoints, so they were the back screen a third and fourth time
            // with an Excel button on top. The export is now a second submit on
            // the back queue's own filter form, under that screen's permiso, so
            // it needs no leaf of its own: see routes/notas-cargo.php. Their
            // holders keep the access through BACK FIJA and BACK MOVIL, which
            // every one of them already holds.
            //
            // The four ids below are legacy's "GLOBAL" copies, kept because they
            // are the leaves the real reviewers hold. Each one's tienda-filtered
            // twin is gone (fija 14 and 15, móvil 17 and 18): the holders of a
            // filtered leaf are in every case a strict subset of the holders of
            // its global counterpart, which shows a superset of the same rows,
            // and the column they filtered on is the case's región rather than
            // the reviewer's store. Four users held each filtered leaf against
            // 13 and 28 for the fija globals.
            [21, 'BACK FIJA',            'fa fa-wifi',       12, 'notacargo.fija.back'],
            [22, 'VALIDACION FIJA',      'fa fa-wifi',       12, 'notacargo.fija.validar'],
            [28, 'BACK MOVIL',           'fa fa-mobile',     12, 'notacargo.movil.back'],
            [29, 'VALIDACION MOVIL',     'fa fa-mobile',     12, 'notacargo.movil.validar'],
            [31, 'PRODUCCION',           'fa fa-area-chart', 8,  'produccion.ver'],
            [32, 'AVERIAS',              'fa fa-file',       0,  null],
            [33, 'REGISTRO',             'fa fa-area-chart', 32, 'quiebre.registrar'],
            [34, 'SEGUIMIENTO',          'fa fa-area-chart', 32, 'quiebre.validar'],
            [35, 'PERSONAL',             'fa fa-users',      0,  null],
            [36, 'DATOS PERSONAL',       'fa fa-users',      35, 'personal.gestionar'],
            [40, 'ASIGNACION DE TIENDA', 'fa fa-bank',       35, 'asignacion.gestionar'],
            [37, 'LIQUIDACION',          'fa fa-money',      0,  null],
            [38, 'LIQUIDACION',          'fa fa-money',      37, 'liquidacion.gestionar'],
            // New screen the legacy app never had a working equivalent of: its
            // rol page (vista/usuario.php) was an off-sidebar, broken orphan.
            // This is the permissions-admin surface, not a legacy remap.
            [39, 'ROLES Y PERMISOS',     'fa fa-key',        5,  'roles.manage'],
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
