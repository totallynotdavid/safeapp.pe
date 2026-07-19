<?php

namespace App\Models;

use Database\Factories\AsignaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// User -> tienda assignment. estado 0 is the active assignment. The asignacion
// module owns the write side; operational screens read it to scope their rows
// to the operator's tienda via Usuario::tiendaAsignada().
class Asigna extends Model
{
    /** @use HasFactory<AsignaFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'asigna';

    // No natural primary key in the legacy table; reads go through the
    // (id_usuario, estado) index, never by row id.
    protected $primaryKey = null;

    public $incrementing = false;

    protected $fillable = ['id_tienda', 'id_usuario', 'estado'];

    protected function casts(): array
    {
        return ['estado' => 'integer'];
    }

    public function tienda(): BelongsTo
    {
        return $this->belongsTo(Tienda::class, 'id_tienda', 'id_tienda');
    }
}
