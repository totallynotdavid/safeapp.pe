<?php 
	require_once "../capa_conexion/conexion.php";
	require_once "../capa_logica/crudUsuario.php";
	$obj= new crudusuario();

	try {
    $resultado = $obj->eliminar($_POST['id_usuario']);
    echo json_encode($resultado);
    //print_r($resultado);
	} catch (Exception $exc) {
	    header("HTTP/1.1 500");
	    echo $exc->getMessage();
	}

 ?>