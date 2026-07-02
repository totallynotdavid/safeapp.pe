<?php 

require_once "../capa_conexion/conexion.php";
require_once "../capa_logica/crudNotadecargo.php";
$obj= new crudNotadecargo();


	$datos=array( 

		
		$_POST['ncejecutivotdp']


		);


try {
    $resultado = $obj->obtenDatosCartera($datos);
    echo json_encode($resultado);
    //print_r($resultado);
	} catch (Exception $exc) {
	    header("HTTP/1.1 500");
	    echo $exc->getMessage();
	}



 ?>
 