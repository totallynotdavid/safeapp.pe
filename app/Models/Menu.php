<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    public $timestamps = false;

    protected $table = 'menu';

    protected $primaryKey = 'id_menu';

    // estado is inverted from usuario.estado: 0 means visible/active here,
    // not disabled. Verified against vista/menu.php's sidebar query, which
    // filters on `estado = 0`. Left uncast (not boolean) to avoid implying
    // the usual true/false-means-enabled reading.
    protected $fillable = ['descripcion', 'icono', 'estado', 'id_ref', 'url'];

    public function padre(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'id_ref', 'id_menu');
    }

    public function hijos(): HasMany
    {
        return $this->hasMany(Menu::class, 'id_ref', 'id_menu');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'accesoxrol', 'id_menu', 'id_rol');
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(Usuario::class, 'acceso', 'id_menu', 'id_usuario');
    }
}
