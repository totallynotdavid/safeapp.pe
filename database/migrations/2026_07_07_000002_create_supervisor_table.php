<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// usuario.id_supervisor points here and is 0 (no row) until a user is
// assigned, so reads are an optional relation, not an inner join.
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
