<?php

namespace App\Models;

use Database\Factories\AsignaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// User -> tienda assignment. estado 0 is the active assignment. The assignment
// module (not yet ported) will own the write side; today this table is read to
// scope operational screens to the operator's tienda.
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
}
