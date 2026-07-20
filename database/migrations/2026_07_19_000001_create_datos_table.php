<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// One customer on one campaign list: what the business calls "un dato".
//
// This table used to hold customer identity and work assignment as well. Both
// moved out (see create_cliente_table and create_asignacion_datos_table) and
// what is left is exactly the campaign facts: which campaign delivered this
// customer, which queue works them, and what the file said about them.
//
// The payoff is that "an import never overwrites what a person recorded" stops
// being a rule and becomes a shape. A load inserts campaign rows; it cannot
// reach identity, ownership or progress because they are not here. The
// importer's hand-maintained list of touchable columns is gone with it.
//
// Two legacy columns are intentionally absent. `cliente_movistar` and
// `q_competencia` were confirmed unpopulated across the full production dump
// before being dropped. Reintroduce either one only with a writer for it.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('datos', static function (Blueprint $table) {
            $table->increments('id_dato');
            $table->unsignedInteger('id_cliente');

            // Provenance: the campaign that delivered this customer, or
            // AltaDeClientes::ORIGEN when an agent registered them by hand.
            // Production carries 17 distinct values, the longest 25 characters.
            $table->string('origen', 100);

            // Routing: which queue works the record, limited to Dato::TIPOS_DATA.
            //
            // Legacy conflated this with provenance. Client creation wrote
            // 'CONVERGENTE' here (while also writing 'INHOUSE' into origen,
            // correctly), and since the queue filters on this column, all 2,677
            // clients created that way became unreachable. Keeping the two
            // columns apart is what stops that recurring; requiring both at
            // import time is what stops it arriving from a file.
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
