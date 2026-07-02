<?php 
	
	require_once "../capa_conexion/conexion.php";
	require_once "../capa_logica/crudUsuario.php";
	$obj= new crudusuario();

	echo json_encode($obj->obtenDatosRol());
	

 ?>