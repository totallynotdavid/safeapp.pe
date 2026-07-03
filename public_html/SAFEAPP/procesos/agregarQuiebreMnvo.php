<?php

require_once '../capa_conexion/conexion.php';
require_once '../capa_logica/crudQuiebre.php';
$obj = new crudQuiebre();

try {
    $datosquiebre = array(
        $_POST['idusu'],
        $_POST['idsupervisor'],
        $_POST['estado'],
        $_POST['fechaInicio'],
        $_POST['ncfruc'],
        $_POST['ncfrazonsocial'],
        $_POST['quiebre_servicio'],
        $_POST['quiebre_numero_problema'],
        $_POST['fechaActivacion'],
        $_POST['quiebre_tipo_averia'],
        $_POST['fechaInicio'],
        $_POST['quiebre_problemas'],
        $_POST['quiebre_detalle'],
        $_POST['quiebre_contacto1'],
        $_POST['quiebre_celular1'],
        $_POST['quiebre_correo1'],
        $_POST['quiebre_contacto2'],
        $_POST['quiebre_celular2'],
        $_POST['quiebre_correo2'],
        $_POST['quiebre_ticket'],
        $_POST['quiebre_numero_ticket'],
        $_POST['fechaTicket'],
        $_POST['ncregion'],
        $_POST['quiebre_observaciones'],
        $_POST['quiebre_back'],
    );
} catch (Exception $exce) {
    header('HTTP/1.1 500');
    echo $exc->getMessage();
}

try {
    $resultado = $obj->agregarQuiebremovil($datosquiebre);
    echo json_encode($resultado);
    print_r($resultado);
    echo $resultado;
} catch (Exception $exc) {
    header('HTTP/1.1 500');
    echo $exc->getMessage();
}
