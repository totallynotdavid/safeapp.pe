<?php

namespace App\Models;

use Database\Factories\NcMovilFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// A nota de cargo móvil. The full lifecycle (producto → validación → back →
// liquidación) lives on this one row; the liquidación stage is the only one
// ported so far, so this model exposes just that stage's write surface and the
// relations that stage reads.
class NcMovil extends Model
{
    /** @use HasFactory<NcMovilFactory> */
    use HasFactory;

    // Only cases the validator passed reach the liquidación queue.
    public const VALIDACION_PROCEDE = 'PROCEDE';

    // Settlement outcomes the liquidador chooses, and the states the queue
    // filters by. Both drive the form/list options so the allowed set has a
    // single owner. LIQUIDADO is the only filter state that is not also an
    // observación (it marks a case already settled).
    public const OBSERVACIONES = ['DEUDA', 'BAJA', 'BAJA PARCIAL', 'PORT OUT', 'SUSPENSION', 'ACUERDO COMERCIAL'];

    public const ESTADOS_LIQUIDACION = ['LIQUIDADO', ...self::OBSERVACIONES];

    public $timestamps = false;

    protected $table = 'nc_movil';

    protected $primaryKey = 'id_cargo';

    // The liquidación stage writes only these five columns; every other column
    // belongs to an upstream stage and is set by the seeder/factory or the
    // still-legacy screens, never mass-assigned here.
    protected $fillable = [
        'comision',
        'observacion',
        'descuento',
        'fecha_liquidacion',
        'comentario_liquidador',
    ];

    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'date',
            'fecha_validacion' => 'date',
            'fecha_actualizacion' => 'date',
            'fecha_estimada' => 'date',
            'fecha_liquidacion' => 'date',
            'cargo_fijo' => 'decimal:2',
            'q_lineas' => 'integer',
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
        return $this->belongsTo(Tienda::class, 'zonal', 'id_tienda');
    }
}
