<?php

namespace App\Models;

use App\Models\Builders\NcFijaBuilder;
use App\Models\Concerns\Validable;
use Database\Factories\NcFijaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Registro, validación and back office own separate parts of the row.
class NcFija extends Model
{
    /** @use HasFactory<NcFijaFactory> */
    use HasFactory;

    use Validable;

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

    // In-flight cases stay visible regardless of age.
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

    public const ESTADO_COMPLETADO = 'COMPLETADO';

    public const MODALIDADES = ['ALTA', 'PORTABILIDAD', 'FUNNEL'];

    public const TECNOLOGIAS = ['FTTH', 'HFC', 'GPON'];

    public const NODOS = ['SINNODO', 'NAT', 'NCP', 'VACANTE', 'RESERVADO'];

    public $timestamps = false;

    protected $table = 'nc_fija';

    protected $primaryKey = 'id_cargo';

    // Each screen fills only from its own validated payload.
    protected $fillable = [
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

    // Region of the case, not the ejecutivo's current tienda.
    public function region(): BelongsTo
    {
        return $this->belongsTo(Tienda::class, 'zonal', 'id_tienda');
    }

    // Supervisor recorded when the case was registered.
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
