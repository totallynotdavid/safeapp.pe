<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('usuario', static function (Blueprint $table): void {
            $table->timestamp('correo_verificado_en')->nullable()->after('correo');
        });

        Schema::create('preferencia_notificacion_usuario', static function (Blueprint $table): void {
            $table->unsignedInteger('id_usuario')->primary();
            $table->boolean('correo_cuenta')->default(true);
            $table->boolean('correo_seguridad')->default(true);
        });

        Schema::create('notificacion_usuario', static function (Blueprint $table): void {
            $table->increments('id_notificacion');
            $table->unsignedInteger('id_usuario')->index();
            $table->string('categoria', 20);
            $table->string('titulo', 120);
            $table->text('descripcion');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacion_usuario');
        Schema::dropIfExists('preferencia_notificacion_usuario');
        Schema::table('usuario', static function (Blueprint $table): void {
            $table->dropColumn('correo_verificado_en');
        });
    }
};
