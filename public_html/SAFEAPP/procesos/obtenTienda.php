<?php 
	
	require_once "../capa_conexion/conexion.php";
	require_once "../capa_logica/crudTienda.php";
	$obj= new crudTienda();

$datos=array(


		$_POST['idusu'],
		$_POST['idtiendaact']
		
		);
	

	echo json_encode($obj->obtenTienda($datos));

 ?>