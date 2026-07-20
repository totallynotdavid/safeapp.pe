<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// The sellable-product catalog behind the nota de cargo fija registro form: the
// agent picks a subtipo, then a descripción within it, and the monthly charge
// comes from the matching row.
//
// `tipo` is kept even though only FIJA is read today. The production table holds
// 65 FIJA rows and 21 MOVIL ones, and dropping the column would mean the cutover
// silently discards the MOVIL half; no screen reads it (the móvil registro form
// has its own fixed M4/COMBO list), but that is a reason to leave the rows
// alone, not to delete them. AVANZADO is a third tipo the legacy code queries
// and the table has never had a row for, which is half of why that screen was
// dropped: its product dropdowns came up empty.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('producto', static function (Blueprint $table) {
            $table->increments('id');
            $table->string('tipo', 30)->index();
            $table->string('subtipo', 100);
            $table->string('descripcion', 100);
            // Legacy called this `cf`. It is the same quantity as
            // nc_fija.cargo_fijo and now carries the same name.
            $table->decimal('cargo_fijo', 10, 2)->default(0);
            // 1 = offerable. All 65 FIJA rows are 1; the column exists so a
            // retired product stops appearing without losing the cases sold
            // under it.
            $table->unsignedTinyInteger('estado')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto');
    }
};
