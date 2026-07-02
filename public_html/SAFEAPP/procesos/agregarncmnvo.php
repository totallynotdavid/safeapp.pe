<?php

require_once "../capa_conexion/conexion.php";
require_once "../capa_logica/crudNotadecargo.php";
$obj = new crudNotadecargo();


$datos = array(

	$_POST['idusu'], //0
	$_POST['idsupervisor'], //1
	$_POST['ncqlineas'], //2
	$_POST['ncmodalidad'], //3
	$_POST['nccargofijo'], //4
	$_POST['ncfruc'], //5
	$_POST['ncfrazonsocial'], //6
	$_POST['ncnomcontacto'], //7
	$_POST['nctelefono1'], //8
	$_POST['nctelefono2'], //9
	$_POST['nczonaltdp'], //10
	$_POST['ncobservaciones'], //11
	$_POST['ncoportunidad'], //12
	$_POST['ncestimada'], //13
	$_POST['ncnegociacion'], //14
	$_POST['ncejecutivotdp'], //16
	$_POST['nctipoproducto'], //17
	$_POST['ncetapa'], //18
);

try {
	$resultado = $obj->agregarNCmovil($datos);
	echo json_encode($resultado);
	//print_r($resultado);
} catch (Exception $exc) {
	header("HTTP/1.1 500");
	echo $exc->getMessage();
}
