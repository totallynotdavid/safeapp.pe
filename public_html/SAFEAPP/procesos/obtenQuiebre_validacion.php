<?php

require_once "../capa_conexion/conexion.php";
require_once "../capa_logica/crudQuiebre.php";
$obj = new crudQuiebre();

echo json_encode($obj->obtenQuiebreValid($_POST['idquiebre']));

