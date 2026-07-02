<?php 

require_once "../capa_conexion/conexion.php";
require_once "../capa_logica/crudllamada.php";
$obj= new crudllamada();


	$datos=array( 

		$_POST['idusu'],
		$_POST['telefono'],
		$_POST['ruc'],
		$_POST['razon_social'],
		$_POST['respuesta'],
		$_POST['qlineas'],
		$_POST['nomcontacto'],
		$_POST['correorl'],
		$_POST['comentario'],
		$_POST['razon_social'],
		$_POST['tipo_data'],
		$_POST['qclaro'],
		$_POST['qentel'],
		$_POST['qbitel'],
		$_POST['gestion']
	
	

		);


try {
    $resultado = $obj->agregarH($datos);
    echo json_encode($resultado);
    //print_r($resultado);
	} catch (Exception $exc) {
	    header("HTTP/1.1 500");
	    echo $exc->getMessage();
	}



 ?>
 