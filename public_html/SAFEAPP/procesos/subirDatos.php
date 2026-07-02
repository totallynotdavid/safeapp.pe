<?php 
	
	require_once "../capa_conexion/conexion.php";
	require_once "../capa_logica/crudDatos.php";
	$obj= new crud();




try {
    $resultado = $obj->subirDatos();
    echo json_encode($resultado);
    //print_r($resultado);
	} catch (Exception $exc) {
	    header("HTTP/1.1 500");
	    echo $exc->getMessage();
	}


 ?>