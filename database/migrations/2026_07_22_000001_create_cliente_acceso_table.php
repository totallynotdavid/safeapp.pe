<?php

use App\Cockpit\BackfillDeAccesosClientes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cliente_acceso', static function (Blueprint $table) {
            $table->increments('id_acceso');
            $table->unsignedInteger('id_cliente');
            $table->unsignedInteger('id_usuario');
            $table->timestamp('fecha_otorgado')->useCurrent();
            $table->unique(['id_cliente', 'id_usuario']);
            $table->index('id_usuario');
            $table->foreign('id_cliente')->references('id_cliente')->on('cliente')->cascadeOnDelete();
            $table->foreign('id_usuario')->references('id_usuario')->on('usuario')->cascadeOnDelete();
        });

        BackfillDeAccesosClientes::ejecutar();
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_acceso');
    }
};
