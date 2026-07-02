<?php 
session_start();
	require_once "../capa_conexion/conexion.php";
	require_once "../capa_logica/crudTelefono.php";
	$obj= new crudtelefono();
	
	$datos=array(

		$_POST['dni'],
		$_POST['telefono']
		);
	

try {
    $resultado = $obj->CambiaEstadoTel($datos);
    echo json_encode($resultado);
    //print_r($resultado);
	} catch (Exception $exc) {
	    header("HTTP/1.1 500");
	    echo $exc->getMessage();
	}



 ?>