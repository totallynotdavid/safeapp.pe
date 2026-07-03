<?php

session_start();
require_once '../capa_conexion/conexion.php';
require_once 'clave.php';

$obj = new conectar();
$conexion = $obj->conexion();

if (isset($_POST['user']) && isset($_POST['pass'])) {
    $user = $_POST['user'];
    $pass = $_POST['pass'];

    $sql = "SELECT id_usuario, personal, id_rol, clave FROM usuario WHERE usuario = ? AND estado = '1'";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, 's', $user);
    mysqli_stmt_execute($stmt);
    $data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($data && verificarClave($conexion, (int) $data['id_usuario'], $pass, $data['clave'])) {
        $_SESSION['user'] = $data['personal'];
        $_SESSION['idusu'] = $data['id_usuario'];
        $_SESSION['rol'] = $data['id_rol'];

        echo '1';
    } else {
        echo 'No tiene filas';
    }
} else {
    echo 'No se capturo admin y clave';
}
