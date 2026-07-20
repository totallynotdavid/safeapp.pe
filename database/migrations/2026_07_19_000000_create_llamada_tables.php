<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Baseline schema for contact logging. `respuesta` is the fixed catalog of
// dispositions an agent can choose. `llamada` is the append-only log of every
// contact recorded against a customer, joined by id_cliente.
//
// Types are normalized from the legacy all-text DDL: ids to integers, SI/NO
// flags to booleans, fecha to datetime. Like the other baselines these CREATEs
// only run against fresh databases, never the prod dump.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('respuesta', static function (Blueprint $table) {
            $table->increments('id_respuesta');
            $table->string('descripcion', 150);
            // The channel this disposition belongs to, LLAMADA or VISITA. It is
            // the source of llamada.gestion, which is why the form never asks.
            $table->string('tipo', 20);
            // Outcome flags legacy stored as SI/NO/NULL text. Nullable because
            // some dispositions genuinely leave an outcome unstated.
            $table->boolean('contactabilidad')->nullable();
            $table->boolean('negociacion')->nullable();
            $table->boolean('volverllamar')->nullable();
        });

        Schema::create('llamada', static function (Blueprint $table) {
            $table->increments('id_llamada');
            $table->unsignedInteger('id_usuario');
            $table->unsignedInteger('id_respuesta');
            $table->dateTime('fecha');

            // The customer this contact was logged against, and the campaign
            // record that put them in front of the agent. id_dato is nullable
            // because a contact outlives the campaign that prompted it: the
            // customer is the permanent fact, the campaign is not.
            $table->unsignedInteger('id_cliente');
            $table->unsignedInteger('id_dato')->nullable();

            // The point of contact.
            $table->string('razon_social', 255)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('nombre_contacto', 150)->nullable();
            $table->string('correo', 150)->nullable();
            $table->text('comentario')->nullable();

            // Copied from the client at contact time so the log stays readable
            // if the client is later reassigned or resegmented. gestion is the
            // channel, taken from the chosen respuesta.
            $table->string('gestion', 100)->nullable();
            $table->string('tipo_data', 30)->nullable();

            // Lines the client held with each carrier at contact time. One
            // column per carrier, with ownership derived rather than stored.
            // The cutover maps the legacy q_lineas column into q_movistar.
            $table->unsignedInteger('q_movistar')->default(0);
            $table->unsignedInteger('q_entel')->default(0);
            $table->unsignedInteger('q_claro')->default(0);
            $table->unsignedInteger('q_bitel')->default(0);

            // Serves an agent's log by date, and the contact history for one
            // client.
            $table->index(['id_usuario', 'fecha']);
            $table->index('id_cliente');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('llamada');
        Schema::dropIfExists('respuesta');
    }
};
