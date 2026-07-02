<?php

class crudhistorial {


    public function obtenDatos($dni) {
        $datos=array();
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sql = " SELECT  l.id_respuesta,l.fecha_llamada,l.telefono,l.id_usuario
            from llamada as l left join detalle_llamada as dl on dl.id_llamada=l.id_llamada 
             where l.dni_ruc='$dni'";
       
        $result = sqlsrv_query($conexion, $sql);

        while ($row = sqlsrv_fetch_array($result)) {

            $datos[] = $row;
        }

        return $datos;

    }

}

?>