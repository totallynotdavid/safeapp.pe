<?php

require_once '../capa_conexion/conexion.php';
require_once '../capa_logica/crudNotadecargo.php';
$obj = new crudNotadecargo();

$datos = array(
    $_POST['idusu'], //0
    $_POST['idcargo'], //1
    $_POST['ncestado'], //2

    $_POST['ncejecutivotdp'], //3
    $_POST['nczonaltdp'], //4
    $_POST['nccomentarioback'], //5
    $_POST['fechaact'], //6
    $_POST['ncoportunidad'], //7
    $_POST['ncnodo'], //8
    $_POST['ncfwa'], //9
    $_POST['ncetapab'], //10
    $_POST['ncciclo'], //11
    $_POST['nccuentaf'], //12
);

try {
    $resultado = $obj->agregarncmBack($datos);
    echo json_encode($resultado);
    print_r($datos);
} catch (Exception $exc) {
    header('HTTP/1.1 500');
    echo $exc->getMessage();
}
