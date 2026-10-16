<?php

namespace App\Models;

use Database\Factories\DatoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id_dato
 * @property int $id_cliente
 * @property string $origen
 * @property string $tipo_data
 * @property int $estado
 * @property string|null $zonal
 * @property string|null $nodo
 * @property int $q_movistar
 * @property int $q_entel
 * @property int $q_claro
 * @property int $q_bitel
 * @property-read Cliente $cliente
 * @property-read AsignacionDato|null $asignacionActiva
 * @property-read EloquentCollection<int, AsignacionDato> $asignaciones
 * @property-read EloquentCollection<int, Llamada> $llamadas
 *
 * @method static Builder<Dato> enCola(int $idAgente, ?int $idTienda, string $tipoData)
 */
class Dato extends Model
{
    /** @use HasFactory<DatoFactory> */
    use HasFactory;

    public const ESTADO_PENDIENTE = 0;

    public const ESTADO_GESTIONADO = 1;

    // Holds only segments that have a screen, because it decides which queue works
    // the record. Where a customer came from goes in `origen`.
    public const TIPOS_DATA = ['MOVIL', 'FIJA'];

    public $timestamps = false;

    protected $table = 'datos';

    protected $primaryKey = 'id_dato';

    // No $fillable: no column is ever request input. The line counts an agent
    // submits are recorded on the llamada instead.

    protected function casts(): array
    {
        return [
            'estado' => 'integer',
            'q_movistar' => 'integer',
            'q_entel' => 'integer',
            'q_claro' => 'integer',
            'q_bitel' => 'integer',
        ];
    }

    /**
     * The llamada insert and the estado change stay in one transaction, so a
     * failure cannot leave a logged call on a record that never leaves the queue.
     *
     * @param  array{id_respuesta: int, ...<string, int|string|null>}  $contacto  validated payload from StoreGestionRequest
     */
    public function registrarGestion(int $idAgente, array $contacto): Llamada
    {
        return $this->getConnection()->transaction(function () use ($idAgente, $contacto): Llamada {
            $llamada = new Llamada($contacto);
            $llamada->forceFill([
                'id_dato' => $this->id_dato,
                'id_cliente' => $this->id_cliente,
                'id_usuario' => $idAgente,
                'razon_social' => $this->cliente->razon_social,
                'tipo_data' => $this->tipo_data,
                'gestion' => Respuesta::query()->whereKey($contacto['id_respuesta'])->value('tipo'),
                'fecha' => now(),
            ])->save();

            // The line mix is recorded on the call, never written back over what the
            // file delivered, so a load and a person never share a mutable field.
            $this->forceFill(['estado' => self::ESTADO_GESTIONADO])->save();

            return $llamada;
        });
    }

    /**
     * @param  Builder<Dato>  $query
     * @return Builder<Dato>
     */
    public function scopeEnCola(Builder $query, int $idAgente, ?int $idTienda, string $tipoData): Builder
    {
        return $query
            ->where('estado', self::ESTADO_PENDIENTE)
            ->where('tipo_data', $tipoData)
            ->whereHas(
                'asignacionActiva',
                static fn (Builder $q): Builder => $q
                    ->where('id_usuario', $idAgente)
                    ->where('id_tienda', $idTienda),
            );
    }

    /** @return BelongsTo<Cliente, $this> */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    /**
     * At most one row matches: the unique index on (id_dato, activa) enforces it.
     *
     * @return HasOne<AsignacionDato, $this>
     */
    public function asignacionActiva(): HasOne
    {
        return $this
            ->hasOne(AsignacionDato::class, 'id_dato', 'id_dato')
            ->where('activa', AsignacionDato::ACTIVA);
    }

    /** @return HasMany<AsignacionDato, $this> */
    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionDato::class, 'id_dato', 'id_dato');
    }

    /** @return HasMany<Llamada, $this> */
    public function llamadas(): HasMany
    {
        return $this->hasMany(Llamada::class, 'id_dato', 'id_dato');
    }
}
