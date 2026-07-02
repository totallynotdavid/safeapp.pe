<?php

session_start();
require_once "../capa_conexion/conexion.php";
$obj= new conectar();
$conexion=$obj->conexion();

if(isset($_POST["user"]) && isset($_POST["pass"])){
  $user =  $_POST['user'];
  $pass =  $_POST['pass'];
  $pass=md5($pass);

  $sql = "SELECT id_usuario,personal,id_rol FROM usuario 
WHERE usuario='$user' and '$pass'= clave  and estado='1' ";
 
  $result = mysqli_query($conexion, $sql);

  $num_row = mysqli_num_rows($result);
  
  if ($num_row > 0) {
    $data =mysqli_fetch_array($result);
    $_SESSION["user"] = $data["personal"];
    $_SESSION ["idusu"]=$data["id_usuario"];
    $_SESSION ["rol"]=$data["id_rol"];
    
    echo "1";
  } else {
    echo "No tiene filas" ;
    
  }
} else {
    echo "No se capturo admin y clave";
}

?>
