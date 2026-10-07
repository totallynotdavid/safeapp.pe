<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * An agent's hold on one campaign record, open while they are working it and
 * closed when they release it.
 *
 * Rows are kept after release. Legacy overwrote the three assignment columns on
 * the work row, so a released record forgot it had ever been held: an agent
 * could draw a batch, never call any of it, hand it back, and leave no trace.
 *
 * @property int $id_asignacion
 * @property int $id_dato
 * @property int $id_usuario
 * @property int $id_tienda
 * @property Carbon $fecha_asignacion
 * @property Carbon|null $fecha_liberacion
 * @property int|null $activa
 * @property-read Dato $dato
 * @property-read Usuario|null $agente
 * @property-read Tienda|null $tienda
 */
class AsignacionDato extends Model
{
    // Written while the hold is open, NULL once released. Only ever this value;
    // it exists so the unique index on (id_dato, activa) can enforce one holder
    // at a time, because a unique index ignores NULLs.
    public const ACTIVA = 1;

    public $timestamps = false;

    protected $table = 'asignacion_datos';

    protected $primaryKey = 'id_asignacion';

    // Every column is server state decided by DistribucionDeDatos or
    // AltaDeClientes; none of it is ever agent input.
    protected $fillable = [
        'id_dato',
        'id_usuario',
        'id_tienda',
        'fecha_asignacion',
        'fecha_liberacion',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'fecha_asignacion' => 'date',
            'fecha_liberacion' => 'date',
        ];
    }

    /** @return BelongsTo<Dato, $this> */
    public function dato(): BelongsTo
    {
        return $this->belongsTo(Dato::class, 'id_dato', 'id_dato');
    }

    /** @return BelongsTo<Usuario, $this> */
    public function agente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /** @return BelongsTo<Tienda, $this> */
    public function tienda(): BelongsTo
    {
        return $this->belongsTo(Tienda::class, 'id_tienda', 'id_tienda');
    }
}
