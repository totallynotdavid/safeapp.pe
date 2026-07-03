<?php

require_once '../capa_logica/sesion.php';
require_once '../capa_conexion/conexion.php';
require_once '../capa_logica/crudNotadecargo.php';
$obj = new crudNotadecargo();

$datos = array(
    $_POST['idcargos'], //0
    $_POST['nccomisions'], //1
    $_POST['ncobservacionl'], //2
    $_POST['ncdescuentos'], //3
    $_POST['fechavalidacionl'], //4
    $_POST['nccomentariol'], //5
);

try {
    $resultado = $obj->agregarliquidacion($datos);
    echo json_encode($resultado);
    print_r($datos);
} catch (Exception $exc) {
    header('HTTP/1.1 500');
    echo $exc->getMessage();
}
