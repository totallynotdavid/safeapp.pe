<?php

namespace App\Models;

use Database\Factories\LlamadaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id_llamada
 * @property int $id_usuario
 * @property int $id_respuesta
 * @property Carbon $fecha
 * @property int $id_cliente
 * @property int|null $id_dato
 * @property string|null $razon_social
 * @property string|null $telefono
 * @property string|null $nombre_contacto
 * @property string|null $correo
 * @property string|null $comentario
 * @property string|null $gestion
 * @property string|null $tipo_data
 * @property int $q_movistar
 * @property int $q_entel
 * @property int $q_claro
 * @property int $q_bitel
 * @property-read Usuario|null $agente
 * @property-read Respuesta|null $respuesta
 * @property-read Cliente|null $cliente
 * @property-read Dato|null $dato
 */
class Llamada extends Model
{
    /** @use HasFactory<LlamadaFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'llamada';

    protected $primaryKey = 'id_llamada';

    // Exactly the agent-supplied payload. Everything else is force-filled by
    // Dato::registrarGestion.
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

    /** @return BelongsTo<Usuario, $this> */
    public function agente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /** @return BelongsTo<Respuesta, $this> */
    public function respuesta(): BelongsTo
    {
        return $this->belongsTo(Respuesta::class, 'id_respuesta', 'id_respuesta');
    }

    /** @return BelongsTo<Cliente, $this> */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    /** @return BelongsTo<Dato, $this> */
    public function dato(): BelongsTo
    {
        return $this->belongsTo(Dato::class, 'id_dato', 'id_dato');
    }
}
