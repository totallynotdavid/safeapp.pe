<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rol', static function (Blueprint $table) {
            $table->increments('id_rol');
            $table->string('nombre_rol', 50);
        });

        Schema::create('menu', static function (Blueprint $table) {
            $table->increments('id_menu');
            $table->string('descripcion', 200);
            $table->string('icono', 200);
            // estado is inverted from usuario.estado: 0 means visible/active.
            $table->integer('estado');
            $table->integer('id_ref');
            // Permission token the RBAC gate checks. Null on section-header
            // rows, which group leaves in the sidebar and grant nothing.
            $table->string('permiso', 200)->nullable();
        });

        Schema::create('usuario', static function (Blueprint $table) {
            $table->increments('id_usuario');
            $table->string('usuario', 50);
            // Null means the account has no password and cannot sign in until
            // one is set.
            $table->string('clave', 255)->nullable();
            $table->integer('id_rol');
            $table->string('personal', 50);
            // 1 means active here (opposite of menu.estado).
            $table->integer('estado');
            $table->string('sexo', 1);
            // 0 means no supervisor assigned.
            $table->integer('id_supervisor')->default(0);
            // Filled by the "datos personal" flow, not at creation.
            $table->date('fecha_nacimiento')->nullable();
            $table->text('telefono')->nullable();
            $table->text('correo')->nullable();
        });

        // The composite key makes each grant unique: a repeated pair is rejected.
        Schema::create('acceso', static function (Blueprint $table) {
            $table->integer('id_usuario');
            $table->integer('id_menu');
            $table->primary(['id_usuario', 'id_menu']);
        });

        Schema::create('accesoxrol', static function (Blueprint $table) {
            $table->integer('id_rol');
            $table->integer('id_menu');
            $table->primary(['id_rol', 'id_menu']);
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
