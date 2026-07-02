<?php 
	require_once "../capa_conexion/conexion.php";
	require_once "../capa_logica/crudUsuario.php";
	$obj= new crudusuario();

	$datos=array(
		$_POST['personal'],
		$_POST['nombreusu'],
		$_POST['clave'],
		$_POST['rolcombo'],
		$_POST['regcombo'],
		$_POST['supcombo'],
		$_POST['genero'],
		$_POST['estado']
		);
	
	try {
    $resultado = $obj->agregarRegion($datos);
    echo json_encode($resultado);
    //print_r($resultado);
	} catch (Exception $exc) {
	    header("HTTP/1.1 500");
	    echo $exc->getMessage();
	}
	
 ?>