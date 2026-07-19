<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Baseline schema for the nota-de-cargo-móvil (nc_movil) lifecycle. This is the
// spine of the whole notacargo_movil family (producto entry, back office,
// validación, liquidación, reports); it is created once here and reused as each
// of those stages ports. Only the LIQUIDACIÓN stage is being ported now, so the
// producto/validación/back columns are modeled nullable and get tightened when
// their screens port. Types are normalized from the legacy all-text DDL the same
// way the quiebre baseline was: ids to integers, dates to date, money to decimal.
// Like the other baselines these CREATEs only run against fresh databases
// (tests, from-scratch dev), never the prod dump.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('nc_movil', static function (Blueprint $table) {
            $table->increments('id_cargo');

            // Producto stage (registration): the case as the ejecutivo entered it.
            $table->string('estado', 30)->default('INGRESADO');
            $table->unsignedInteger('id_usuario');
            $table->unsignedInteger('id_supervisor')->default(0);
            $table->date('fecha_ingreso')->nullable();
            $table->unsignedInteger('q_lineas')->default(0);
            $table->decimal('cargo_fijo', 10, 2)->default(0);
            $table->string('modalidad', 100)->nullable();
            $table->string('tipo', 100)->nullable();
            $table->string('ruc', 20)->nullable();
            $table->string('razon_social', 255)->nullable();
            $table->string('contacto', 100)->nullable();
            $table->string('telefono1', 50)->nullable();
            $table->string('telefono2', 50)->nullable();
            $table->text('comentario_ejecutivo')->nullable();
            // The carrier account executive ("ejecutivo TDP") the case is
            // attributed to; 0 until one is chosen. Legacy carried two columns
            // for this: producto wrote the id into a text `ejecutivo_tdp` and
            // back-office wrote it into an int `id_cartera`, and different
            // screens joined cartera through different ones, so they could
            // disagree about who owns a case. Across the 2,006 production rows
            // they do: ejecutivo_tdp holds the real data (576 rows, 21 distinct
            // ids), id_cartera is set in 3, and in the 2 rows where both are set
            // the values differ (85 vs 65, 34 vs 87). So the two collapse to
            // this column and the cutover copies ejecutivo_tdp into it, taking
            // the populated column as authoritative.
            $table->unsignedInteger('id_cartera')->default(0);
            // Legacy stored this as text holding an id_tienda; it is a tienda FK,
            // modeled as an integer (the cutover transform casts it).
            $table->unsignedInteger('zonal')->nullable();

            // Validación stage: the validator's decision.
            $table->unsignedInteger('id_validador')->default(0);
            $table->string('validacion', 30)->nullable();
            $table->date('fecha_validacion')->nullable();
            $table->text('comentario_validador')->nullable();

            // Back-office / seguimiento stage.
            $table->date('fecha_actualizacion')->nullable();
            $table->unsignedInteger('id_back')->default(0);
            $table->text('comentario_back')->nullable();
            $table->string('oportunidad', 100)->nullable();
            $table->string('ejecutivo_telefonica', 100)->nullable();
            $table->string('zonal_telefonica', 100)->nullable();
            $table->string('nodo', 100)->nullable();
            $table->string('nivel_negociacion', 100)->nullable();
            $table->date('fecha_estimada')->nullable();
            $table->string('casofwa', 100)->nullable();
            $table->string('tipo_producto', 100)->nullable();
            $table->string('etapa', 100)->nullable();
            $table->string('tipo_etapa', 100)->nullable();

            // Liquidación stage: the only columns this port writes. comision and
            // descuento are kept as strings to match the legacy free-form text
            // columns (they may hold values like "S/ 100" or "10%"); a future
            // tightening to decimal can happen once the real content is known.
            $table->string('comision', 50)->nullable();
            $table->string('observacion', 50)->nullable();
            $table->string('descuento', 50)->nullable();
            $table->date('fecha_liquidacion')->nullable();
            $table->text('comentario_liquidador')->nullable();

            // Legacy columns not read by any ported stage; kept so the schema maps.
            $table->string('ciclo', 50)->nullable();
            $table->string('cuenta_financiera', 100)->nullable();

            // The liquidación queue lists validated cases by year of update.
            $table->index(['validacion', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nc_movil');
    }
};
