<?php

namespace App\Models;

use Database\Factories\LlamadaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// One contact an agent logged against a customer. Append-only: rows are never
// edited, and the accumulated history is what the client screen and the
// seguimiento reports read. Dato::registrarGestion owns the write.
//
// Points at the customer, not at the campaign record that prompted the call, so
// a contact survives the campaign. This is also the one legacy table that always
// referenced clients by their natural key, and correspondingly the only one with
// no orphaned rows in the production dump.
class Llamada extends Model
{
    /** @use HasFactory<LlamadaFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'llamada';

    protected $primaryKey = 'id_llamada';

    // The guard on what a request can set: exactly the agent-supplied payload.
    // Everything else is server state and is force-filled by the write owner.
    protected $fillable = [
        'id_respuesta',
        'telefono',
        'nombre_contacto',
        'correo',
        'comentario',
        'q_movistar',
        'q_entel',
        'q_claro',
        'q_bitel',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'q_movistar' => 'integer',
            'q_entel' => 'integer',
            'q_claro' => 'integer',
            'q_bitel' => 'integer',
        ];
    }

    public function agente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function respuesta(): BelongsTo
    {
        return $this->belongsTo(Respuesta::class, 'id_respuesta', 'id_respuesta');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function dato(): BelongsTo
    {
        return $this->belongsTo(Dato::class, 'id_dato', 'id_dato');
    }
}
