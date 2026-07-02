<?php

class crudtelefono {

 
    public function agregarTelefono($datos) {
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sql = "SELECT count(*) from telefono as t where  t.telefono='$datos[1]' ";         

        $result = mysqli_query($conexion, $sql);
        $ver = mysqli_fetch_array($result);

      
          if($ver[0]<1){
            $sqlagregar = "INSERT INTO telefono (ruc,telefono,origen,comentario,estado)
             values('$datos[0]','$datos[1]','LLAMADA','AGREGADO MANUALMENTE',0)";   

             $resultagregar = mysqli_query($conexion, $sqlagregar);

          }  
        
       echo'ok';
    }


      public function CambiaEstadoTel($datos){
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sql = "SELECT t.id_telefono
                    from telefono as t 
                    where  t.telefono='$datos[1]'";         

        $result = sqlsrv_query($conexion, $sql);
        $ver = sqlsrv_fetch_array($result, SQLSRV_FETCH_NUMERIC);

      
          
            $sqlagregar = "UPDATE telefono set estado='1'where id_telefono='$ver[0]'";   

             $resultagregar = sqlsrv_query($conexion, $sqlagregar);

           
        
       echo'ok';


      }

      public function VerificarTel($tel){

        $obj = new conectar();
        $conexion = $obj->conexion();

        $sql = "SELECT t.estado
                    from telefono as t 
                    where  t.telefono='$tel' ";

        $result = sqlsrv_query($conexion, $sql);
        $ver = sqlsrv_fetch_array($result, SQLSRV_FETCH_NUMERIC);

        $datos = array(
            'estado' => $ver[0]
            
           
        );
        return $datos;
      }
            

   

}

?>