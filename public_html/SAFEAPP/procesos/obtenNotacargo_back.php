<?php 
	
	require_once "../capa_conexion/conexion.php";
	require_once "../capa_logica/crudNotadecargo.php";
	$obj= new crudNotadecargo();

	echo json_encode($obj->obtenCargoFijaBack($_POST['idcargo']));

 ?>