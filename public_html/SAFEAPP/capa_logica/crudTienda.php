<?php

class crudTienda {

  

    public function obtenTienda($dat) {
        $datos=array();
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sql = "SELECT  t.id_tienda,t.nombre from asigna as a left join tienda as t on t.id_tienda=a.id_tienda
where a.id_usuario='$dat[0]' and a.id_tienda not in ('$dat[1]') ";
       
        $result = mysqli_query($conexion, $sql);

        while ($row = mysqli_fetch_array($result)) {

            $datos[] = $row;
        }

       
        return $datos;


    }



    public function CambiarTienda($dat) {
        $datos=array();
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sqlTELACT = " UPDATE asigna  set estado='1' where id_usuario='$dat[0]' and id_tienda='$dat[1]'";
       
        $resultTELACT = mysqli_query($conexion, $sqlTELACT);


        $sqlNVOTEL = " UPDATE asigna  set estado='0' where id_usuario='$dat[0]' and id_tienda='$dat[2]'";
       
        $resultNVOTEL = mysqli_query($conexion, $sqlNVOTEL);

        
       
            echo'ok';

    }


  
   



}


?>