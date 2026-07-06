<?php

namespace App\Models;

use Database\Factories\UsuarioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    /** @use HasFactory<UsuarioFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'usuario';

    protected $primaryKey = 'id_usuario';

    protected $authPasswordName = 'clave';

    // No remember_token column on the legacy table; disable "remember me"
    // rather than adding a column nothing else needs yet.
    protected $rememberTokenName = '';

    protected $fillable = [
        'usuario', 'clave', 'id_rol', 'personal', 'sexo', 'id_supervisor',
        'fecha_nacimiento', 'telefono', 'correo',
    ];

    protected $hidden = ['clave'];

    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
            'fecha_nacimiento' => 'date',
        ];
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'acceso', 'id_usuario', 'id_menu');
    }

    public function hasPermiso(string $permiso): bool
    {
        // Strict compare: Collection's two-arg contains() uses loose ==, and
        // PHP's null == '' is true, so a null-permiso header would match ''.
        return $this->menus->contains(fn (Menu $menu): bool => $menu->permiso === $permiso);
    }
}
