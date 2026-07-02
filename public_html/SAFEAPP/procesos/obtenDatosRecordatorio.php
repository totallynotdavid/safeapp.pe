<?php 
	
	require_once "../capa_conexion/conexion.php";
	require_once "../capa_logica/crudRecordatorio.php";
	$obj= new crudRecordatorio();

	echo json_encode($obj->obtenDatos($_POST['idrecordatorio']));

 ?>