<?php 
	require_once "../capa_conexion/conexion.php";
	require_once "../capa_logica/crudNotadecargo.php";
	$obj= new crudNotadecargo();

	$datos=array(

		$_POST['fechahoy'],//0
		$_POST['idusu'],//1
		$_POST['ncvalidacion'],//2
		$_POST['ncobservacionesval'],//3
		$_POST['idcargo'],//4
		$_POST['ncejecutivotdp'],//5
		$_POST['nccargofijo']//6
		
		
		);
	
	try {
    $resultado = $obj->agregarncfValidacionAv($datos);
    echo json_encode($resultado);
    //print_r($resultado);
	} catch (Exception $exc) {
	    header("HTTP/1.1 500");
	    echo $exc->getMessage();
	}
	
 ?>