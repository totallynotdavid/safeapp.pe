<?php

namespace App\Models;

use App\Models\Builders\NcFijaBuilder;
use App\Models\Concerns\Validable;
use Database\Factories\NcFijaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// A nota de cargo fija. The whole lifecycle lives on this one row and each stage
// owns a disjoint set of columns: registro (the ejecutivo) writes the case,
// validación writes the decision, back writes `estado` and everything fulfilment
// produces. No stage writes another's columns, which is why the three screens
// assign explicitly instead of sharing $fillable.
//
// This deliberately shares nothing with NcMovil beyond the validación decision
// (see Validable) and the shape of the period window (see NotaCargoBuilder). The
// two productos have different estado vocabularies, different columns and
// different closing dates; the resemblance between the screens is that they run
// the same three-stage process, not that they hold the same case.
class NcFija extends Model
{
    /** @use HasFactory<NcFijaFactory> */
    use HasFactory;

    use Validable;

    /**
     * The lifecycle, owned by the back office. Cases open as XINGRESAR.
     */
    public const ESTADOS = [
        'XINGRESAR',
        'PENDIENTE',
        'AGENDADO',
        'INICIADO',
        'OBSERVADO',
        'ARBITRAJE',
        'BOLSA',
        'ADR',
        'COMPLETADO',
        'CANCELADO',
    ];

    /**
     * Estados that mean the case is still moving. These are dated by
     * fecha_ingreso with no month bound, so an in-flight case never falls off
     * the queue just because it is old. See NotaCargoBuilder.
     */
    public const ESTADOS_EN_CURSO = [
        'XINGRESAR',
        'PENDIENTE',
        'AGENDADO',
        'INICIADO',
        'OBSERVADO',
        'ARBITRAJE',
        'BOLSA',
        'ADR',
    ];

    /** The estado a case is closed with, and the only one that settles. */
    public const ESTADO_COMPLETADO = 'COMPLETADO';

    public const MODALIDADES = ['ALTA', 'PORTABILIDAD', 'FUNNEL'];

    /** The access technology. */
    public const TECNOLOGIAS = ['FTTH', 'HFC', 'GPON'];

    /** Where the line sits in the network plan. */
    public const NODOS = ['SINNODO', 'NAT', 'NCP', 'VACANTE', 'RESERVADO'];

    public $timestamps = false;

    protected $table = 'nc_fija';

    protected $primaryKey = 'id_cargo';

    /**
     * Every column a screen may write, across all three stages.
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
        'subtipo',
        'descripcion',
        'cargo_fijo',
        'modalidad',
        'contacto',
        'telefono1',
        'telefono2',
        'direccion',
        'coordenadas',
        'tecnologia',
        'zonal',
        'comentario',
        // Back.
        'comentario_back',
        'peticion',
        'contrata',
        'nasignado',
        'caso_sf',
        'nodo',
        'ncfinanciera',
        'ncfe',
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
     * The región the case is for, not the ejecutivo's own tienda. Tienda is the
     * región catalog; see the migration for why the two are not the same thing.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Tienda::class, 'zonal', 'id_tienda');
    }

    // The supervisor on record when the ejecutivo registered the case, which is
    // not necessarily the one that user reports to today.
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Supervisor::class, 'id_supervisor', 'id_supervisor');
    }

    /**
     * @return NcFijaBuilder<self>
     */
    public function newEloquentBuilder($query): NcFijaBuilder
    {
        return new NcFijaBuilder($query);
    }

    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'date',
            'fecha_validacion' => 'date',
            'fecha_actualizacion' => 'date',
            'fecha_liquidacion' => 'date',
            'cargo_fijo' => 'decimal:2',
        ];
    }
}
