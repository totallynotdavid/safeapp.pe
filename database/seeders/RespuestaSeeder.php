<?php

namespace Database\Seeders;

use App\Models\Respuesta;
use Illuminate\Database\Seeder;

// The fixed contact-disposition catalog. Reference data, seeded in every
// environment. The legacy SI/NO/NULL flag text is normalized to booleans here,
// keeping null where the outcome is genuinely unstated.
class RespuestaSeeder extends Seeder
{
    // id, descripcion, tipo, contactabilidad, negociacion, volverllamar.
    private const CATALOGO = [
        [1,  'Desea Fija',                       'LLAMADA', true,  true,  false],
        [2,  'Desea Movil',                      'LLAMADA', true,  true,  false],
        [3,  'Desea Fija y movil',               'LLAMADA', true,  true,  false],
        [4,  'Desea Renovar',                    'LLAMADA', true,  true,  false],
        [5,  'Reclamo/Post venta',               'LLAMADA', true,  false, false],
        [6,  'Fidelizado c/competencia',         'LLAMADA', true,  false, false],
        [7,  'Sin contacto',                     'LLAMADA', false, false, false],
        [8,  'No apto (sunat / otro)',           'LLAMADA', false, false, false],
        [9,  'No interesado',                    'LLAMADA', true,  false, false],
        [10, 'Volver a llamar',                  'LLAMADA', true,  null,  true],
        [11, 'Sin lineas en la competencia',     'LLAMADA', true,  false, false],
        [12, 'Deuda en sistemas',                'LLAMADA', true,  false, false],
        [13, 'Mala cobertura',                   'LLAMADA', true,  false, false],
        [14, 'Ningun contacto existe',           'LLAMADA', false, false, false],
        [15, 'Desea Fija',                       'VISITA',  true,  true,  false],
        [16, 'Desea Movil',                      'VISITA',  true,  true,  false],
        [17, 'Desea Fija y movil',               'VISITA',  true,  true,  false],
        [18, 'Desea Renovar',                    'VISITA',  true,  true,  false],
        [19, 'Reclamo/Post venta',               'VISITA',  true,  false, false],
        [20, 'Fidelizado c/competencia',         'VISITA',  true,  false, false],
        [21, 'Sin contacto/No atendieron',       'VISITA',  false, false, false],
        [22, 'No apto (sunat / otro)',           'VISITA',  false, false, false],
        [23, 'No interesado',                    'VISITA',  true,  false, false],
        [24, 'Volver a visitar/Atendio Tercero', 'VISITA',  true,  null,  true],
        [25, 'Sin lineas en la competencia',     'VISITA',  true,  false, false],
        [26, 'Deuda en sistemas',                'VISITA',  true,  false, false],
        [27, 'Mala cobertura',                   'VISITA',  true,  false, false],
        [28, 'Direccion no existe',              'VISITA',  false, false, false],
        [29, 'Cliente con servicio en planta',   'LLAMADA', true,  true,  true],
    ];

    public function run(): void
    {
        $rows = array_map(static fn (array $r): array => [
            'id_respuesta' => $r[0],
            'descripcion' => $r[1],
            'tipo' => $r[2],
            'contactabilidad' => $r[3],
            'negociacion' => $r[4],
            'volverllamar' => $r[5],
        ], self::CATALOGO);

        Respuesta::query()->insert($rows);
    }
}
