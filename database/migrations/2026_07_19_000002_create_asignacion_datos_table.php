<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Who is working a campaign record, and who worked it before.
//
// A release closes a row rather than erasing it, so an agent who draws a batch,
// never calls any of it and releases it leaves a trace.
//
// Named `asignacion_datos` to sit beside `asignacion_tienda` (agent to store):
// the two are named for what they assign.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('asignacion_datos', static function (Blueprint $table) {
            $table->increments('id_asignacion');
            $table->unsignedInteger('id_dato');
            $table->unsignedInteger('id_usuario');
            $table->unsignedInteger('id_tienda');

            $table->date('fecha_asignacion');
            $table->date('fecha_liberacion')->nullable();

            // 1 while the agent holds the record, NULL once released. It carries
            // no information the dates do not, and exists for the unique index
            // below: a unique index ignores NULLs, so "at most one agent holds a
            // record at a time" becomes a database constraint rather than a rule
            // the application remembers.
            $table->unsignedTinyInteger('activa')->nullable();
            $table->unique(['id_dato', 'activa']);

            // The agent's own queue, and the release sweep, both read by holder.
            $table->index(['id_usuario', 'activa']);

            $table->foreign('id_dato')->references('id_dato')->on('datos')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignacion_datos');
    }
};
