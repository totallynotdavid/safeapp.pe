<?php

namespace App\Navigation;

/**
 * Every gestión route is named after the channel a Dato carries in tipo_data,
 * so 'MOVIL' reaches gestion.movil and its children.
 *
 * This lives apart from GestionController because the sidebar needs the same
 * mapping to decide which nav item a detail page belongs under, and a view has
 * no business reaching into a controller to ask.
 */
class RutaDeGestion
{
    public static function index(string $tipoData): string
    {
        return 'gestion.'.mb_strtolower($tipoData);
    }

    public static function agregar(string $tipoData): string
    {
        return self::index($tipoData).'.agregar';
    }
}
