<?php 
	
	require_once "../capa_conexion/conexion.php";
	require_once "../capa_logica/crudllamada.php";
	$obj= new crudllamada();

	echo json_encode($obj->obtenDatos($_POST['idllamada']));

 ?>