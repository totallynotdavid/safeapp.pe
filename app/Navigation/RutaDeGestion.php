<?php

namespace App\Navigation;

/**
 * Every gestión route is named after the tipo_data of its Dato, so 'MOVIL'
 * reaches gestion.movil and its children. The sidebar needs the same mapping,
 * so it lives outside GestionController.
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
