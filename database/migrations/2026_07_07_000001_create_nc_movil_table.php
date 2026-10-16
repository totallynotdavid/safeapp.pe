<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// One row carries the whole case through four stages, each owning a disjoint
// set of columns: registro (the ejecutivo), validación (the validador), back
// (the back office), liquidación (the liquidador).
return new class extends Migration {
    public function up(): void
    {
        Schema::create('nc_movil', static function (Blueprint $table) {
            $table->increments('id_cargo');

            // Registro stage: the case as the ejecutivo entered it.
            //
            // Both estados start PENDIENTE and are then owned elsewhere:
            // `validacion` by the validador, `estado` by the back office. The
            // ejecutivo never writes either after the insert.
            $table->string('estado', 30)->default('PENDIENTE');
            $table->string('validacion', 30)->default('PENDIENTE');
            $table->unsignedInteger('id_usuario');
            $table->unsignedInteger('id_supervisor')->default(0);
            $table->date('fecha_ingreso')->nullable();
            // The client row already exists by the time a case is filed.
            $table->unsignedInteger('id_cliente')->index();
            $table->unsignedInteger('q_lineas')->default(0);
            $table->decimal('cargo_fijo', 10, 2)->default(0);
            $table->string('modalidad', 100)->nullable();
            $table->string('contacto', 100)->nullable();
            $table->string('telefono1', 50)->nullable();
            $table->string('telefono2', 50)->nullable();
            $table->text('comentario_ejecutivo')->nullable();
            // The región the case is for, chosen on the form. This is not the
            // ejecutivo's own tienda. Back may correct it. Nothing else writes it.
            $table->unsignedInteger('zonal')->nullable();
            // The carrier account executive ("ejecutivo TDP") the case is
            // attributed to. 0 means that none is chosen.
            $table->unsignedInteger('id_cartera')->default(0);
            $table->string('oportunidad', 100)->nullable();
            $table->string('nivel_negociacion', 10)->nullable();
            $table->date('fecha_estimada')->nullable();
            $table->string('tipo_producto', 100)->nullable();
            $table->string('etapa', 100)->nullable();

            // Validación stage: the validador's decision, and the two figures
            // they are allowed to correct before passing the case on.
            $table->unsignedInteger('id_validador')->default(0);
            $table->date('fecha_validacion')->nullable();
            $table->text('comentario_validador')->nullable();

            // Back stage: fulfilment. `estado` above is this stage's column.
            $table->date('fecha_actualizacion')->nullable();
            $table->unsignedInteger('id_back')->default(0);
            $table->text('comentario_back')->nullable();
            $table->string('nodo', 100)->default('SINNODO');
            $table->string('casofwa', 100)->nullable();
            $table->string('ciclo', 50)->nullable();
            $table->string('cuenta_financiera', 100)->nullable();

            // Liquidación stage. comision and descuento are free-form text: they
            // may hold values like "S/ 100" or "10%".
            $table->string('comision', 50)->nullable();
            $table->string('observacion', 50)->nullable();
            $table->string('descuento', 50)->nullable();
            $table->date('fecha_liquidacion')->nullable();
            $table->text('comentario_liquidador')->nullable();

            // The liquidación queue lists validated cases by year of update; the
            // registro queue lists one ejecutivo's own cases.
            $table->index(['validacion', 'estado']);
            $table->index('id_usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nc_movil');
    }
};
