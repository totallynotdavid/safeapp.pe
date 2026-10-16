<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// One customer on one campaign list: what the business calls "un dato".
//
// A row holds campaign facts only: which campaign delivered this customer,
// which queue works them, and what the file said about them. Identity is in
// `cliente` and assignment in `asignacion_datos`.
//
// That split makes "an import never overwrites what a person recorded" a shape
// rather than a rule: a load inserts campaign rows and cannot reach identity,
// ownership or progress, because they are not here.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('datos', static function (Blueprint $table) {
            $table->increments('id_dato');
            $table->unsignedInteger('id_cliente');

            // Provenance: the campaign that delivered this customer, or
            // AltaDeClientes::ORIGEN when an agent registered them by hand.
            $table->string('origen', 100);

            // Routing: which queue works the record, limited to Dato::TIPOS_DATA.
            // Distinct from origen: the queue filters on this column, so a
            // campaign name here would make the record unreachable.
            $table->string('tipo_data', 30);

            // 0 = still to be worked, 1 = already gestionado. A property of the
            // campaign record, not of the customer: the same customer can be
            // pending on a new campaign while worked on an old one. Logging a
            // contact advances it, and Dato::registrarGestion owns that.
            $table->unsignedTinyInteger('estado')->default(0);

            // Free-text descriptors carried over from the import feed. nodo packs
            // contact scraps ("TELEF: DNI: REPRE:") that no screen parses.
            $table->string('zonal', 100)->nullable();
            $table->text('nodo')->nullable();

            // Lines the customer held with each carrier according to the file.
            // The agent's own correction is recorded per contact on `llamada`,
            // so these stay as the campaign delivered them and no single mutable
            // field is written by both a load and a person.
            $table->unsignedInteger('q_movistar')->default(0);
            $table->unsignedInteger('q_entel')->default(0);
            $table->unsignedInteger('q_claro')->default(0);
            $table->unsignedInteger('q_bitel')->default(0);

            // The natural key of a campaign record, and what makes a reload
            // idempotent: loading the same file twice updates these rows instead
            // of duplicating them. A customer legitimately appears once per
            // campaign per segment.
            $table->unique(['id_cliente', 'origen', 'tipo_data']);

            // Serves the pool read: unworked records of one segment in one
            // campaign, which is the query behind every batch assignment.
            $table->index(['tipo_data', 'estado', 'origen']);

            $table->foreign('id_cliente')->references('id_cliente')->on('cliente')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('datos');
    }
};
