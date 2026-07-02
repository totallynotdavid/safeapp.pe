<?php

class crud
{

    public function obtenDatos($ruc)
    {
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sql = "SELECT d.razon_social
					from datos as d 
					where ruc ='$ruc' ";

        $result = mysqli_query($conexion, $sql);
        $ver = mysqli_fetch_array($result);

        $datos = array(
            'razon_social' => $ver[0]
        );
        return $datos;
    }


    public function obtenRuc($nvoruc)
    {
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sql = "SELECT d.estado,d.razon_social,d.ruc
                    from datos as d 
                    where ruc ='$nvoruc' ";

        $result = mysqli_query($conexion, $sql);
        $ver = mysqli_fetch_array($result);

        $datos = array(
            'estado' => $ver[0],
            'razon_social' => $ver[1],
            'ruc' => $ver[2]



        );
        return $datos;
    }





    public function autoAsignarData($id_usuario, $origen, $tipo_data)
    {
        $obj = new conectar();
        $conexion = $obj->conexion();

        $id_usuario = mysqli_real_escape_string($conexion, $id_usuario);
        $origen = mysqli_real_escape_string($conexion, $origen);
        $tipo_data = mysqli_real_escape_string($conexion, $tipo_data);

        // Get user's id_tienda from asigna
        $sqlTienda = "SELECT id_tienda FROM asigna WHERE id_usuario='$id_usuario' AND estado='0' LIMIT 1";
        $resTienda = mysqli_query($conexion, $sqlTienda);
        $id_tienda = 0;
        if ($resTienda && mysqli_num_rows($resTienda) > 0) {
            $rowTienda = mysqli_fetch_array($resTienda);
            $id_tienda = $rowTienda[0];
        }

        // Get 30 random IDs that match the criteria (exact origin match)
        $sqlSelect = "SELECT id_cliente FROM datos 
                      WHERE id_usuario = 0 
                      AND estado = 0 
                      AND origen = '$origen' 
                      AND tipo_data = '$tipo_data' 
                      ORDER BY RAND() 
                      LIMIT 30";

        $resultSelect = mysqli_query($conexion, $sqlSelect);

        if (!$resultSelect || mysqli_num_rows($resultSelect) == 0) {
            return array('status' => 'error', 'message' => 'No hay datos disponibles para asignar con ese origen.', 'asignados' => 0);
        }

        $ids = array();
        while ($row = mysqli_fetch_array($resultSelect)) {
            $ids[] = $row[0];
        }

        $idsString = implode(',', $ids);

        // Update those specific rows: assign user AND tienda so records appear in user's table
        $sqlUpdate = "UPDATE datos SET id_usuario = '$id_usuario', id_tienda = '$id_tienda' WHERE id_cliente IN ($idsString)";
        $resultUpdate = mysqli_query($conexion, $sqlUpdate);

        if ($resultUpdate) {
            $affected = mysqli_affected_rows($conexion);
            return array('status' => 'ok', 'message' => 'Se asignaron ' . $affected . ' registros correctamente.', 'asignados' => $affected);
        } else {
            return array('status' => 'error', 'message' => 'Error al asignar datos: ' . mysqli_error($conexion), 'asignados' => 0);
        }
    }


    public function contarDisponibles($origen, $tipo_data)
    {
        $obj = new conectar();
        $conexion = $obj->conexion();

        $origen = mysqli_real_escape_string($conexion, $origen);
        $tipo_data = mysqli_real_escape_string($conexion, $tipo_data);

        $sql = "SELECT COUNT(*) FROM datos 
                WHERE id_usuario = 0 
                AND estado = 0 
                AND origen = '$origen' 
                AND tipo_data = '$tipo_data'";

        $result = mysqli_query($conexion, $sql);
        $ver = mysqli_fetch_array($result);

        return array('disponibles' => $ver[0]);
    }


    public function eliminarDataAsignada($id_usuario, $tipo_data)
    {
        $obj = new conectar();
        $conexion = $obj->conexion();

        $id_usuario = mysqli_real_escape_string($conexion, $id_usuario);
        $tipo_data = mysqli_real_escape_string($conexion, $tipo_data);

        // Only unassign records that have estado=0 (not yet managed). Leave id_tienda as is.
        $sql = "UPDATE datos SET id_usuario = 0 
                WHERE id_usuario = '$id_usuario' 
                AND estado = 0 
                AND tipo_data = '$tipo_data'";

        $result = mysqli_query($conexion, $sql);

        if ($result) {
            $affected = mysqli_affected_rows($conexion);
            return array('status' => 'ok', 'message' => 'Se eliminaron ' . $affected . ' registros asignados.', 'eliminados' => $affected);
        } else {
            return array('status' => 'error', 'message' => 'Error al eliminar datos: ' . mysqli_error($conexion), 'eliminados' => 0);
        }
    }


    public function SubirDatos()
    {
        $obj = new conectar();
        $conexion = $obj->conexion();


        $sql = "SELECT zonal, id_tienda, id_usuario, estado, ruc, razon_social, cliente_movistar, q_movistar, q_entel, q_claro, q_bitel, origen, fecha_asignacion, tipo_data, nodo
                    from datos_temporal  ";

        $result = mysqli_query($conexion, $sql);

        while ($mostrar = mysqli_fetch_array($result)) {

            $sqlid = "SELECT id_cliente
                        from datos where ruc='$mostrar[4]'";

            $resultid = mysqli_query($conexion, $sqlid);

            $total = mysqli_num_rows($resultid);

            if ($total === 0) {

                $transac = "INSERT INTO datos ( zonal, id_tienda, id_usuario, estado, ruc, razon_social, cliente_movistar, q_movistar, q_entel, q_claro, q_bitel, origen, fecha_asignacion, tipo_data, nodo) VALUES ('$mostrar[0]','$mostrar[1]','$mostrar[2]','$mostrar[3]','$mostrar[4]','$mostrar[5]','$mostrar[6]','$mostrar[7]','$mostrar[8]','$mostrar[9]','$mostrar[10]','$mostrar[11]','$mostrar[12]','$mostrar[13]','$mostrar[14]')";
            } else {

                $verid = mysqli_fetch_array($resultid);

                $transac = "UPDATE datos SET zonal='$mostrar[0]',id_tienda='$mostrar[1]',id_usuario='$mostrar[2]',estado='$mostrar[3]',ruc='$mostrar[4]',razon_social='$mostrar[5]',cliente_movistar='$mostrar[6]',q_movistar='$mostrar[7]',q_entel='$mostrar[8]',q_claro='$mostrar[9]',q_bitel='$mostrar[10]',origen='$mostrar[11]',fecha_asignacion='$mostrar[12]',tipo_data='$mostrar[13]',nodo='$mostrar[14]' WHERE id_cliente='$verid[0]'";
            }

            $resultransac = mysqli_query($conexion, $transac);
        }



        return $resultransac;
    }
}