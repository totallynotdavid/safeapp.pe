<?php 
session_start();
	require_once "../capa_conexion/conexion.php";
	require_once "../capa_logica/crudTienda.php";
	$obj= new crudTienda();
	
	$datos=array(


		$_POST['idusu'],
		$_POST['idtiendaact'],
		$_POST['nvatienda']
		
		);
	

try {
    $resultado = $obj->CambiarTienda($datos);
    echo json_encode($resultado);
    //print_r($resultado);
	} catch (Exception $exc) {
	    header("HTTP/1.1 500");
	    echo $exc->getMessage();
	}



 ?>