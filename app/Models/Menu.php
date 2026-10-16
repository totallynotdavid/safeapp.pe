<?php

namespace App\Models;

use Database\Factories\MenuFactory;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id_menu
 * @property string $descripcion
 * @property string $icono
 * @property int $estado
 * @property int $id_ref
 * @property string|null $permiso
 * @property-read Menu|null $padre
 * @property-read EloquentCollection<int, Menu> $hijos
 * @property-read EloquentCollection<int, Rol> $roles
 * @property-read EloquentCollection<int, Usuario> $usuarios
 */
class Menu extends Model
{
    /** @use HasFactory<MenuFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'menu';

    protected $primaryKey = 'id_menu';

    // estado is inverted from usuario.estado: 0 means visible here. Left uncast
    // so it does not read as true-means-enabled.
    protected $fillable = ['descripcion', 'icono', 'estado', 'id_ref', 'permiso'];

    /** @return BelongsTo<Menu, $this> */
    public function padre(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'id_ref', 'id_menu');
    }

    /** @return HasMany<Menu, $this> */
    public function hijos(): HasMany
    {
        return $this->hasMany(Menu::class, 'id_ref', 'id_menu');
    }

    /** @return BelongsToMany<Rol, $this> */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'accesoxrol', 'id_menu', 'id_rol');
    }

    /** @return BelongsToMany<Usuario, $this> */
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(Usuario::class, 'acceso', 'id_menu', 'id_usuario');
    }
}
