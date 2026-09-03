<?php

namespace App\Services;

use App\Mail\AccountEmail;
use App\Models\NotificacionUsuario;
use App\Models\Usuario;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AccountNotifications
{
    public function record(Usuario $usuario, string $category, string $title, string $description): NotificacionUsuario
    {
        $notification = $usuario
            ->notificaciones()
            ->create([
                'categoria' => $category,
                'titulo' => $title,
                'descripcion' => $description,
            ]);
        $preference = $usuario->preferenciasNotificacion;
        $enabled = $category === 'seguridad'
            ? $preference->correo_seguridad ?? true
            : $preference->correo_cuenta ?? true;

        if ($enabled && $usuario->correo && $usuario->correo_verificado_en) {
            try {
                Mail::to($usuario->correo)->queue(new AccountEmail($title, $title, $description));
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return $notification;
    }
}
