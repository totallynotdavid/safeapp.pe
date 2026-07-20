<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Who is working a campaign record, and who worked it before.
//
// Legacy kept this as three columns on the work row (`id_usuario`, `id_tienda`,
// `fecha_asignacion`), so releasing a record erased the fact that anyone had
// ever held it. An agent could draw a batch, never call any of it, release it,
// and leave no trace. That is a real call-center failure mode and it was
// invisible.
//
// Named `asignacion_datos` to sit beside `asignacion_tienda` (agent to store).
// Legacy called that one `asigna`; the two are now named for what they assign.
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

            // 1 while the agent holds the record, NULL once released. The column
            // carries no information the dates do not, and exists solely for the
            // unique index below: a unique index ignores NULLs, so this makes
            // "at most one agent holds a record at a time" a constraint the
            // database enforces rather than a rule the application remembers.
            // Same reasoning as the importer's column list, one level deeper:
            // prefer a shape that cannot express the bad state.
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
