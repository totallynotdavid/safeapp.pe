<?php 

require_once "../capa_conexion/conexion.php";
require_once "../capa_logica/crudNotadecargo.php";
$obj= new crudNotadecargo();


	$datos=array( 

		$_POST['idusu'], //0
		$_POST['idsupervisor'], //1
		$_POST['fechahoy'], //2
		$_POST['ncfqlineas'], //3
		$_POST['ncmodalidad'], //4
		$_POST['suptipocom'], //5
		$_POST['descripcioncom'], //6
		$_POST['nccargofijo'], //7
		$_POST['ncfruc'], //8
		$_POST['ncfrazonsocial'], //9
		$_POST['ncnomcontacto'], //10
		$_POST['nctelefono1'], //11
		$_POST['nctelefono2'], //12
		$_POST['ncdireccion'], //13
		$_POST['nccoordenadas'], //14
		$_POST['ncregion'], //15
		$_POST['ncobservaciones'], //16
		$_POST['nctecnlogia'] // 17
	

		);


try {
    $resultado = $obj->agregarNCfija($datos);
    echo json_encode($resultado);
    //print_r($resultado);
	} catch (Exception $exc) {
	    header("HTTP/1.1 500");
	    echo $exc->getMessage();
	}



 ?>
 