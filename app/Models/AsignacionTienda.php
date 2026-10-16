<?php

namespace App\Models;

use Database\Factories\AsignacionTiendaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id_tienda
 * @property int $id_usuario
 * @property int $estado
 * @property-read Tienda|null $tienda
 */
class AsignacionTienda extends Model
{
    /** @use HasFactory<AsignacionTiendaFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'asignacion_tienda';

    // No natural primary key: reads go through the (id_usuario, estado) index.
    protected $primaryKey = null;

    public $incrementing = false;

    protected $fillable = ['id_tienda', 'id_usuario', 'estado'];

    protected function casts(): array
    {
        return ['estado' => 'integer'];
    }

    /** @return BelongsTo<Tienda, $this> */
    public function tienda(): BelongsTo
    {
        return $this->belongsTo(Tienda::class, 'id_tienda', 'id_tienda');
    }
}
