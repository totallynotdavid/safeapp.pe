<?php 
	
	require_once "../capa_conexion/conexion.php";
	require_once "../capa_logica/crudSupervisor.php";
	$obj= new crudSupervisor();

	echo json_encode($obj->obtenDatos());

 ?>