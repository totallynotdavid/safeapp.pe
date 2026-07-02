<?php 
	
	require_once "../capa_conexion/conexion.php";
	require_once "../capa_logica/crudDatos.php";
	$obj= new crud();

	echo json_encode($obj->obtenDatos($_POST['ruc']));

 ?>