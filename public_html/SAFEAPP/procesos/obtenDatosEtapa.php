<?php

require_once '../capa_logica/sesion.php';
require_once '../capa_conexion/conexion.php';
require_once '../capa_logica/crudUsuario.php';
$obj = new crudUsuario();

echo json_encode($obj->obtenDatosEtapa());
