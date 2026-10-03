<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_usuario
 * @property bool $correo_cuenta
 * @property bool $correo_seguridad
 */
class PreferenciaNotificacionUsuario extends Model
{
    protected $table = 'preferencia_notificacion_usuario';
    protected $primaryKey = 'id_usuario';

    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'correo_cuenta',
        'correo_seguridad',
    ];

    protected function casts(): array
    {
        return [
            'correo_cuenta' => 'boolean',
            'correo_seguridad' => 'boolean',
        ];
    }
}
