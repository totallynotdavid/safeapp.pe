<?php

namespace App\Services\Auth;

use App\Models\Usuario;

class LegacyPasswordHasher
{
    // One owner for producing a stored clave, so new users and password changes
    // hash the same way verifyAndUpgrade() rehashes.
    public function hash(string $plain): string
    {
        return password_hash($plain, PASSWORD_DEFAULT);
    }

    // usuario.clave holds a mix of legacy unsalted MD5 and password_hash()
    // output. Verify against whichever it is, and upgrade to bcrypt in place on a
    // successful legacy match.
    //
    // Uses password_verify() directly rather than the Hash facade: Hash::check()
    // throws a RuntimeException on a non-bcrypt string instead of returning
    // false, which would break the fallthrough to the MD5 check below.
    public function verifyAndUpgrade(Usuario $usuario, string $plain): bool
    {
        if (password_verify($plain, $usuario->clave)) {
            return true;
        }

        $esMd5Legado = preg_match('/^[0-9a-f]{32}$/', $usuario->clave) === 1;
        if (! $esMd5Legado || md5($plain) !== $usuario->clave) {
            return false;
        }

        $usuario->forceFill(['clave' => password_hash($plain, PASSWORD_DEFAULT)])->save();

        return true;
    }
}
