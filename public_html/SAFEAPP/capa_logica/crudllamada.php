<?php

class crudllamada
{
    public function agregar($datos)
    {
        date_default_timezone_set('America/Lima');
        $hoy = date('Y-m-d H:i:s');

        $obj = new conectar();
        $conexion = $obj->conexion();

        $datos9 = utf8_decode($datos[9]);

        $sqlLLAMADA = "INSERT into llamada (  ruc, 
                                                id_usuario,
                                                fecha, 
                                                telefono,
                                                id_respuesta,
                                                q_lineas, 
                                                nombre_contacto, 
                                                correo,comentario,
                                                razon_social,
                                                tipo_data,
                                                q_claro,
                                                q_entel,
                                                q_bitel,
                                                gestion
                                                )
                                    values ('$datos[2]',
                                            '$datos[0]',
                                            '$hoy',
                                            '$datos[1]',
                                            '$datos[4]',
                                            '$datos[5]',
                                            '$datos[6]',
                                            '$datos[7]',
                                            '$datos[8]',
                                            '$datos9',
                                            'MOVIL',
                                            '$datos[10]',
                                            '$datos[11]',
                                            '$datos[12]',
                                             '$datos[13]'
                                            )";

        $rpta = mysqli_query($conexion, $sqlLLAMADA);

        $sqlDATOS = "UPDATE datos set estado='1', q_claro='$datos[10]', q_entel='$datos[11]', q_bitel='$datos[12]' where ruc= '$datos[2]'";

        mysqli_query($conexion, $sqlDATOS);

        return $rpta;
    }

    public function agregarFija($datos)
    {
        date_default_timezone_set('America/Lima');
        $hoy = date('Y-m-d H:i:s');

        $obj = new conectar();
        $conexion = $obj->conexion();

        $datos9 = utf8_decode($datos[9]);

        $sqlLLAMADA = "INSERT into llamada (ruc, id_usuario,fecha, telefono,id_respuesta,q_lineas, nombre_contacto, correo,comentario,razon_social,tipo_data,gestion)
                                    values ('$datos[2]',
                                            '$datos[0]',
                                            '$hoy',
                                            '$datos[1]',
                                            '$datos[4]',
                                            '$datos[5]',
                                            '$datos[6]',
                                            '$datos[7]',
                                            '$datos[8]',
                                            '$datos9',
                                            'FIJA',
                                            '$datos[10]'
                                            )";

        $rpta = mysqli_query($conexion, $sqlLLAMADA);

        $sqlDATOS = "UPDATE datos set estado='1' where ruc= '$datos[2]'";

        mysqli_query($conexion, $sqlDATOS);

        return $rpta;
    }

    public function obtenDatos($idllamada)
    {
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sql = "SELECT ll.ruc,ll.razon_social,ll.telefono,ll.q_lineas,ll.nombre_contacto,ll.correo
        ,ll.comentario,ll.tipo_data,ll.q_claro,ll.q_entel,ll.q_bitel
                    from llamada as ll
                    where ll.id_llamada ='$idllamada' ";

        $result = mysqli_query($conexion, $sql);
        $ver = mysqli_fetch_array($result);

        $datos = array(
            'ruc' => $ver[0],
            'razon_social' => $ver[1],
            'telefono' => $ver[2],
            'q_lineas' => $ver[3],
            'nombre_contacto' => $ver[4],
            'correo' => $ver[5],
            'comentario' => $ver[6],
            'tipo_data' => $ver[7],
            'q_claro' => $ver[8],
            'q_entel' => $ver[9],
            'q_bitel' => $ver[10],
        );
        return $datos;
        print_r($datos);
    }

    public function agregarH($datos)
    {
        date_default_timezone_set('America/Lima');
        $hoy = date('Y-m-d H:i:s');

        $obj = new conectar();
        $conexion = $obj->conexion();

        $datos9 = utf8_decode($datos[9]);

        $sqlLLAMADA = "INSERT into llamada (ruc, id_usuario,fecha, telefono,id_respuesta,q_lineas, nombre_contacto, correo,comentario,razon_social,tipo_data,q_claro,q_entel,q_bitel,gestion)
                                    values ('$datos[2]',
                                            '$datos[0]',
                                            '$hoy',
                                            '$datos[1]',
                                            '$datos[4]',
                                            '$datos[5]',
                                            '$datos[6]',
                                            '$datos[7]',
                                            '$datos[8]',
                                            '$datos9',
                                            '$datos[10]',
                                            '$datos[11]',
                                            '$datos[12]',
                                            '$datos[13]',
                                            '$datos[14]'
                                            )";

        $rpta = mysqli_query($conexion, $sqlLLAMADA);

        return $rpta;
    }

    public function agregarNvocliente($datos)
    {
        date_default_timezone_set('America/Lima');
        $hoy = date('Y-m-d H:i:s');

        $obj = new conectar();
        $conexion = $obj->conexion();

        $ruc = $datos[2];

        $sqlruc = "SELECT  count(*) from datos where ruc='$ruc' ";

        $resultruc = mysqli_query($conexion, $sqlruc);
        $verruc = mysqli_fetch_array($resultruc);

        if ($verruc[0] === '0') {
            $sqlDATOS = "INSERT INTO datos (zonal,id_tienda, id_usuario,estado,ruc,razon_social,
                   cliente_movistar,q_movistar,q_entel,q_claro,q_bitel,origen,fecha_asignacion
                   ,tipo_data,nodo,q_competencia)
                                            values ('$datos[4]',
                                                    '$datos[10]',
                                                    '$datos[0]',
                                                    '1',
                                                    '$ruc',
                                                    '$datos[3]',
                                                    '',
                                                    '',
                                                    '',
                                                    '',
                                                    '',
                                                    'INHOUSE',
                                                    '$hoy',
                                                    'CONVERGENTE',
                                                    '',
                                                    ''
                                                    )";
        } else {
            $sqlDATOS = "UPDATE datos set estado='1',id_tienda='$datos[10]',id_usuario='$datos[0]',
                    fecha_asignacion='$hoy' where ruc= '$ruc'";
        }

        $sqlLLAMADA = "INSERT INTO llamada (ruc,razon_social,id_usuario,fecha, telefono,id_respuesta,q_lineas, nombre_contacto, correo,comentario,tipo_data,gestion,q_claro,q_bitel,q_entel)
                                            values ('$ruc',
                                                    '$datos[3]',
                                                    '$datos[0]',
                                                    '$hoy',
                                                    '$datos[1]',
                                                    '$datos[5]',
                                                    '$datos[6]',
                                                    '$datos[7]',
                                                    '$datos[8]',
                                                    '$datos[9]',
                                                    'INHOUSE',
                                                    '$datos[11]',
                                                    0,0,0
                                                    )";
        $result = mysqli_query($conexion, $sqlLLAMADA);
        mysqli_query($conexion, $sqlDATOS);

        return $result;
    }
}
