<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Most quiebre columns are nullable while StoreQuiebreRegistroRequest requires
// them, so the schema is looser than the app.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('tienda', static function (Blueprint $table) {
            $table->increments('id_tienda');
            $table->string('nombre', 50);
        });

        // estado 0 means the active assignment.
        Schema::create('asignacion_tienda', static function (Blueprint $table) {
            $table->unsignedInteger('id_tienda');
            $table->unsignedInteger('id_usuario');
            $table->integer('estado');
            $table->index(['id_usuario', 'estado']);
        });

        Schema::create('quiebre', static function (Blueprint $table) {
            $table->increments('id_quiebre');

            // Validation-owned columns: the only ones this module writes.
            $table->string('estado', 20)->default('PENDIENTE');
            $table->unsignedInteger('id_validador')->default(0);
            $table->date('fecha_validacion')->nullable();
            $table->string('caso_sf', 50)->nullable();
            $table->text('comentario_validador')->nullable();

            // Registration-owned columns, written by QuiebreRegistroController.
            $table->unsignedInteger('id_usuario');
            $table->unsignedInteger('id_supervisor')->default(0);
            // An id_tienda.
            $table->unsignedInteger('zonal_telefonica')->nullable();
            // A quiebre is an open workflow item, not a historical record like
            // llamada, so it references the client and joins for the name.
            $table->unsignedInteger('id_cliente')->index();
            $table->string('servicio', 100)->nullable();
            $table->string('numero_problema', 50)->nullable();
            $table->string('tipo_averia', 100)->nullable();
            $table->string('problema_equipo', 255)->nullable();
            $table->text('detalle_equipo')->nullable();
            $table->date('fecha_inicio_averia')->nullable();
            $table->date('fecha_activacion')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->string('ticket_atencion', 50)->nullable();
            $table->string('numero_ticket', 50)->nullable();
            $table->date('fecha_ticket_atencion')->nullable();
            $table->string('contacto1', 100)->nullable();
            $table->string('celular1', 50)->nullable();
            $table->string('correo1', 100)->nullable();
            $table->string('contacto2', 100)->nullable();
            $table->string('celular2', 50)->nullable();
            $table->string('correo2', 100)->nullable();
            $table->text('comentario_ejecutivo')->nullable();
            $table->string('back', 50)->nullable();
            $table->date('fecha_registro')->nullable();
            // Written by neither the registration nor the validation flow.
            $table->string('validacion', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiebre');
        Schema::dropIfExists('asignacion_tienda');
        Schema::dropIfExists('tienda');
    }
};
