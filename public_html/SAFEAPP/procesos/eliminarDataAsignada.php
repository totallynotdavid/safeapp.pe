<?php

session_start();

if (!isset($_SESSION['idusu'])) {
    header('HTTP/1.1 403');
    echo json_encode(array('status' => 'error', 'message' => 'No autorizado'));
    exit();
}

require_once '../capa_conexion/conexion.php';
require_once '../capa_logica/crudDatos.php';
$obj = new crud();

$id_usuario = $_SESSION['idusu'];
$tipo_data = $_POST['tipo_data'];

// Validate tipo_data is exclusively FIJA or MOVIL
if ($tipo_data !== 'FIJA' && $tipo_data !== 'MOVIL') {
    echo
        json_encode(array(
            'status' => 'error',
            'message' => 'Tipo de data inválido. Solo se permite FIJA o MOVIL.',
            'eliminados' => 0,
        ))
    ;
    exit();
}

try {
    $resultado = $obj->eliminarDataAsignada($id_usuario, $tipo_data);
    echo json_encode($resultado);
} catch (Exception $exc) {
    header('HTTP/1.1 500');
    echo json_encode(array('status' => 'error', 'message' => $exc->getMessage(), 'eliminados' => 0));
}
