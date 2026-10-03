<?php

namespace App\Models;

use Database\Factories\RolFactory;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id_rol
 * @property string $nombre_rol
 * @property-read EloquentCollection<int, Usuario> $usuarios
 * @property-read EloquentCollection<int, Menu> $menus
 */
class Rol extends Model
{
    /** @use HasFactory<RolFactory> */
    use HasFactory;

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
