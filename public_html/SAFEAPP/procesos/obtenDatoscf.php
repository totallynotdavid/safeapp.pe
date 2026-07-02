<?php 

require_once "../capa_conexion/conexion.php";
require_once "../capa_logica/crudProducto.php";
$obj= new crudproducto();


	$datos=array( 

		$_POST['suptipocom'],
		$_POST['descripcioncom']


		);


try {
    $resultado = $obj->obtenDatoscf($datos);
    echo json_encode($resultado);
    //print_r($resultado);
	} catch (Exception $exc) {
	    header("HTTP/1.1 500");
	    echo $exc->getMessage();
	}



 ?>
 