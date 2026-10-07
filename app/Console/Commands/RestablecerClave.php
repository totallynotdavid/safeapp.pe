<?php

namespace App\Console\Commands;

use App\Models\Usuario;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Sets a password for accounts that cannot use the Usuarios screen, including
 * the first administrator.
 */
class RestablecerClave extends Command
{
    protected $signature = 'usuario:restablecer-clave {usuario : The account name to sign in with}';

    protected $description = 'Set a new random password on an account and print it once';

    public function handle(): int
    {
        $nombre = $this->argument('usuario');
        $nombre = is_string($nombre) ? $nombre : '';
        $usuario = Usuario::query()->where('usuario', $nombre)->first();

        if ($usuario === null) {
            $this->components->error("No account named `{$nombre}`.");

            return self::FAILURE;
        }

        $clave = Str::password(16, symbols: false);
        $usuario->clave = $clave;
        $usuario->save();

        $this->components->info("Password for `{$usuario->usuario}`:");
        $this->line($clave);

        return self::SUCCESS;
    }
}
