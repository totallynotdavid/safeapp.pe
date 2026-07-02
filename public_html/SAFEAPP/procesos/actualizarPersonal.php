<?php
require_once "../capa_conexion/conexion.php";
require_once "../capa_logica/crudUsuario.php";
$obj = new crudusuario();

$datos = array(
    $_POST['id_usu'],
    $_POST['personalA'],
    $_POST['fecha_nacimientoA'],
    $_POST['telefonoA'],
    $_POST['correoA']
);

try {
    $resultado = $obj->actualizarPersonal($datos);
    echo json_encode($resultado);
    print_r($resultado);
} catch (Exception $exc) {
    header("HTTP/1.1 500");
    echo $exc->getMessage();
}
