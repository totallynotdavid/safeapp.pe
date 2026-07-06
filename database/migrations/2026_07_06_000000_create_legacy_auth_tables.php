<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Baseline schema for the legacy auth/RBAC tables, so fresh databases (the
// test suite, a from-scratch build) have the tables the Eloquent models map
// onto. The shared dev/prod database is loaded from the SQL dump and never
// migrated, so these CREATE statements only ever run against new databases.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rol', function (Blueprint $table) {
            $table->increments('id_rol');
            $table->string('nombre_rol', 50);
        });

        Schema::create('menu', function (Blueprint $table) {
            $table->increments('id_menu');
            $table->string('descripcion', 200);
            $table->string('icono', 200);
            // estado is inverted from usuario.estado: 0 means visible/active.
            $table->integer('estado');
            $table->integer('id_ref');
            $table->string('url', 200);
        });

        Schema::create('usuario', function (Blueprint $table) {
            $table->increments('id_usuario');
            $table->string('usuario', 50);
            $table->string('clave', 255);
            $table->integer('id_rol');
            $table->string('personal', 50);
            // 1 means active here (opposite of menu.estado).
            $table->integer('estado');
            $table->string('sexo', 1);
            $table->integer('id_supervisor');
            $table->date('fecha_nacimiento');
            $table->text('telefono');
            $table->text('correo');
        });

        // The legacy pivots have no primary key or unique constraint, so
        // nothing stops duplicate grant rows. Mirrored as-is.
        Schema::create('acceso', function (Blueprint $table) {
            $table->integer('id_menu');
            $table->integer('id_usuario');
        });

        Schema::create('accesoxrol', function (Blueprint $table) {
            $table->integer('id_rol');
            $table->integer('id_menu');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accesoxrol');
        Schema::dropIfExists('acceso');
        Schema::dropIfExists('usuario');
        Schema::dropIfExists('menu');
        Schema::dropIfExists('rol');
    }
};
