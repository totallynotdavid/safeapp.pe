<?php

class crudQuiebre
{
    public function agregarQuiebremovil($datosquiebre)
    {
        date_default_timezone_set('America/Lima');
        $hoy = date('Y-m-d');
        $obj = new conectar();
        $conexion = $obj->conexion();
        $sqlQuiebre = "INSERT INTO quiebre (
                                            id_usuario,
                                            id_supervisor,
                                            estado,
                                            fecha_inicio_averia,
                                            ruc,
                                            razon_social,
                                            servicio,
                                            numero_problema,
                                            fecha_activacion,
                                            tipo_averia,
                                            fecha_inicio,
                                            problema_equipo,
                                            detalle_equipo,
                                            contacto1,
                                            celular1,
                                            correo1,
                                            contacto2,
                                            celular2,
                                            correo2,
                                            ticket_atencion,
                                            numero_ticket,
                                            fecha_ticket_atencion,
                                            zonal_telefonica,
                                            comentario_ejecutivo,
                                            fecha_registro,
                                            back
                                            )
                                    values (
                                            '$datosquiebre[0]',
                                            '$datosquiebre[1]',
                                            'PENDIENTE',
                                            '$datosquiebre[3]',
                                            '$datosquiebre[4]',
                                            '$datosquiebre[5]',
                                            '$datosquiebre[6]',
                                            '$datosquiebre[7]',
                                            '$datosquiebre[8]',
                                            '$datosquiebre[9]',
                                            '$datosquiebre[10]',
                                            '$datosquiebre[11]',
                                            '$datosquiebre[12]',
                                            '$datosquiebre[13]',
                                            '$datosquiebre[14]',
                                            '$datosquiebre[15]',
                                            '$datosquiebre[16]',
                                            '$datosquiebre[17]',
                                            '$datosquiebre[18]',
                                            '$datosquiebre[19]',
                                            '$datosquiebre[20]',
                                            '$datosquiebre[21]',
                                            '$datosquiebre[22]',
                                            '$datosquiebre[23]',
                                            '$hoy',
                                            '$datosquiebre[24]'
                                            )";
        $rpta = mysqli_query($conexion, $sqlQuiebre);
        return $rpta;
    }

    public function obtenQuiebreMovil($idquiebre)
    {
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sql = "SELECT  ncquiebre.estado,
                        ncquiebre.fecha_inicio_averia,
                        ncquiebre.ruc,
                        ncquiebre.razon_social,
                        ncquiebre.servicio,
                        ncquiebre.numero_problema,
                        ncquiebre.fecha_activacion,
                        ncquiebre.tipo_averia,
                        ncquiebre.fecha_inicio,
                        ncquiebre.problema_equipo,
                        ncquiebre.detalle_equipo,
                        ncquiebre.contacto1,
                        ncquiebre.celular1,
                        ncquiebre.correo1,
                        ncquiebre.contacto2,
                        ncquiebre.celular2, 
                        ncquiebre.correo2,
                        ncquiebre.ticket_atencion, 
                        ncquiebre.numero_ticket, 
                        ncquiebre.fecha_ticket_atencion, 
                        t.nombre, 
                        ncquiebre.comentario_ejecutivo, 
                        usu.personal, 
                        ncquiebre.validacion, 
                        ncquiebre.fecha_validacion, 
                        ncquiebre.casosf,
                        ncquiebre.comentario_validador,
                        ncquiebre.fecha_registro,
                        ncquiebre.back            
                        from quiebre as ncquiebre 
                        join usuario as usu on ncquiebre.id_usuario=usu.id_usuario
                        join tienda as t on t.id_tienda=ncquiebre.zonal_telefonica
                        where id_quiebre ='$idquiebre' ";

        $result = mysqli_query($conexion, $sql);
        $ver = mysqli_fetch_array($result);
        // $sqlsup = "SELECT sup.nombre
        //             from quiebre as ncquiebre inner join supervisor as sup on ncquiebre.id_supervisor=sup.id_supervisor
        //             where id_quiebre ='$idquiebre' ";
        // $resultsup = mysqli_query($conexion, $sqlsup);
        // $versup = mysqli_fetch_array($resultsup);
        $sqlval = "SELECT usu.personal
                    from quiebre as ncquiebre join usuario as usu on ncquiebre.id_validador=usu.id_usuario  
                    where id_quiebre ='$idquiebre' ";
        $resultval = mysqli_query($conexion, $sqlval);
        $verval = mysqli_fetch_array($resultval);

        $datos = array(
            'estado' => $ver[0],
            'fecha_inicio_averia' => $ver[1],
            'ruc' => $ver[2],
            'razon_social' => $ver[3],
            'servicio' => $ver[4],
            'numero_problema' => $ver[5],
            'fecha_activacion' => $ver[6],
            'tipo_averia' => $ver[7],
            'fecha_inicio' => $ver[8],
            'problema_equipo' => $ver[9],
            'detalle_equipo' => $ver[10],
            'contacto1' => $ver[11],
            'celular1' => $ver[12],
            'correo1' => $ver[13],
            'contacto2' => $ver[14],
            'celular2' => $ver[15],
            'correo2' => $ver[16],
            'ticket_atencion' => $ver[17],
            'numero_ticket' => $ver[18],
            'fecha_ticket_atencion' => $ver[19],
            'zonal_telefonica' => $ver[20],
            'comentario_ejecutivo' => $ver[21],
            'id_validador' => $verval[0],
            'validacion' => $ver[22],
            'fecha_validacion' => $ver[24],
            'casosf' => $ver[25],
            'comentario_validador' => $ver[26],
            'fecha_registro' => $ver[27],
            'back' => $ver[28],
        );
        return $datos;
    }

    public function agregarQuiebreComentario($datos)
    {
        $obj = new conectar();
        $conexion = $obj->conexion();
        $sqlquiebreMovil = "UPDATE quiebre SET 
                            contacto1='$datos[1]',
                            celular1='$datos[2]',
                            correo1='$datos[3]',
                            contacto2='$datos[4]',
                            celular2='$datos[5]',
                            correo2='$datos[6]',
                            numero_problema='$datos[7]',
                            comentario_ejecutivo='$datos[8]'
                            WHERE id_quiebre='$datos[0]' ";
        $rpta = mysqli_query($conexion, $sqlquiebreMovil);
        return $rpta;
    }

    public function obtenQuiebreValid($idquiebre)
    {
        $obj = new conectar();
        $conexion = $obj->conexion();
        $sql = "SELECT  ncquiebre.fecha_inicio,
                        ncquiebre.fecha_activacion,
                        ncquiebre.fecha_inicio_averia, 
                        ncquiebre.ruc,
                        ncquiebre.razon_social,
                        ncquiebre.servicio, 
                        ncquiebre.tipo_averia, 
                        ncquiebre.problema_equipo, 
                        ncquiebre.detalle_equipo,
                        ncquiebre.ticket_atencion, 
                        ncquiebre.fecha_ticket_atencion, 
                        ncquiebre.numero_ticket,
                        ncquiebre.contacto1,
                        ncquiebre.celular1, 
                        ncquiebre.correo1, 
                        ncquiebre.contacto2,
                        ncquiebre.celular2,
                        ncquiebre.correo2,
                        usu.personal,
                        ncquiebre.numero_problema,
                        t.nombre,
                        ncquiebre.comentario_ejecutivo,
                        usu.personal,
                        ncquiebre.casosf,
                        ncquiebre.comentario_validador,
                        ncquiebre.estado,
                        ncquiebre.fecha_registro,
                        ncquiebre.back
                        from quiebre as ncquiebre 
                        inner join usuario as usu on ncquiebre.id_usuario=usu.id_usuario
                        left join tienda as t on t.id_tienda=ncquiebre.zonal_telefonica
                        where id_quiebre ='$idquiebre' ";
        $result = mysqli_query($conexion, $sql);
        $ver = mysqli_fetch_array($result);
        $datos = array(
            'fecha_inicio' => $ver[0],
            'fecha_activacion' => $ver[1],
            'fecha_inicio_averia' => $ver[2],
            'ruc' => $ver[3],
            'razon_social' => $ver[4],
            'servicio' => $ver[5],
            'tipo_averia' => $ver[6],
            'problema_equipo' => $ver[7],
            'detalle_equipo' => $ver[8],
            'ticket_atencion' => $ver[9],
            'fecha_ticket_atencion' => $ver[10],
            'numero_ticket' => $ver[11],
            'contacto1' => $ver[12],
            'celular1' => $ver[13],
            'correo1' => $ver[14],
            'contacto2' => $ver[15],
            'celular2' => $ver[16],
            'correo2' => $ver[17],
            'id_usuario' => $ver[18],
            'numero_problema' => $ver[19],
            'zonal_telefonica' => $ver[20],
            'comentario_ejecutivo' => $ver[21],
            'id_validador' => $ver[22],
            'casosf' => $ver[23],
            'comentario_validador' => $ver[24],
            'estado' => $ver[25],
            'fecha_registro' => $ver[26],
            'back' => $ver[27],
        );
        return $datos;
    }

    public function agregarQuiebreValidacion($datos)
    {
        date_default_timezone_set('America/Lima');
        $hoy = date('Y-m-d');
        $obj = new conectar();
        $conexion = $obj->conexion();
        $sqlquiebreMovil = "UPDATE quiebre 
                            SET 
                                id_validador='$datos[1]',
                                estado='$datos[2]',
                                fecha_validacion='$hoy',
                                casosf='$datos[4]',
                                comentario_validador='$datos[3]'
                                WHERE id_quiebre='$datos[5]' ";
        $rpta = mysqli_query($conexion, $sqlquiebreMovil);
        return $rpta;
    }
}
