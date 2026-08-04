<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\UpdateNotificationPreferencesRequest;
use App\Http\Requests\Account\UpdatePasswordRequest;
use App\Http\Requests\Account\UpdateProfileRequest;
use App\Mail\AccountEmail;
use App\Models\PreferenciaNotificacionUsuario;
use App\Models\Usuario;
use App\Services\AccountNotifications;
use App\Services\Auth\LegacyPasswordHasher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AccountSettingsController extends Controller
{
    public function __construct(
        private readonly LegacyPasswordHasher $hasher,
        private readonly AccountNotifications $notifications,
    ) {}

    public function profile(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        return view('configuracion.cuenta.perfil', ['usuario' => $usuario->loadMissing('rol')]);
    }

    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $data = $request->validated();
        $field = $data['campo'];
        $emailChanged = false;

        if ($field === 'personal')
            $usuario->personal = Str::upper($data['personal']);
        if ($field === 'fecha_nacimiento')
            $usuario->fecha_nacimiento = $data['fecha_nacimiento'] ?? null;
        if ($field === 'telefono')
            $usuario->telefono = $data['telefono'] ?? null;
        if ($field === 'correo') {
            $email = $data['correo'] ?? null;
            $emailChanged = $email !== $usuario->correo;
            $usuario->correo = $email;
            if ($emailChanged)
                $usuario->correo_verificado_en = null;
        }
        $usuario->save();

        $this->notifications->record(
            $usuario,
            'cuenta',
            'Información personal actualizada',
            'Actualizaste '.str_replace('_', ' ', $field).' en SAFEAPP.',
        );
        if ($emailChanged && $usuario->correo)
            $this->sendVerification($usuario);

        return back()->with(
            'status',
            $emailChanged
                ? 'Datos actualizados. Revisa tu correo para verificar la nueva dirección.'
                : 'Información personal actualizada.',
        );
    }

    public function security(Request $request): View
    {
        return view('configuracion.cuenta.seguridad', ['usuario' => $request->user()]);
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        if (! $this->hasher->verifyAndUpgrade($usuario, $request->string('clave_actual'))) {
            return back()->withErrors(['clave_actual' => 'La contraseña actual no es correcta.']);
        }
        $usuario->clave = $this->hasher->hash($request->string('clave'));
        $usuario->save();
        $this->notifications->record(
            $usuario,
            'seguridad',
            'Contraseña actualizada',
            'La contraseña de tu cuenta SAFEAPP fue actualizada.',
        );

        return back()->with('status', 'Contraseña actualizada.');
    }

    public function notifications(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $preferences = PreferenciaNotificacionUsuario::firstOrCreate(
            ['id_usuario' => $usuario->id_usuario],
            ['correo_cuenta' => true, 'correo_seguridad' => true],
        );

        return view('configuracion.cuenta.notificaciones', [
            'usuario' => $usuario,
            'preferences' => $preferences,
            'notifications' => $usuario->notificaciones()->latest()->limit(12)->get(),
        ]);
    }

    public function updateNotifications(UpdateNotificationPreferencesRequest $request): JsonResponse|RedirectResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $preferences = PreferenciaNotificacionUsuario::updateOrCreate(
            ['id_usuario' => $usuario->id_usuario],
            [
                'correo_cuenta' => $request->boolean('correo_cuenta'),
                'correo_seguridad' => $request->boolean('correo_seguridad'),
            ],
        );
        if ($request->expectsJson())
            return response()->json(['preferences' => $preferences]);

        return back()->with('status', 'Preferencias de notificación actualizadas.');
    }

    public function verifyEmail(Request $request, Usuario $usuario, string $hash): RedirectResponse
    {
        abort_unless((int) $request->user()->id_usuario === (int) $usuario->id_usuario, 403);
        abort_unless(hash_equals(sha1((string) $usuario->correo), $hash), 403);
        $usuario->correo_verificado_en ??= now();
        $usuario->save();

        return redirect()
            ->route('configuracion.perfil')
            ->with('status', 'Correo verificado. Ya puedes recibir notificaciones por email.');
    }

    public function resendVerification(Request $request): RedirectResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        if ($usuario->correo && ! $usuario->correo_verificado_en)
            $this->sendVerification($usuario);

        return back()->with('status', 'Si tu correo está pendiente, enviamos un nuevo enlace de verificación.');
    }

    private function sendVerification(Usuario $usuario): void
    {
        $url = URL::temporarySignedRoute('configuracion.correo.verificar', now()->addMinutes(60), [
            'usuario' => $usuario,
            'hash' => sha1((string) $usuario->correo),
        ]);
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
