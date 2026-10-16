<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// The sellable-product catalog behind the nota de cargo fija registro form: the
// agent picks a subtipo, then a descripción within it, and the monthly charge
// comes from the matching row.
//
// `tipo` is kept even though only FIJA is read: the cutover carries MOVIL rows
// too.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('producto', static function (Blueprint $table) {
            $table->increments('id');
            $table->string('tipo', 30)->index();
            $table->string('subtipo', 100);
            $table->string('descripcion', 100);
            $table->decimal('cargo_fijo', 10, 2)->default(0);
            // 1 = offerable. A retired product stops appearing without losing
            // the cases sold under it.
            $table->unsignedTinyInteger('estado')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto');
    }
};
