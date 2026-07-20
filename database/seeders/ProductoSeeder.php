<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;

/**
 * The fija product catalog the nota de cargo registro form offers.
 *
 * Reference data, like the menus and the disposition catalog, so it runs in
 * every environment. These are the production rows, trimmed and upper-cased:
 * the live table holds 65 that collapse to these 63, the duplicates being
 * 'solo TV'/'SOLO TV' and two identical 'movistar voz' rows. Casing is the only
 * thing normalized.
 *
 * Two things are left exactly as the business has them, because they are not
 * typos. A subtipo can appear at two prices for the same speed (MONO BA 200MB at
 * 69.90 and 89.90, DUO BA + TV FLEX at four such pairs), which is why the form
 * selects a catalog row by id and shows the price on the option rather than
 * keying on the subtipo/descripción pair. And MONO LINEA still reads MOVISTAR
 * VOZ, from before the business moved to Entel; renaming it belongs with the
 * rest of the operator transition, not here.
 */
class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $catalogo = [
            ['DUO (INT+TV) FLEX IP', '100MB',        119.9],
            ['DUO (INT+TV) FLEX IP', '300MB',        139.9],
            ['DUO (INT+TV) FLEX IP', '600MB',        159.9],
            ['DUO (INT+TV) FLEX IP', '1000MB',       229.9],
            ['DUO (INT+TV) IP',      '100MB',        139.9],
            ['DUO (INT+TV) IP',      '300MB',        159.9],
            ['DUO (INT+TV) IP',      '600MB',        179.9],
            ['DUO (INT+TV) IP',      '1000MB',       239.9],
            ['DUO (INT+TV) IP',      '1000MB',       249.9],
            ['DUO BA',               '150MB',        79.9],
            ['DUO BA',               '200MB',        79.9],
            ['DUO BA',               '300MB',        99.9],
            ['DUO BA',               '400MB',        99.9],
            ['DUO BA',               '600MB',        109.9],
            ['DUO BA',               '600MB',        119.9],
            ['DUO BA',               '1000MB',       159.9],
            ['DUO BA',               '1000MB',       179.9],
            ['DUO BA + TV',          '150MB',        149.9],
            ['DUO BA + TV',          '200MB',        149.9],
            ['DUO BA + TV',          '300MB',        169.9],
            ['DUO BA + TV',          '400MB',        169.9],
            ['DUO BA + TV',          '600MB',        189.9],
            ['DUO BA + TV',          '1000MB',       229.9],
            ['DUO BA + TV FLEX',     '100MB',        119.9],
            ['DUO BA + TV FLEX',     '150MB',        129.9],
            ['DUO BA + TV FLEX',     '200MB',        129.9],
            ['DUO BA + TV FLEX',     '200MB',        139.9],
            ['DUO BA + TV FLEX',     '300MB',        139.9],
            ['DUO BA + TV FLEX',     '300MB',        149.9],
            ['DUO BA + TV FLEX',     '600MB',        159.9],
            ['DUO BA + TV FLEX',     '600MB',        169.9],
            ['DUO BA + TV FLEX',     '1000MB',       209.9],
            ['DUO BA + TV FLEX',     '1000MB',       229.9],
            ['DUO BA TV',            '150MB',        149.9],
            ['DUO BA TV FLEX',       '150MB',        129.9],
            ['DUO TV',               'TV + FIJO',    90],
            ['DUO TV',               'TV + FIJO',    129.9],
            ['MONO BA',              '50MB',         59.9],
            ['MONO BA',              '150MB',        69.9],
            ['MONO BA',              '200MB',        69.9],
            ['MONO BA',              '200MB',        89.9],
            ['MONO BA',              '300MB',        89.9],
            ['MONO BA',              '400MB',        89.9],
            ['MONO BA',              '600MB',        99.9],
            ['MONO BA',              '600MB',        109.9],
            ['MONO BA',              '1000MB',       149.9],
            ['MONO BA',              '1000MB',       169.9],
            ['MONO BA IP',           '200MB',        119.9],
            ['MONO BA IP',           '300MB',        129.9],
            ['MONO BA IP',           '400MB',        139.9],
            ['MONO BA IP',           '600MB',        159.9],
            ['MONO BA IP',           '1000MB',       199.9],
            ['MONO LINEA',           'MOVISTAR VOZ', 46.88],
            ['MONO TV',              'SOLO TV',      134.9],
            ['TRIO',                 '150MB',        159.9],
            ['TRIO',                 '200MB',        179.9],
            ['TRIO',                 '300MB',        179.9],
            ['TRIO',                 '600MB',        199.9],
            ['TRIO',                 '1000MB',       239.9],
            ['TRIO IP',              '100MB',        149.9],
            ['TRIO IP',              '300MB',        169.9],
            ['TRIO IP',              '600MB',        189.9],
            ['TRIO IP',              '1000MB',       259.9],
        ];

        Producto::insert(array_map(static fn (array $fila): array => [
            'tipo' => Producto::FIJA,
            'subtipo' => $fila[0],
            'descripcion' => $fila[1],
            'cargo_fijo' => $fila[2],
            'estado' => Producto::OFRECIBLE,
        ], $catalogo));
    }
}
