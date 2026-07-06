<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
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
}
