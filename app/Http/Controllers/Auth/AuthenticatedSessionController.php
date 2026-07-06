<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Usuario;
use App\Services\Auth\LegacyPasswordHasher;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function store(LoginRequest $request, LegacyPasswordHasher $hasher): JsonResponse
    {
        $usuario = Usuario::where('usuario', $request->string('usuario'))
            ->where('estado', true)
            ->first();

        if (! $usuario || ! $hasher->verifyAndUpgrade($usuario, $request->string('clave'))) {
            throw ValidationException::withMessages([
                'usuario' => 'Credenciales inválidas.',
            ]);
        }

        Auth::login($usuario);
        $request->session()->regenerate();

        return response()->json(['usuario' => $usuario->usuario, 'rol' => $usuario->rol?->nombre_rol]);
    }

    public function destroy(): JsonResponse
    {
        Auth::guard('web')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return response()->json(['ok' => true]);
    }
}
