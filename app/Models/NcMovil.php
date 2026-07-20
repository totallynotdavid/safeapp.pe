<?php

namespace App\Models;

use App\Models\Builders\NcMovilBuilder;
use App\Models\Concerns\Validable;
use Database\Factories\NcMovilFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// A nota de cargo móvil. The whole lifecycle lives on this one row and each
// stage owns a disjoint set of columns: registro (the ejecutivo) writes the
// case, validación writes the decision, back writes `estado` and the fulfilment
// fields, liquidación writes the settlement. No stage writes another's columns,
// which is why the three screens assign explicitly instead of sharing $fillable.
class NcMovil extends Model
{
    /** @use HasFactory<NcMovilFactory> */
    use HasFactory;

    use Validable;

    /**
     * The lifecycle, owned by the back office. Every one of the 8 estados
     * present in the 2,006 production rows is in here; BOLSA and SALIDAANTICIPADA
     * are offered by the legacy screen and have never been recorded.
     */
    public const ESTADOS = [
        'PENDIENTE',
        'PROCESANDO',
        'INGRESADO',
        'DELIVERY',
        'ACTIVADO',
        'CAIDA',
        'BOLSA',
        'SALIDAANTICIPADA',
        'LIQUIDADO',
        'ADR',
    ];

    /**
     * Estados that mean the case is still moving. These are dated by
     * fecha_ingreso with no month bound, so an in-flight case never falls off
     * the queue just because it is old. See NotaCargoBuilder.
     */
    public const ESTADOS_EN_CURSO = [
        'INGRESADO',
        'PROCESANDO',
        'PENDIENTE',
        'DELIVERY',
        'BOLSA',
        'SALIDAANTICIPADA',
        'ADR',
    ];

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

    public const MODALIDADES = ['ALTA', 'PORTABILIDAD', 'BAM'];

    public const TIPOS_PRODUCTO = ['M4', 'COMBO', 'M4/COMBO'];

    public const ETAPAS = [
        'F1-PERDIDA',
        'F1-GANADA',
        'F2-GESTION',
        'F3-NEGOCIACION',
        'F4-DISENO',
        'F5-DEFINICION',
    ];

    /**
     * Probability of closing, as a percentage. Legacy's dropdown offered these
     * four plus CAIDA, which is an estado and not a probability; no row carries
     * it and the back office owns estado, so the option is gone.
     */
    public const NIVELES_NEGOCIACION = ['25', '50', '75', '100'];

    public $timestamps = false;

    protected $table = 'nc_movil';

    protected $primaryKey = 'id_cargo';

    /**
     * Every column a screen may write, across all four stages.
     *
     * The per-stage boundary is the form request, not this list: each screen
     * fills from its own validated() payload, which is already an allowlist of
     * that stage's columns. This list only keeps unvalidated input out, so the
     * columns no screen ever writes (both estados, the identity and date stamps)
     * stay off it and are assigned by name where a stage owns them.
     */
    protected $fillable = [
        // Registro.
        'id_cliente',
        'zonal',
        'q_lineas',
        'cargo_fijo',
        'modalidad',
        'contacto',
        'telefono1',
        'telefono2',
        'nivel_negociacion',
        'fecha_estimada',
        'tipo_producto',
        'etapa',
        'oportunidad',
        'comentario_ejecutivo',
        // Back.
        'comentario_back',
        'nodo',
        'casofwa',
        'ciclo',
        'cuenta_financiera',
        // Liquidación.
        'comision',
        'observacion',
        'descuento',
        'fecha_liquidacion',
        'comentario_liquidador',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function ejecutivo(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function validador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_validador', 'id_usuario');
    }

    /**
     * The región the case is for, not the ejecutivo's own tienda. The two differ
     * in 1,390 of the 2,006 production rows, so naming this `tienda` would
     * assert something the data denies. Tienda is the región catalog.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Tienda::class, 'zonal', 'id_tienda');
    }

    // The carrier account executive the case is attributed to. Optional:
    // id_cartera stays 0 until the registro or back screen picks one.
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

    /**
     * @return NcMovilBuilder<self>
     */
    public function newEloquentBuilder($query): NcMovilBuilder
    {
        return new NcMovilBuilder($query);
    }

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
}
