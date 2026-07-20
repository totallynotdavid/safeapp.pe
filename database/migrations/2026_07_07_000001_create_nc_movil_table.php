<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Schema for the nota-de-cargo-móvil (nc_movil) lifecycle. One row carries the
// whole case through four stages, each owning a disjoint set of columns and no
// others: registro (the ejecutivo), validación (the validador), back (the back
// office), liquidación (the liquidador). Types are normalized from the legacy
// all-text DDL the same way the quiebre baseline was: ids to integers, dates to
// date, money to decimal. Like the other baselines these CREATEs only run
// against fresh databases (tests, from-scratch dev), never the prod dump.
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
            // The client the case is opened against. Legacy stored ruc and
            // razon_social as free text here and blocked an unknown RUC with a
            // "IR A FUNNEL + AGREGAR CLIENTE" dead-end, so the client row
            // already exists by the time a case is filed: it predates the case
            // in 1,845 of the 1,851 production cases whose RUC resolves. The
            // cutover transform resolves the legacy ruc and registers a client
            // for the 47 rows that match none, whose razon_social they carry.
            $table->unsignedInteger('id_cliente')->index();
            $table->unsignedInteger('q_lineas')->default(0);
            $table->decimal('cargo_fijo', 10, 2)->default(0);
            $table->string('modalidad', 100)->nullable();
            $table->string('contacto', 100)->nullable();
            $table->string('telefono1', 50)->nullable();
            // Legacy marked telefono2 required on a form that had no second
            // number to give, so agents typed "0", "1" or "-1": it is set in 175
            // of 2,006 rows and the content is mostly those placeholders. It is
            // optional here, which is what the data says it always was.
            $table->string('telefono2', 50)->nullable();
            $table->text('comentario_ejecutivo')->nullable();
            // The región the case is for, chosen on the form. This is not the
            // ejecutivo's own tienda: creators assigned to REGION LIMA file for
            // Piura, La Libertad and Lambayeque, and the two differ in 1,390 of
            // the 2,006 rows. Legacy's per-tienda validación and back screens
            // filtered this against the reviewer's own assignment, which is why
            // nobody used them (see the create_nc_movil comment in plan.md
            // item 27). Back may correct it; nothing else writes it.
            $table->unsignedInteger('zonal')->nullable();
            // The carrier account executive ("ejecutivo TDP") the case is
            // attributed to; 0 until one is chosen. Legacy carried two columns
            // for this: registro wrote the id into a text `ejecutivo_tdp` and
            // back wrote it into an int `id_cartera`, and different screens
            // joined cartera through different ones, so they could disagree
            // about who owns a case. Across the 2,006 production rows they do:
            // ejecutivo_tdp holds the real data (576 rows, 21 distinct ids),
            // id_cartera is set in 3, and in the 2 rows where both are set the
            // values differ (85 vs 65, 34 vs 87). So the two collapse to this
            // column and the cutover copies ejecutivo_tdp into it, taking the
            // populated column as authoritative.
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
            // Legacy hardcoded SINNODO on insert and let back change it, which
            // it did in 12 of 2,006 rows. The default lives here so the insert
            // does not have to carry a literal.
            $table->string('nodo', 100)->default('SINNODO');
            $table->string('casofwa', 100)->nullable();
            $table->string('ciclo', 50)->nullable();
            $table->string('cuenta_financiera', 100)->nullable();

            // Liquidación stage. comision and descuento are kept as strings to
            // match the legacy free-form text columns (they may hold values like
            // "S/ 100" or "10%"); a future tightening to decimal can happen once
            // the real content is known. Nothing has populated them yet:
            // comision is empty in all 2,006 production rows.
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
