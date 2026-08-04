<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Usuario;
use App\Services\Auth\LegacyPasswordHasher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(
        LoginRequest $request,
        LegacyPasswordHasher $hasher,
    ): RedirectResponse {
        $usuarioInput = $request->string('usuario');
        $clave = $request->string('clave');

        $usuario = Usuario::where('usuario', $usuarioInput)
            ->where('estado', true)
            ->first();

        if (! $usuario || ! $hasher->verifyAndUpgrade($usuario, $clave)) {
            throw ValidationException::withMessages([
                'usuario' => 'Credenciales inválidas.',
            ]);
        }

        Auth::guard('web')->login($usuario);
        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
