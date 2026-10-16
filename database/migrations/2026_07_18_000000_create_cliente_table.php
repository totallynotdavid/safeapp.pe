<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Who a customer is, and nothing else. A row is never deleted and never owned
// by anyone: imports upsert it, and `datos`, `llamada`, `nc_fija`, `nc_movil`
// and `quiebre` all point at it.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('cliente', static function (Blueprint $table) {
            $table->increments('id_cliente');

            // The natural key. A work queue may list a customer twice. This
            // table may not.
            $table->string('ruc', 20)->unique();
            $table->string('razon_social', 250)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente');
    }
};
