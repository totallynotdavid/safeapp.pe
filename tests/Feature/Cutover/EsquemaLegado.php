<?php

namespace Tests\Feature\Cutover;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Declared here rather than dumped from production because what each test needs
 * is one row shaped to trigger one rule, and because the real export cannot live
 * in the repository: it is 76 MB of real customer data.
 *
 * Every column is nullable text or integer, which is how legacy declared almost
 * all of them: `text NOT NULL` holding '' for absent, and `date NOT NULL`
 * holding '0000-00-00'. Getting that shape right is the point, since most of
 * what the transform does is undo it.
 */
final class EsquemaLegado
{
    public static function construir(): void
    {
        self::tablasDeCampana();
        self::tablasDeCasos();
        self::tablasDeCuentas();
        self::tablasDeCatalogo();
    }

    private static function tablasDeCampana(): void
    {
        $esquema = Schema::connection('legacy');

        $esquema->create('datos', static function (Blueprint $t): void {
            $t->integer('id_cliente', true);
            $t->text('ruc')->nullable();
            $t->text('razon_social')->nullable();
            $t->text('origen')->nullable();
            $t->text('tipo_data')->nullable();
            $t->text('zonal')->nullable();
            $t->text('nodo')->nullable();
            $t->integer('estado')->nullable();
            $t->integer('id_usuario')->nullable();
            $t->integer('id_tienda')->nullable();
            $t->text('fecha_asignacion')->nullable();
            $t->integer('q_movistar')->nullable();
            $t->integer('q_entel')->nullable();
            $t->integer('q_claro')->nullable();
            $t->integer('q_bitel')->nullable();
        });

        $esquema->create('llamada', static function (Blueprint $t): void {
            $t->integer('id_llamada', true);
            $t->text('ruc')->nullable();
            $t->text('razon_social')->nullable();
            $t->integer('id_usuario')->nullable();
            $t->text('fecha')->nullable();
            $t->text('telefono')->nullable();
            $t->text('gestion')->nullable();
            $t->integer('id_respuesta')->nullable();
            $t->integer('q_lineas')->nullable();
            $t->text('nombre_contacto')->nullable();
            $t->text('correo')->nullable();
            $t->text('comentario')->nullable();
            $t->text('tipo_data')->nullable();
            $t->integer('q_claro')->nullable();
            $t->integer('q_entel')->nullable();
            $t->integer('q_bitel')->nullable();
        });
    }

    private static function tablasDeCasos(): void
    {
        self::tablaNcFija();
        self::tablaNcMovil();
        self::tablaQuiebre();
    }

    private static function tablaNcFija(): void
    {
        Schema::connection('legacy')->create('nc_fija', static function (Blueprint $t): void {
            $t->integer('id_cargo', true);
            $t->text('ruc')->nullable();
            $t->text('razon_social')->nullable();
            $t->integer('id_usuario')->nullable();
            $t->integer('id_supervisor')->nullable();
            $t->integer('id_validador')->nullable();
            $t->integer('id_back')->nullable();
            $t->text('fecha_ingreso')->nullable();
            $t->text('fecha_liquidacion')->nullable();
            $t->text('fecha_validacion')->nullable();
            $t->text('fecha_actualizacion')->nullable();
            $t->text('subtipo')->nullable();
            $t->text('descripcion')->nullable();
            $t->float('cargo_fijo')->nullable();
            $t->text('modalidad')->nullable();
            $t->text('contacto')->nullable();
            $t->text('telefono1')->nullable();
            $t->text('telefono2')->nullable();
            $t->text('estado')->nullable();
            $t->text('validacion')->nullable();
            $t->text('direccion')->nullable();
            $t->text('coordenadas')->nullable();
            $t->text('zonal')->nullable();
            $t->text('zonal_telefonica')->nullable();
            $t->text('peticion')->nullable();
            $t->text('nasignado')->nullable();
            $t->text('comentario')->nullable();
            $t->text('comentario_back')->nullable();
            $t->text('comentario_validador')->nullable();
            $t->text('contrata')->nullable();
            $t->text('caso_sf')->nullable();
            $t->text('tecnologia')->nullable();
            $t->text('nodo')->nullable();
            $t->text('ncfinanciera')->nullable();
            $t->text('ncfe')->nullable();
        });
    }

