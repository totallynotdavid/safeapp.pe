<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\UpdateNotificationPreferencesRequest;
use App\Http\Requests\Account\UpdatePasswordRequest;
use App\Http\Requests\Account\UpdateProfileRequest;
use App\Mail\AccountEmail;
use App\Models\PreferenciaNotificacionUsuario;
use App\Models\Usuario;
use App\Services\AccountNotifications;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class AccountSettingsController extends Controller
{
    public function __construct(
        private readonly AccountNotifications $notifications,
    ) {}

    public function profile(#[CurrentUser] Usuario $usuario): View
    {
        return view('configuracion.cuenta.perfil', [
            'usuario' => $usuario->loadMissing('rol'),
        ]);
    }

    public function updateProfile(
        UpdateProfileRequest $request,
        #[CurrentUser]
        Usuario $usuario,
    ): RedirectResponse {
        $field = $request->string('campo')->toString();
        $emailChanged = false;

        switch ($field) {
            case 'personal':
                $usuario->personal = Str::upper($request->string('personal')->toString());
                break;

            case 'fecha_nacimiento':
                $usuario->fecha_nacimiento = $request->date('fecha_nacimiento');
                break;

            case 'telefono':
                $usuario->telefono = $request->filled('telefono') ? $request->string('telefono')->toString() : null;
                break;

            case 'correo':
                $email = $request->filled('correo') ? $request->string('correo')->toString() : null;
                $emailChanged = $email !== $usuario->correo;
                $usuario->correo = $email;

                if ($emailChanged) {
                    $usuario->correo_verificado_en = null;
                }

                break;
        }

        $usuario->save();

        $this->notifications->record(
            $usuario,
            'cuenta',
            'Información personal actualizada',
            'Actualizaste '.str_replace('_', ' ', $field).' en SAFEAPP.',
        );

        if ($emailChanged && $usuario->correo) {
            $this->sendVerification($usuario);
        }

        return back()->with(
            'status',
            $emailChanged
                ? 'Datos actualizados. Revisa tu correo para verificar la nueva dirección.'
                : 'Información personal actualizada.',
        );
    }

    public function security(#[CurrentUser] Usuario $usuario): View
    {
        return view('configuracion.cuenta.seguridad', [
            'usuario' => $usuario,
        ]);
    }

    public function updatePassword(
        UpdatePasswordRequest $request,
        #[CurrentUser]
        Usuario $usuario,
    ): RedirectResponse {
        if ($usuario->clave === null || ! Hash::check($request->string('clave_actual')->toString(), $usuario->clave)) {
            return back()->withErrors([
                'clave_actual' => 'La contraseña actual no es correcta.',
            ]);
        }

        $usuario->clave = $request->string('clave')->toString();
        $usuario->save();

        $this->notifications->record(
            $usuario,
            'seguridad',
            'Contraseña actualizada',
            'La contraseña de tu cuenta SAFEAPP fue actualizada.',
        );

        return back()->with('status', 'Contraseña actualizada.');
    }

    public function notifications(#[CurrentUser] Usuario $usuario): View
    {
        $preferences = PreferenciaNotificacionUsuario::query()->firstOrCreate(
            ['id_usuario' => $usuario->id_usuario],
            [
                'correo_cuenta' => true,
                'correo_seguridad' => true,
            ],
        );

        return view('configuracion.cuenta.notificaciones', [
            'usuario' => $usuario,
            'preferences' => $preferences,
            'notifications' => $usuario
                ->notificaciones()
                ->latest()
                ->limit(12)
                ->get(),
        ]);
    }

    public function updateNotifications(
        UpdateNotificationPreferencesRequest $request,
        #[CurrentUser]
        Usuario $usuario,
    ): JsonResponse|RedirectResponse {
        $preferences = PreferenciaNotificacionUsuario::query()->updateOrCreate(
            ['id_usuario' => $usuario->id_usuario],
            [
                'correo_cuenta' => $request->boolean('correo_cuenta'),
                'correo_seguridad' => $request->boolean('correo_seguridad'),
            ],
        );

        if ($request->expectsJson()) {
            return response()->json([
                'preferences' => $preferences,
            ]);
        }

        return back()->with(
            'status',
            'Preferencias de notificación actualizadas.',
        );
    }

    public function verifyEmail(
        #[CurrentUser]
        Usuario $actual,
        Usuario $usuario,
        string $hash,
    ): RedirectResponse {
        abort_unless(
            (int) $actual->id_usuario === (int) $usuario->id_usuario,
            403,
        );

        abort_unless(
            hash_equals(sha1((string) $usuario->correo), $hash),
            403,
        );

        if ($usuario->correo_verificado_en === null) {
            $usuario->correo_verificado_en = Carbon::now();
        }

        $usuario->save();

        return redirect()
            ->route('configuracion.perfil')
            ->with(
                'status',
                'Correo verificado. Ya puedes recibir notificaciones por email.',
            );
    }

    public function resendVerification(#[CurrentUser] Usuario $usuario): RedirectResponse
    {
        if ($usuario->correo && ! $usuario->correo_verificado_en) {
            $this->sendVerification($usuario);
        }

        return back()->with(
            'status',
            'Si tu correo está pendiente, enviamos un nuevo enlace de verificación.',
        );
    }

    private function sendVerification(Usuario $usuario): void
    {
        $url = URL::temporarySignedRoute(
            'configuracion.correo.verificar',
            now()->addMinutes(60),
            [
                'usuario' => $usuario,
                'hash' => sha1((string) $usuario->correo),
            ],
        );

        try {
            Mail::to($usuario->correo)->queue(
                new AccountEmail(
                    'Verifica tu correo de SAFEAPP',
                    'Verifica tu correo',
                    'Confirma que controlas esta dirección para recibir avisos de cuenta y seguridad.',
                    $url,
                    'Verificar correo',
                ),
            );
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
