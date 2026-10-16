<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Same three stages as móvil, each owning a disjoint set of columns: registro
// (the ejecutivo), validación (the validador), back (the back office). Fija has
// no liquidación stage. The back office closes a case itself by setting
// COMPLETADO and the month it settled.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('nc_fija', static function (Blueprint $table) {
            $table->increments('id_cargo');

            // Registro stage: the case as the ejecutivo entered it.
            //
            // A case opens XINGRESAR ("por ingresar") and PENDIENTE, and neither
            // is the ejecutivo's to change afterwards: `validacion` belongs to
            // the validador and `estado` to the back office.
            $table->string('estado', 30)->default('XINGRESAR');
            $table->string('validacion', 30)->default('PENDIENTE');
            $table->unsignedInteger('id_usuario');
            $table->unsignedInteger('id_supervisor')->default(0);
            $table->date('fecha_ingreso')->nullable();
            $table->unsignedInteger('id_cliente')->index();

            // What was sold. Stored as the strings the catalog offered rather
            // than a producto id, because the case is a record of a sale and the
            // catalog moves underneath it: a foreign key would have to lie about
            // either the product or its price.
            $table->string('subtipo', 100)->nullable();
            $table->string('descripcion', 100)->nullable();
            $table->decimal('cargo_fijo', 10, 2)->default(0);
            $table->string('modalidad', 100)->nullable();

            // Where the line goes and who to call about it.
            $table->string('contacto', 100)->nullable();
            $table->string('telefono1', 50)->nullable();
            $table->string('telefono2', 50)->nullable();
            $table->text('direccion')->nullable();
            $table->string('coordenadas', 100)->nullable();
            $table->string('tecnologia', 20)->nullable();
            // The región the case is for, chosen on the form, not the
            // ejecutivo's own tienda. Back corrects it in place.
            $table->unsignedInteger('zonal')->nullable();
            $table->text('comentario')->nullable();

            // Validación stage: the decision, and nothing else.
            $table->unsignedInteger('id_validador')->default(0);
            $table->date('fecha_validacion')->nullable();
            $table->text('comentario_validador')->nullable();

            // Back stage: fulfilment. `estado` above is this stage's column.
            //
            // fecha_actualizacion is stamped on every back write: when the back
            // office last acted. fecha_liquidacion is the month a COMPLETADO
            // case settled.
            $table->date('fecha_actualizacion')->nullable();
            $table->date('fecha_liquidacion')->nullable();
            $table->unsignedInteger('id_back')->default(0);
            $table->text('comentario_back')->nullable();
            $table->string('peticion', 100)->nullable();
            // The field contractor that installs the line. Free text, with no
            // catalog table, so the value is trimmed and upper-cased at the form
            // boundary.
            $table->string('contrata', 100)->nullable();
            $table->string('nasignado', 100)->nullable();
            $table->string('caso_sf', 100)->nullable();
            $table->string('nodo', 100)->default('SINNODO');
            $table->string('ncfinanciera', 100)->nullable();
            $table->string('ncfe', 100)->nullable();

            // The back queue lists validated cases; the registro queue lists one
            // ejecutivo's own.
            $table->index(['validacion', 'estado']);
            $table->index('id_usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nc_fija');
    }
};
