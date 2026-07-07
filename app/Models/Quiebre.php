<?php

namespace App\Models;

use Database\Factories\QuiebreFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quiebre extends Model
{
    /** @use HasFactory<QuiebreFactory> */
    use HasFactory;

    // The validation workflow states. estado carries the validation decision;
    // the list filters and the update form both draw their options from here so
    // the allowed set has a single owner.
    public const ESTADOS = ['PENDIENTE', 'ATENDIDO', 'PROCESANDO', 'DEVUELTO'];

    public $timestamps = false;

    protected $table = 'quiebre';

    protected $primaryKey = 'id_quiebre';

    // Only the validation-owned columns are mass-assignable here; registration
    // columns belong to the (still-legacy) REGISTRO screen.
    protected $fillable = ['estado', 'id_validador', 'fecha_validacion', 'casosf', 'comentario_validador'];

    protected function casts(): array
    {
        return [
            'fecha_registro' => 'date',
            'fecha_inicio_averia' => 'date',
            'fecha_activacion' => 'date',
            'fecha_inicio' => 'date',
            'fecha_ticket_atencion' => 'date',
            'fecha_validacion' => 'date',
        ];
    }

    public function ejecutivo(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function validador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_validador', 'id_usuario');
    }

    public function tienda(): BelongsTo
    {
        return $this->belongsTo(Tienda::class, 'zonal_telefonica', 'id_tienda');
    }
}
