<?php

require_once '../capa_logica/sesion.php';
require_once '../capa_conexion/conexion.php';
require_once '../capa_logica/crudDatos.php';
$obj = new crud();

$origen = $_POST['origen'];
$tipo_data = $_POST['tipo_data'];

try {
    $resultado = $obj->contarDisponibles($origen, $tipo_data);
    echo json_encode($resultado);
} catch (Exception $exc) {
    header('HTTP/1.1 500');
    echo $exc->getMessage();
}
