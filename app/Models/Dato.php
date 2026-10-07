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
 * One customer on one campaign list: the unit of work behind GESTION MOVIL and
 * GESTION FIJA. The business calls these "datos", and a single one "un dato".
 *
 * Deliberately not called Cliente: a customer is a person, this is a job. The
 * same customer delivered by two campaigns is two rows here and one in `cliente`,
 * which is what lets a campaign be reloaded without disturbing the customer or
 * the agent working them.
 *
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

    // 0 = still to be worked, 1 = already gestionado.
    public const ESTADO_PENDIENTE = 0;

    public const ESTADO_GESTIONADO = 1;

    // Every segment a record can belong to. This column decides which queue
    // works it, so it holds only segments that have a screen. It is not the
    // place to record where a customer came from: `origen` does that, and
    // conflating the two is what stranded the legacy CONVERGENTE rows.
    public const TIPOS_DATA = ['MOVIL', 'FIJA'];

    public $timestamps = false;

    protected $table = 'datos';

    protected $primaryKey = 'id_dato';

    // No $fillable, so nothing here is mass-assignable. Every column is either
    // campaign data written by the importer or by AltaDeClientes, or progress
    // written by registrarGestion(). None of it is ever request input, and the
    // line counts an agent submits are recorded on the llamada instead.

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
     * Write owner of "logging a contact advances the record's state". The
     * llamada insert and the estado change must stay in one transaction: split
     * them and a failure leaves a logged call on a record that never leaves the
     * queue.
     *
     * @param  array{id_respuesta: int, ...<string, int|string|null>}  $contacto  validated payload from StoreGestionRequest
     */
    public function registrarGestion(int $idAgente, array $contacto): Llamada
    {
        return $this->getConnection()->transaction(function () use ($idAgente, $contacto): Llamada {
            // $fillable guards the agent-supplied half. The rest is server
            // state. The channel comes from the chosen respuesta, never from the
            // request, so a VISITA outcome cannot be filed against a LLAMADA.
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

            // The contact is also the freshest read on the customer's line mix.
            // It is recorded on the call, not written back over what the file
            // delivered, so a load and a person never share a mutable field.
            $this->forceFill(['estado' => self::ESTADO_GESTIONADO])->save();

            return $llamada;
        });
    }

    /**
     * The agent's outstanding queue for one segment, scoped to their active
     * tienda. A record assigned under a tienda the agent no longer holds is
     * intentionally invisible here.
     *
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
     * The agent currently holding this record, if any. At most one row can
     * match: the unique index on (id_dato, activa) makes that a constraint
     * rather than an expectation.
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
