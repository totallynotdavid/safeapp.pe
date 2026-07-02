<?php 

require_once "../capa_conexion/conexion.php";
require_once "../capa_logica/crudNotadecargo.php";
$obj= new crudNotadecargo();


	$datos=array( 

		$_POST['idcargom'], //0
		$_POST['ncqlineas'],//1
		$_POST['nccargofijo'] //2

		);


try {
    $resultado = $obj->agregarNCmovilcomentarioback($datos);
    echo json_encode($resultado);
    //print_r($resultado);
	} catch (Exception $exc) {
	    header("HTTP/1.1 500");
	    echo $exc->getMessage();
	}


