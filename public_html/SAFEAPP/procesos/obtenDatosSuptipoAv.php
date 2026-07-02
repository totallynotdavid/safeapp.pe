<?php 
	
	require_once "../capa_conexion/conexion.php";
	require_once "../capa_logica/crudProducto.php";
	$obj= new crudproducto();

	echo json_encode($obj->obtenDatosSuptipoAv());

 ?>