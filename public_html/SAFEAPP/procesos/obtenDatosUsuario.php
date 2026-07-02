<?php 
	
	require_once "../capa_conexion/conexion.php";
	require_once "../capa_logica/crudUsuario.php";
	$obj= new crudusuario();

	$hola = $obj->obtenDatos($_POST['id_usuario']);
	
	echo json_encode($hola);

 ?>