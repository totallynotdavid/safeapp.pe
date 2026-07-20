<?php

namespace App\Models;

use App\Cockpit\Operadores;
use Database\Factories\ClienteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

// A client record an agent works: one row of the queue behind GESTION MOVIL and
// GESTION FIJA. Legacy calls the table `datos`; the domain term is Cliente.
class Cliente extends Model
{
    /** @use HasFactory<ClienteFactory> */
    use HasFactory;

    // 0 = still to be worked, 1 = already gestionado.
    public const ESTADO_PENDIENTE = 0;

    public const ESTADO_GESTIONADO = 1;

    // Every segment a client can belong to. This column decides which queue
    // works the client, so it holds only segments that have a screen. It is not
    // the place to record where a client came from: `origen` does that, and
    // conflating the two is what stranded the legacy CONVERGENTE rows (see the
    // create_datos_table migration).
    public const TIPOS_DATA = ['MOVIL', 'FIJA'];

    // An unassigned client: id_usuario 0 is the pool, not a real user.
    public const SIN_AGENTE = 0;

    public $timestamps = false;

    protected $table = 'datos';

    protected $primaryKey = 'id_cliente';

    // Only the line counts are mass-assigned, and only by registrarGestion().
    // Ownership (id_usuario, id_tienda), estado, and the import-fed descriptors
    // are set explicitly by whichever flow owns them.
    protected $fillable = [
        'q_movistar',
        'q_entel',
        'q_claro',
        'q_bitel',
    ];

    protected function casts(): array
    {
        return [
            'fecha_asignacion' => 'date',
            'estado' => 'integer',
            'q_movistar' => 'integer',
            'q_entel' => 'integer',
            'q_claro' => 'integer',
            'q_bitel' => 'integer',
        ];
    }

    /**
     * The single way a workflow resolves a RUC to a client.
     *
     * Legacy had four copies of this (the quiebre and three notacargo screens),
     * each an AJAX endpoint, because each of those forms was a modal that had
     * no client in hand. They exist as one lookup here so the identity a
     * workflow files against comes from one place. `ruc` is unique, so this is
     * a single row or nothing.
     */
    public static function porRuc(string $ruc): ?self
    {
        return self::query()->where('ruc', $ruc)->first();
    }

    public function lineasPropias(): int
    {
        return (int) $this->{Operadores::columna(Operadores::PROPIO)};
    }

    public function lineasCompetencia(): int
    {
        return array_sum(array_map(
            fn (string $operador): int => (int) $this->{Operadores::columna($operador)},
            Operadores::competencia(),
        ));
    }

    /**
     * Write owner of "logging a contact advances the client's state". The
     * llamada insert and the estado change must stay in one transaction: split
     * them and a failure leaves a logged call on a client that never leaves the
     * queue.
     *
     * @param  array<string, mixed>  $contacto  validated payload from StoreGestionRequest
     */
    public function registrarGestion(int $idAgente, array $contacto): Llamada
    {
        return DB::transaction(function () use ($idAgente, $contacto): Llamada {
            // $fillable guards the agent-supplied half. The rest is server
            // state. The channel comes from the chosen respuesta, never from the
            // request, so a VISITA outcome cannot be filed against a LLAMADA.
            $llamada = $this->llamadas()->make($contacto);
            $llamada->forceFill([
                'id_usuario' => $idAgente,
                'razon_social' => $this->razon_social,
                'tipo_data' => $this->tipo_data,
                'gestion' => Respuesta::query()->whereKey($contacto['id_respuesta'])->value('tipo'),
                'fecha' => now(),
            ])->save();

            // The contact is also the freshest read on the client's line mix.
            $this->fill(array_intersect_key($contacto, array_flip($this->getFillable())));
            $this->forceFill(['estado' => self::ESTADO_GESTIONADO])->save();

            return $llamada;
        });
    }

    /**
     * The agent's outstanding queue for one segment, scoped to their active
     * tienda. A client assigned under a tienda the agent no longer holds is
     * intentionally invisible here.
     */
    public function scopeEnCola(Builder $query, int $idAgente, ?int $idTienda, string $tipoData): Builder
    {
        return $query
            ->where('id_usuario', $idAgente)
            ->where('id_tienda', $idTienda)
            ->where('estado', self::ESTADO_PENDIENTE)
            ->where('tipo_data', $tipoData);
    }

    /**
     * The phone numbers known for this client, newest contact first.
     *
     * Derived from the call log, which is the source of truth: a number is known
     * because someone was contacted on it. There is deliberately no phone table
     * to keep in step.
     *
     * @return Collection<int, object{telefono: string, veces: int, ultimo_contacto: string}>
     */
    public function telefonosConocidos(): Collection
    {
        return $this
            ->llamadas()
            ->selectRaw('telefono, COUNT(*) as veces, MAX(fecha) as ultimo_contacto')
            ->whereNotNull('telefono')
            ->where('telefono', '<>', '')
            ->groupBy('telefono')
            ->orderByDesc('ultimo_contacto')
            ->get();
    }

    public function agente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function tienda(): BelongsTo
    {
        return $this->belongsTo(Tienda::class, 'id_tienda', 'id_tienda');
    }

    public function llamadas(): HasMany
    {
        return $this->hasMany(Llamada::class, 'ruc', 'ruc');
    }
}
