<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    public $timestamps = false;

    protected $table = 'rol';

    protected $primaryKey = 'id_rol';

    protected $fillable = ['nombre_rol'];

    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'id_rol', 'id_rol');
    }

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'accesoxrol', 'id_rol', 'id_menu');
    }
}