    private static function tablaNcMovil(): void
    {
        Schema::connection('legacy')->create('nc_movil', static function (Blueprint $t): void {
            $t->integer('id_cargo', true);
            $t->text('ruc')->nullable();
            $t->text('razon_social')->nullable();
            $t->integer('id_usuario')->nullable();
            $t->integer('id_supervisor')->nullable();
            $t->integer('id_validador')->nullable();
            $t->integer('id_back')->nullable();
            $t->integer('id_cartera')->nullable();
            $t->text('ejecutivo_tdp')->nullable();
            $t->text('estado')->nullable();
            $t->text('validacion')->nullable();
            $t->text('fecha_ingreso')->nullable();
            $t->text('fecha_validacion')->nullable();
            $t->text('fecha_actualizacion')->nullable();
            $t->text('fecha_liquidacion')->nullable();
            $t->text('fecha_estimada')->nullable();
            $t->integer('q_lineas')->nullable();
            $t->float('cargo_fijo')->nullable();
            $t->text('modalidad')->nullable();
            $t->text('contacto')->nullable();
            $t->text('telefono1')->nullable();
            $t->text('telefono2')->nullable();
            $t->text('comentario_ejecutivo')->nullable();
            $t->text('comentario_validador')->nullable();
            $t->text('comentario_back')->nullable();
            $t->text('comentario_liquidador')->nullable();
            $t->text('zonal')->nullable();
            $t->text('zonal_telefonica')->nullable();
            $t->text('oportunidad')->nullable();
            $t->text('nivel_negociacion')->nullable();
            $t->text('tipo_producto')->nullable();
            $t->text('etapa')->nullable();
            $t->text('nodo')->nullable();
            $t->text('casofwa')->nullable();
            $t->text('ciclo')->nullable();
            $t->text('cuenta_financiera')->nullable();
            $t->text('comision')->nullable();
            $t->text('observacion')->nullable();
            $t->text('descuento')->nullable();
        });
    }

    private static function tablaQuiebre(): void
    {
        Schema::connection('legacy')->create('quiebre', static function (Blueprint $t): void {
            $t->integer('id_quiebre', true);
            $t->text('ruc')->nullable();
            $t->text('razon_social')->nullable();
            $t->text('estado')->nullable();
            $t->text('validacion')->nullable();
            $t->integer('id_usuario')->nullable();
            $t->integer('id_supervisor')->nullable();
            $t->integer('id_validador')->nullable();
            $t->text('fecha_validacion')->nullable();
            $t->text('fecha_inicio_averia')->nullable();
            $t->text('fecha_activacion')->nullable();
            $t->text('fecha_inicio')->nullable();
            $t->text('fecha_ticket_atencion')->nullable();
            $t->text('fecha_registro')->nullable();
            $t->text('casosf')->nullable();
            $t->text('comentario_validador')->nullable();
            $t->text('comentario_ejecutivo')->nullable();
            $t->text('zonal_telefonica')->nullable();
            $t->text('servicio')->nullable();
            $t->text('numero_problema')->nullable();
            $t->text('tipo_averia')->nullable();
            $t->text('problema_equipo')->nullable();
            $t->text('detalle_equipo')->nullable();
            $t->text('ticket_atencion')->nullable();
            $t->text('numero_ticket')->nullable();
            $t->text('contacto1')->nullable();
            $t->text('celular1')->nullable();
            $t->text('correo1')->nullable();
            $t->text('contacto2')->nullable();
            $t->text('celular2')->nullable();
            $t->text('correo2')->nullable();
            $t->text('back')->nullable();
        });
    }

    private static function tablasDeCuentas(): void
    {
        $esquema = Schema::connection('legacy');

        $esquema->create('rol', static function (Blueprint $t): void {
            $t->integer('id_rol', true);
            $t->text('nombre_rol')->nullable();
        });

        $esquema->create('usuario', static function (Blueprint $t): void {
            $t->integer('id_usuario', true);
            $t->text('usuario')->nullable();
            $t->text('clave')->nullable();
            $t->integer('id_rol')->nullable();
            $t->text('personal')->nullable();
            $t->integer('estado')->nullable();
            $t->text('sexo')->nullable();
            $t->integer('id_supervisor')->nullable();
            $t->text('fecha_nacimiento')->nullable();
            $t->text('telefono')->nullable();
            $t->text('correo')->nullable();
        });

        $esquema->create('acceso', static function (Blueprint $t): void {
            $t->integer('id_usuario');
            $t->integer('id_menu');
        });

        $esquema->create('accesoxrol', static function (Blueprint $t): void {
            $t->integer('id_rol');
            $t->integer('id_menu');
        });

        $esquema->create('asigna', static function (Blueprint $t): void {
            $t->integer('id_tienda');
            $t->integer('id_usuario');
            $t->integer('estado');
        });
    }

    private static function tablasDeCatalogo(): void
    {
        $esquema = Schema::connection('legacy');

        $esquema->create('tienda', static function (Blueprint $t): void {
            $t->integer('id_tienda', true);
            $t->text('nombre')->nullable();
        });

        $esquema->create('supervisor', static function (Blueprint $t): void {
            $t->integer('id_supervisor', true);
            $t->text('nombre')->nullable();
            $t->integer('estado')->nullable();
        });

        $esquema->create('cartera', static function (Blueprint $t): void {
            $t->integer('id_cartera', true);
            $t->text('nombre')->nullable();
            $t->text('propietario')->nullable();
            $t->integer('id_tienda')->nullable();
            $t->integer('estado')->nullable();
            $t->text('DNI')->nullable();
        });

        $esquema->create('producto', static function (Blueprint $t): void {
            $t->integer('id', true);
            $t->text('tipo')->nullable();
            $t->text('subtipo')->nullable();
            $t->text('descripcion')->nullable();
            $t->float('cf')->nullable();
            $t->integer('estado')->nullable();
        });
    }
}
