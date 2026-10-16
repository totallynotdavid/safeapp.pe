<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
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
    // NULL once released. The unique index on (id_dato, activa) enforces one
    // holder at a time because a unique index ignores NULLs.
    public const ACTIVA = 1;

    public $timestamps = false;

    protected $table = 'asignacion_datos';

    protected $primaryKey = 'id_asignacion';

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
