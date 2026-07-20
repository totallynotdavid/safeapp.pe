<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Who a customer is, and nothing else.
//
// This table exists because the legacy `datos` table was three tables in a
// trench coat: customer identity, campaign membership, and work assignment, all
// in one row with three different lifetimes. Every anomaly the cutover audit
// found traces back to that:
//
//   - 5,015 charge notes and averías (83% of nc_fija) referenced a RUC with no
//     `datos` row. When a campaign list was replaced, customer identity was
//     collateral damage, because identity lived in the campaign row.
//   - `ruc` was not unique in production (28 values over 87 rows). A work queue
//     may legitimately list a customer twice; an identity table may not.
//   - An import could take a client from the agent working it, so the importer
//     had to carry a hand-maintained list of columns it was allowed to touch.
//
// A row here is never deleted and never owned by anyone. Imports upsert it,
// workflows resolve to it, and `datos`, `llamada`, `nc_fija`, `nc_movil` and
// `quiebre` all point at it. That the charge-note tables already referenced
// `id_cliente` is why they needed no change when this table was introduced:
// the column stayed, and the table it names stopped losing rows.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('cliente', static function (Blueprint $table) {
            $table->increments('id_cliente');

            // The natural key. Unique here in a way it could never be on a work
            // queue, which is the whole reason identity is its own table.
            $table->string('ruc', 20)->unique();
            $table->string('razon_social', 250)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente');
    }
};
