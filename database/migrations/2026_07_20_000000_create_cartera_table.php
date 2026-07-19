<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// The catalog of carrier account executives a nota de cargo can be attributed
// to, shown in the screens as "ejecutivo TDP". Despite the name this is not the
// portfolio of clients an agent works (that is `datos`/Cliente); a cartera row
// is one named executive on the carrier side, identified by a portfolio code.
//
//   nombre      the portfolio code, e.g. SC-00058_LIMA
//   propietario the executive's full name, which is what the screens display
//   DNI         their national id, carried so the back-office form can show it
//
// Small and hand-maintained: 22 rows in production, with no screen that edits
// them. Normalized from the legacy all-text DDL; the column name DNI keeps its
// legacy casing so the cutover copy stays a straight column-for-column move.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('cartera', static function (Blueprint $table) {
            $table->increments('id_cartera');
            $table->string('nombre', 100);
            $table->string('propietario', 150);
            $table->unsignedInteger('id_tienda');
            // 1 = selectable. Note this is the opposite polarity to menu.estado
            // and asigna.estado, where 0 is the live state.
            $table->integer('estado')->default(1);
            $table->string('DNI', 20)->nullable();

            // The producto and back-office forms offer the executives of one
            // tienda at a time.
            $table->index(['id_tienda', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cartera');
    }
};
