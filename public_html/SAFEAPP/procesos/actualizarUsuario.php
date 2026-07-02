<?php 
	require_once "../capa_conexion/conexion.php";
	require_once "../capa_logica/crudUsuario.php";
	$obj= new crudusuario();

	$datos=array(

		$_POST['id_usu'],
		$_POST['personalA'],
		$_POST['claveA'], 
		$_POST['supcomboA'], 
		$_POST['estadoA']

		);

	try {
    $resultado = $obj->actualizarUsuario($datos);
    echo json_encode($resultado);
    //print_r($resultado);
	} catch (Exception $exc) {
	    header("HTTP/1.1 500");
	    echo $exc->getMessage();
	}
	
 ?>