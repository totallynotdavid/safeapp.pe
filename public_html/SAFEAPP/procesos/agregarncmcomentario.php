<?php

require_once '../capa_conexion/conexion.php';
require_once '../capa_logica/crudNotadecargo.php';
$obj = new crudNotadecargo();

$datos = array(
    $_POST['idcargo'], //0
    $_POST['ncobservacionesejes'], //1
    $_POST['nctelefono1m'], //2
    $_POST['nctelefono2m'], //3
    $_POST['ncqlineass'], //4
    $_POST['nccargofijos'], //5
    $_POST['ncnegociacions'], //5
    $_POST['ncestimadas'], //6
    $_POST['ncoportunidade'], //7
);

try {
    $resultado = $obj->agregarNCmovilcomentario($datos);
    echo json_encode($resultado);

    //print_r($resultado);
} catch (Exception $exc) {
    header('HTTP/1.1 500');
    echo $exc->getMessage();
}
