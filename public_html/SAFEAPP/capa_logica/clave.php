<?php

// Centralizes password hashing so every write path (new user, password
// change) upgrades to the same scheme, and every read path (login)
// verifies the same way. `usuario.clave` currently holds a mix of legacy
// unsalted MD5 (from the original schema) and password_hash() output.
// verificarClave() migrates an account off MD5 the moment it next logs
// in successfully, so no bulk migration of the `usuario` table is needed.

function hashearClave(string $clave): string
{
    return password_hash($clave, PASSWORD_DEFAULT);
}

function verificarClave(mysqli $conexion, int $idUsuario, string $claveIngresada, string $claveGuardada): bool
{
    if (password_verify($claveIngresada, $claveGuardada)) {
        return true;
    }

    $esMd5Legado = preg_match('/^[0-9a-f]{32}$/', $claveGuardada) === 1;
    if (!$esMd5Legado || md5($claveIngresada) !== $claveGuardada) {
        return false;
    }

    $claveNueva = hashearClave($claveIngresada);
    $stmt = mysqli_prepare($conexion, "UPDATE usuario SET clave = ? WHERE id_usuario = ?");
    mysqli_stmt_bind_param($stmt, "si", $claveNueva, $idUsuario);
    mysqli_stmt_execute($stmt);

    return true;
}
