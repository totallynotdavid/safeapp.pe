<?php 

require_once "../capa_conexion/conexion.php";
require_once "../capa_logica/crudllamada.php";
$obj= new crudllamada();


	$datos=array( 


		$_POST['idusu'], //0
		$_POST['telefono'], // 1
		$_POST['nvorucl'], //2
		$_POST['nvorazonsocial'], //3
		$_POST['nvozonal'], //4
		$_POST['respuesta'], //5
		$_POST['qlineas'], //6
		$_POST['nomcontacto'], //7
		$_POST['correorl'],//8
		$_POST['comentario'], //9
		$_POST['tienda'],//10
		$_POST['gestion'] //11
				

		);


try {
    $resultado = $obj->agregarNvocliente($datos);
    echo json_encode($resultado);
    //print_r($resultado);
	} catch (Exception $exc) {
	    header("HTTP/1.1 500");
	    echo $exc->getMessage();
	}



 ?>
 