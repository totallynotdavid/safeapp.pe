<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// The supervisor lookup the personnel screens display. usuario.id_supervisor
// points here; the column stays 0 (no row) until a user is assigned, so reads
// are an optional relation, not an inner join. Normalized from the legacy
// all-text DDL: nombre text -> string, estado kept as the legacy integer flag.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('supervisor', static function (Blueprint $table) {
            $table->increments('id_supervisor');
            $table->string('nombre', 100);
            $table->integer('estado')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervisor');
    }
};
