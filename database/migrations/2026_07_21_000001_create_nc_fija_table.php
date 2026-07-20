<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Schema for the nota-de-cargo-fija (nc_fija) lifecycle. Same three stages as
// móvil, each owning a disjoint set of columns: registro (the ejecutivo),
// validación (the validador), back (the back office). Fija has no liquidación
// stage; the back office closes a case itself by setting COMPLETADO and the
// month it settled.
//
// Six of the legacy table's 40 columns are gone, each because the 5,970
// production rows say they hold nothing:
//
//   tipo                  'FIJA' in every row. The table is the tipo.
//   q_lineas              1 in every row, and the form field is disabled. A
//                         fija case is one line.
//   agenda                empty in every row; no code path writes it.
//   id_cartera            0 in every row, though the back form's "ejecutivo
//                         TDP" dropdown is wired and would have populated for
//                         the 1,317 cases whose zonal has carteras. Four years
//                         of nobody choosing one is the answer. Móvil keeps the
//                         column: it is set in 576 of its 2,006 rows.
//   ejecutivo_telefonica  1 row of 5,970, a free-text second spelling of that
//                         same attribution.
//   zonal_telefonica      see `zonal` below.
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
            // The client the case is opened against, replacing legacy's free
            // text ruc + razon_social. Only 1,009 of the 5,970 legacy rows carry
            // a RUC that resolves to a client row, but the rate climbs steeply
            // by year (12% in 2023, 55.6% in 2026): the gap is a backlog from
            // before registering the client first became routine, not a case
            // that cannot have one. The cutover registers a client for each of
            // the 3,219 unresolved RUCs, which is exactly what the app already
            // does at the screen when an agent enters an unknown one.
            $table->unsignedInteger('id_cliente')->index();

            // What was sold. Stored as the strings the catalog offered rather
            // than a producto id, because the case is a record of a sale and the
            // catalog moves underneath it: 335 rows hold a subtipo/descripción
            // pair the catalog no longer has, and 2,029 of the remaining 5,635
            // were sold at a price other than the catalog's current one. A
            // foreign key would have to lie about one or the other.
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
            // Three real technologies spelled 27 ways, because legacy made this
            // a free-text input: FTTH also appears as FFTH, FTTHH, FFTTH, FTHH,
            // FTTTHH, FTTTH, FTH, FTT, FFTT, FIBRA and FIBRA OPTICA; HFC also as
            // FHC, HCF and HF. Three rows hold GPS coordinates, pasted into the
            // wrong field. It is a select now, over the three values those 27
            // collapse to (FIBRA is read as FTTH, which is an inference from the
            // spelling and not something the data states).
            $table->string('tecnologia', 20)->nullable();
            // The región the case is for, chosen on the form, not the
            // ejecutivo's own tienda. Legacy wrote back's correction to a second
            // column, zonal_telefonica, and left this one standing, so the two
            // disagreed in 1,835 of the 4,478 rows where both were set and each
            // screen read whichever it happened to join through. Back corrects
            // this column in place instead.
            $table->unsignedInteger('zonal')->nullable();
            $table->text('comentario')->nullable();

            // Validación stage: the decision, and nothing else. Legacy's write
            // also set ejecutivo_telefonica, which is the dropped column above.
            $table->unsignedInteger('id_validador')->default(0);
            $table->date('fecha_validacion')->nullable();
            $table->text('comentario_validador')->nullable();

            // Back stage: fulfilment. `estado` above is this stage's column.
            //
            // fecha_actualizacion is stamped on every back write, which makes it
            // exactly "when the back office last acted": it is set in all 5,163
            // rows the back has touched and in none of the 806 still sitting at
            // XINGRESAR. fecha_liquidacion is the month a COMPLETADO case
            // settled, and is set in no row that is not COMPLETADO.
            $table->date('fecha_actualizacion')->nullable();
            $table->date('fecha_liquidacion')->nullable();
            $table->unsignedInteger('id_back')->default(0);
            $table->text('comentario_back')->nullable();
            $table->string('peticion', 100)->nullable();
            // The field contractor that installs the line. Free text, as legacy
            // had it: ten real contractors appear, along with single letters, a
            // phone number and three place names. A catalog would be the fix and
            // there is no table to hang it on, so the value is at least trimmed
            // and upper-cased at the form boundary.
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
