<?php 
	
	require_once "../capa_conexion/conexion.php";
	require_once "../capa_logica/crudTelefono.php";
	$obj= new crudtelefono();

	echo json_encode($obj->VerificarTel($_POST['tel']));

 ?>