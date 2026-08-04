<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreferenciaNotificacionUsuario extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'id_usuario';
    protected $table = 'preferencia_notificacion_usuario';
    protected $fillable = ['id_usuario', 'correo_cuenta', 'correo_seguridad'];

    protected function casts(): array
    {
        return ['correo_cuenta' => 'boolean', 'correo_seguridad' => 'boolean'];
    }
}
