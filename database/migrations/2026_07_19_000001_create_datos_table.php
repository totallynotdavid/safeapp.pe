<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// The client records agents work: the queue behind GESTION MOVIL and GESTION
// FIJA. Keeps the legacy table name `datos`; the domain term is Cliente.
//
// Two legacy columns are intentionally absent. `cliente_movistar` and
// `q_competencia` were confirmed unpopulated across the full production dump
// before being dropped. Reintroduce either one only with a writer for it.
//
// Types are normalized from the legacy all-text DDL as in the other baselines.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('datos', static function (Blueprint $table) {
            $table->increments('id_cliente');
            // Unique because every join in the app resolves a client through its
            // ruc, so a duplicate silently merges two clients' call histories.
            // Legacy had no constraint and carries 28 duplicated rucs (59 rows),
            // all within one segment; the cutover transform dedupes them.
            $table->string('ruc', 20)->unique();
            $table->string('razon_social', 250)->nullable();

            // Ownership of the record: id_usuario 0 means unassigned (the pool
            // agents draw from), id_tienda scopes it to a store.
            $table->unsignedInteger('id_usuario')->default(0);
            $table->unsignedInteger('id_tienda')->nullable();

            // 0 = still to be worked, 1 = already gestionado. Logging a call is
            // what advances it; Cliente::registrarGestion owns that transition.
            $table->unsignedTinyInteger('estado')->default(0);

            // Two different questions, deliberately in two columns. `origen` is
            // provenance: the import campaign, or AltaDeClientes::ORIGEN when an
            // agent registered the client by hand. `tipo_data` is routing: which
            // queue works it, limited to Cliente::TIPOS_DATA.
            //
            // Legacy conflated them. Client creation wrote its provenance into
            // tipo_data as 'CONVERGENTE' (while also writing 'INHOUSE' into
            // origen, correctly), and since the queue filters on tipo_data, all
            // 2,677 clients created that way became unreachable. The import
            // vocabulary was only ever MOVIL/FIJA, so the cutover transform
            // reclassifies those rows and they stay identifiable by origen.
            $table->string('origen', 500)->nullable();
            $table->string('tipo_data', 30)->nullable();

            // Nullable because legacy stored '0000-00-00' when unassigned. The
            // cutover transform maps that to NULL.
            $table->date('fecha_asignacion')->nullable();

            // Free-text descriptors carried over from the import feed. nodo packs
            // contact scraps ("TELEF: DNI: REPRE:") that no screen parses.
            $table->string('zonal', 100)->nullable();
            $table->text('nodo')->nullable();

            // Lines the client holds with each carrier. One column per carrier,
            // matching `llamada`. Which carrier is ours is derived, never stored.
            $table->unsignedInteger('q_movistar')->default(0);
            $table->unsignedInteger('q_entel')->default(0);
            $table->unsignedInteger('q_claro')->default(0);
            $table->unsignedInteger('q_bitel')->default(0);

            // Serves the queue read (my pending clients in one segment) and
            // lookup by ruc, which is how the call log joins back to a client.
            $table->index(['id_usuario', 'tipo_data', 'estado']);
            $table->index('ruc');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('datos');
    }
};
