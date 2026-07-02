<?php 
	
	require_once "../capa_conexion/conexion.php";
	require_once "../capa_logica/crudrespuesta.php";
	$obj= new crudrespuesta();

	echo json_encode($obj->obtenDatos($_POST['gestion']));

 ?>