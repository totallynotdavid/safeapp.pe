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

    public const VALIDACION_RECHAZADO = 'RECHAZADO';

    /**
     * Estados a validated case can still be settled from.
     *
     * Legacy's list was an if/else chain over its own filter dropdown, and only
     * the last branch matched real data: this set (as a UNION of ACTIVADO with
     * the other seven). CAIDA and ADR are deliberately outside it, since a
     * fallen case is not settled, and they are not a rounding error: CAIDA is
     * 399 of the 2,006 production rows. BOLSA and SALIDAANTICIPADA are declared
     * by that legacy query but appear in no row; they stay so the allowlist is
     * the one legacy states rather than one inferred from current stock.
     */
    public const ESTADOS_LIQUIDABLES = [
        'ACTIVADO',
        'INGRESADO',
        'PROCESANDO',
        'PENDIENTE',
        'DELIVERY',
        'BOLSA',
        'SALIDAANTICIPADA',
        'LIQUIDADO',
    ];

    /**
     * The settlement outcome the liquidador records, written to `observacion`.
     *
     * This is not the estado vocabulary and must not be reused as one. Legacy's
     * list screen offered exactly these strings as estado filter values, which
     * no row can match (estado is never any of them in 2,006 production rows),
     * so six of that dropdown's seven options returned nothing by construction.
     */
    public const OBSERVACIONES = ['DEUDA', 'BAJA', 'BAJA PARCIAL', 'PORT OUT', 'SUSPENSION', 'ACUERDO COMERCIAL'];

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

    // The carrier account executive the case is attributed to. Optional:
    // id_cartera stays 0 until the producto or back-office screen picks one.
    public function cartera(): BelongsTo
    {
        return $this->belongsTo(Cartera::class, 'id_cartera', 'id_cartera');
    }

    // The supervisor on record when the ejecutivo registered the case, which is
    // not necessarily the one that user reports to today.
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Supervisor::class, 'id_supervisor', 'id_supervisor');
    }
}
