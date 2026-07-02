<?php 
	
	require_once "../capa_conexion/conexion.php";
	require_once "../capa_logica/crudDatos.php";
	$obj= new crud();

	echo json_encode($obj->obtenRuc($_POST['nvoruc']));

 ?>