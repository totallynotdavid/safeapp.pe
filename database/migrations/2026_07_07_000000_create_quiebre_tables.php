<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Baseline schema for the averías/quiebre cluster and the assignment lookup the
// validation screen scopes on.
//
// Most quiebre columns are nullable while StoreQuiebreRegistroRequest requires
// them, so the schema is looser than the app. Tightening it waits until after the
// cutover: the 10 production rows are carried across by cutover:transform, and a
// NOT NULL added now would only change what the transform has to backfill.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('tienda', static function (Blueprint $table) {
            $table->increments('id_tienda');
            $table->string('nombre', 50);
        });

        // User -> tienda assignment. estado 0 means the active assignment; the
        // screens that scope on a tienda read it from here. AsignacionController
        // owns the write side.
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
            // Legacy stored this as text holding an id_tienda; it is a tienda FK,
            // so it is modeled as an integer here (the cutover transform casts it).
            $table->unsignedInteger('zonal_telefonica')->nullable();
            // The client the avería is reported for. Legacy stored the ruc and
            // razon_social as free text on this row: it prefilled them from a
            // RUC lookup and then let the modal edit both, so the same RUC could
            // carry different names across quiebres and a quiebre could exist
            // for a RUC no client row matches (7 of the 10 production rows).
            // A quiebre is an open workflow item, not a historical record like
            // llamada, so it references the client and joins for the name. The
            // cutover transform resolves the legacy ruc and registers a client
            // for the orphans, whose razon_social the legacy row still carries.
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
            // Legacy free-text column left unused by both the registration and
            // validation flows; kept so the schema still maps the real table.
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
