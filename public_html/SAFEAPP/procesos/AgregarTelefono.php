<?php 
session_start();
	require_once "../capa_conexion/conexion.php";
	require_once "../capa_logica/crudTelefono.php";
	$obj= new crudtelefono();
	
	$datos=array(


		$_POST['ruc'],
		$_POST['telefono']
		
		);
	

try {
    $resultado = $obj->agregarTelefono($datos);
    echo json_encode($resultado);
    //print_r($resultado);
	} catch (Exception $exc) {
	    header("HTTP/1.1 500");
	    echo $exc->getMessage();
	}



 ?>