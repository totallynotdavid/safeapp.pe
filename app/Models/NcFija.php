<?php

namespace App\Models;

use App\Models\Builders\NcFijaBuilder;
use App\Models\Concerns\Validable;
use Database\Factories\NcFijaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id_cargo
 * @property string $estado
 * @property string $validacion
 * @property int $id_usuario
 * @property int $id_supervisor
 * @property-read Carbon|null $fecha_ingreso
 * @property-write Carbon|string|null $fecha_ingreso
 * @property int $id_cliente
 * @property string|null $subtipo
 * @property string|null $descripcion
 * @property string $cargo_fijo
 * @property string|null $modalidad
 * @property string|null $contacto
 * @property string|null $telefono1
 * @property string|null $telefono2
 * @property string|null $direccion
 * @property string|null $coordenadas
 * @property string|null $tecnologia
 * @property int|null $zonal
 * @property string|null $comentario
 * @property int $id_validador
 * @property-read Carbon|null $fecha_validacion
 * @property-write Carbon|string|null $fecha_validacion
 * @property string|null $comentario_validador
 * @property-read Carbon|null $fecha_actualizacion
 * @property-write Carbon|string|null $fecha_actualizacion
 * @property Carbon|null $fecha_liquidacion
 * @property int $id_back
 * @property string|null $comentario_back
 * @property string|null $peticion
 * @property string|null $contrata
 * @property string|null $nasignado
 * @property string|null $caso_sf
 * @property string $nodo
 * @property string|null $ncfinanciera
 * @property string|null $ncfe
 * @property-read Cliente|null $cliente
 * @property-read Usuario|null $ejecutivo
 * @property-read Usuario|null $validador
 * @property-read Tienda|null $region
 * @property-read Supervisor|null $supervisor
 */
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
