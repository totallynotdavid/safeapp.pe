<?php

class crudrespuesta {

    public function obtenDatos($gestion) {
        $datos=array();
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sql = "SELECT id_respuesta , descripcion from respuesta where tipo='$gestion'";
       
        $result = mysqli_query($conexion, $sql);

        while ($row = mysqli_fetch_array($result)) {

            $datos[] = $row;
        }

       
        return $datos;


    }

}

?>