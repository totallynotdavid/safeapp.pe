<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

// Explicit IDs keep id_ref parent links stable across reseeds.
class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [1,  'FUNNEL',               'fa fa-briefcase',  0,  null],
            [2,  'COBRANZA',             'fa fa-money',      0,  null],
            [3,  'GESTION MOVIL',        'fa fa-phone',      1,  'gestion.movil'],
            [4,  'HISTORICO',            'fa fa-download',   1,  'historico.ver'],
            [5,  'MANTENIMIENTO',        'fa fa-cogs',       0,  null],
            [6,  'USUARIO',              'fa fa-users',      5,  'usuarios.manage'],
            [7,  'GESTION FIJA',         'fa fa-wifi',       1,  'gestion.fija'],
            [8,  'REPORTES',             'fa fa-download',   0,  null],
            [9,  'GESTION DIARIA',       'fa fa-area-chart', 8,  'seguimiento.equipo'],
            [11, 'CARGAR DATOS',         'fa fa-database',   5,  'clientes.importar'],

            // AVANZADO is omitted because its flow cannot be submitted.
            [12, 'NOTA DE CARGO',        'fa fa-file',       0,  null],
            [13, 'FIJA',                 'fa fa-wifi',       12, 'notacargo.fija.registrar'],
            [16, 'MOVIL',                'fa fa-mobile',     12, 'notacargo.movil.registrar'],

            // Back queues own their exports; separate report entries are omitted.
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

        Menu::query()->insert($menus);
    }
}
