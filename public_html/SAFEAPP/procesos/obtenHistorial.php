<?php 
	
	require_once "../capa_conexion/conexion.php";
	require_once "../capa_logica/crudHistorial.php";
	$obj= new crudhistorial();

	echo json_encode($obj->obtenDatos($_POST['dni']));

 ?>