<?php

class crudNotadecargo
{

    public function agregarNCfija($datos)
    {

        date_default_timezone_set("America/Lima");
        $hoy = date("Y-m-d");

        $obj = new conectar();
        $conexion = $obj->conexion();


        $sqlLLAMADA = "INSERT into nc_fija (id_usuario, id_supervisor,fecha_ingreso, ruc,razon_social,tipo, subtipo, descripcion,q_lineas,cargo_fijo,modalidad,contacto,telefono1,telefono2,estado,validacion,direccion,coordenadas,zonal,comentario,tecnologia,nodo)
                                    values ('$datos[0]',
                                            '$datos[1]',
                                             '$hoy',
                                            '$datos[8]',
                                            '$datos[9]',
                                            'FIJA',
                                            '$datos[5]',
                                            '$datos[6]',
                                            '$datos[3]',
                                            '$datos[7]',
                                            '$datos[4]',
                                             '$datos[10]',
                                             '$datos[11]',
                                             '$datos[12]',
                                             'XINGRESAR',
                                             'PENDIENTE',
                                             '$datos[13]',
                                                '$datos[14]',
                                               '$datos[15]',
                                                '$datos[16]',
                                                '$datos[17]',
                                                'SINNODO'

                                            )";

        $rpta = mysqli_query($conexion, $sqlLLAMADA);

        return $rpta;
    }



    public function agregarNCfijaAv($datos)
    {

        date_default_timezone_set("America/Lima");
        $hoy = date("Y-m-d");

        $obj = new conectar();
        $conexion = $obj->conexion();


        $sqlLLAMADA = "INSERT into nc_avanzado (id_usuario, id_supervisor,fecha_ingreso, ruc,razon_social,tipo, subtipo, descripcion,q_lineas,cargo_fijo,modalidad,contacto,telefono1,telefono2,estado,validacion,direccion,coordenadas,zonal,comentario,tecnologia,back,nodo)
                                    values ('$datos[0]',
                                            '$datos[1]',
                                             '$hoy',
                                            '$datos[8]',
                                            '$datos[9]',
                                            'AVANZADO',
                                            '$datos[5]',
                                            '$datos[6]',
                                            '$datos[3]',
                                            '$datos[7]',
                                            '$datos[4]',
                                             '$datos[10]',
                                             '$datos[11]',
                                             '$datos[12]',
                                             'PENDIENTE',
                                             'PENDIENTE',
                                             '$datos[13]',
                                                '$datos[14]',
                                               '$datos[15]',
                                                '$datos[16]',
                                                '$datos[17]',
                                                '$datos[18]',
                                                'SINNODO'

                                            )";

        $rpta = mysqli_query($conexion, $sqlLLAMADA);

        return $rpta;
    }


    public function obtenCargoFija($idcargo)
    {
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sql = "SELECT nc.q_lineas,nc.modalidad,nc.subtipo,nc.descripcion,nc.cargo_fijo,nc.ruc,
                nc.razon_social,nc.contacto,nc.telefono1,nc.telefono2,nc.direccion,nc.coordenadas,
                t.nombre,usu.personal,nc.comentario,nc.estado, nc.comentario_validador,nc.tecnologia,nc.ejecutivo_telefonica
                    from nc_fija as nc inner join usuario as usu on nc.id_usuario=usu.id_usuario
                    left join tienda as t on t.id_tienda=nc.zonal
                    where id_cargo ='$idcargo' ";

        $result = mysqli_query($conexion, $sql);
        $ver = mysqli_fetch_array($result);

        $sqlsup = "SELECT sup.nombre
                    from nc_fija as nc inner join supervisor as sup on nc.id_supervisor=sup.id_supervisor
                   
                    where id_cargo ='$idcargo' ";

        $resultsup = mysqli_query($conexion, $sqlsup);
        $versup = mysqli_fetch_array($resultsup);


        $datos = array(
            'q_lineas' => $ver[0],
            'modalidad' => $ver[1],
            'subtipo' => $ver[2],
            'descripcion' => $ver[3],
            'cargo_fijo' => $ver[4],
            'ruc' => $ver[5],
            'razon_social' => $ver[6],
            'contacto' => $ver[7],
            'telefono1' => $ver[8],
            'telefono2' => $ver[9],
            'direccion' => $ver[10],
            'coordenadas' => $ver[11],
            'zonal' => $ver[12],
            'personal' => $ver[13],
            'comentario' => $ver[14],
            'estado' => $ver[15],
            'comentario_validador' => $ver[16],
            'tecnologia' => $ver[17],
            'ejecutivo_telefonica' => $ver[18],
            'supervisor' => $versup[0]




        );
        return $datos;
    }


    public function obtenCargoFijaAv($idcargo)
    {
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sql = "SELECT 
                        nc.q_lineas,
                        nc.modalidad,
                        nc.subtipo,
                        nc.descripcion,
                        nc.cargo_fijo,
                        nc.ruc,
                        nc.razon_social,
                        nc.contacto,
                        nc.telefono1,
                        nc.telefono2,
                        nc.direccion,
                        nc.coordenadas,
                        t.nombre,
                        usu.personal,
                        nc.comentario,
                        nc.estado, 
                        nc.comentario_validador,
                        nc.tecnologia,
                        nc.ejecutivo_telefonica
                        from nc_avanzado as nc inner join usuario as usu on nc.id_usuario=usu.id_usuario
                        left join tienda as t on t.id_tienda=nc.zonal
                        where id_cargo ='$idcargo' ";

        $result = mysqli_query($conexion, $sql);
        $ver = mysqli_fetch_array($result);

        $sqlsup = "SELECT sup.nombre
                    from nc_avanzado as nc 
                    inner join supervisor as sup on nc.id_supervisor=sup.id_supervisor
                    where id_cargo ='$idcargo' ";

        $resultsup = mysqli_query($conexion, $sqlsup);
        $versup = mysqli_fetch_array($resultsup);


        $datos = array(
            'q_lineas' => $ver[0],
            'modalidad' => $ver[1],
            'subtipo' => $ver[2],
            'descripcion' => $ver[3],
            'cargo_fijo' => $ver[4],
            'ruc' => $ver[5],
            'razon_social' => $ver[6],
            'contacto' => $ver[7],
            'telefono1' => $ver[8],
            'telefono2' => $ver[9],
            'direccion' => $ver[10],
            'coordenadas' => $ver[11],
            'zonal' => $ver[12],
            'personal' => $ver[13],
            'comentario' => $ver[14],
            'estado' => $ver[15],
            'comentario_validador' => $ver[16],
            'tecnologia' => $ver[17],
            'ejecutivo_telefonica' => $ver[18],
            'supervisor' => $versup[0]

        );
        return $datos;
    }


    public function agregarncfValidacion($datos)
    {

        date_default_timezone_set("America/Lima");
        $hoy = date("Y-m-d");

        $obj = new conectar();
        $conexion = $obj->conexion();


        $sqlnotadecargo = "UPDATE nc_fija SET validacion='$datos[2]',id_validador='$datos[1]',
            fecha_validacion='$hoy' , comentario_validador='$datos[3]',ejecutivo_telefonica='$datos[5]' WHERE id_cargo='$datos[4]'
                                    ";

        $rpta = mysqli_query($conexion, $sqlnotadecargo);

        return $rpta;
    }

    public function agregarncfValidacionAv($datos)
    {

        date_default_timezone_set("America/Lima");
        $hoy = date("Y-m-d");

        $obj = new conectar();
        $conexion = $obj->conexion();


        $sqlnotadecargo = "UPDATE nc_avanzado SET validacion='$datos[2]',id_validador='$datos[1]',
            fecha_validacion='$hoy' , comentario_validador='$datos[3]',ejecutivo_telefonica='$datos[5]',cargo_fijo='$datos[6]' WHERE id_cargo='$datos[4]'
                                    ";

        $rpta = mysqli_query($conexion, $sqlnotadecargo);

        return $rpta;
    }


    public function obtenCargoFijaBack($idcargo)
    {
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sql = "SELECT  nc.q_lineas,
                        nc.modalidad,
                        nc.subtipo,
                        nc.descripcion,
                        nc.cargo_fijo,
                        nc.ruc,
                        nc.razon_social,
                        nc.contacto,
                        nc.telefono1,
                        nc.telefono2,
                        nc.direccion,
                        nc.coordenadas,
                        t.nombre,
                        usu.personal,
                        nc.comentario,
                        nc.estado, 
                        nc.fecha_ingreso,
                        nc.fecha_validacion,
                        nc.validacion,
                        nc.comentario_validador,
                        nc.estado, 
                        nc.peticion,
                        nc.contrata,
                        nc.fecha_liquidacion,
                        nc.nasignado, 
                        nc.ejecutivo_telefonica, 
                        nc.comentario_back, 
                        nc.zonal_telefonica, 
                        nc.fecha_actualizacion,
                        nc.tecnologia,
                        nc.caso_sf,
                        nc.id_cartera,
                        nc.nodo,
                        nc.ncfinanciera,
                        nc.ncfe
                
                from nc_fija as nc inner join usuario as usu on nc.id_usuario=usu.id_usuario
                left join tienda as t on t.id_tienda=nc.zonal
                where id_cargo ='$idcargo' ";

        $result = mysqli_query($conexion, $sql);
        $ver = mysqli_fetch_array($result);

        $sqlsup = "SELECT sup.nombre
                    from nc_fija as nc inner join supervisor as sup on nc.id_supervisor=sup.id_supervisor
                   
                    where id_cargo ='$idcargo' ";

        $resultsup = mysqli_query($conexion, $sqlsup);
        $versup = mysqli_fetch_array($resultsup);


        $sqlval = "SELECT usu.personal
                    from nc_fija as nc inner join usuario as usu on nc.id_validador=usu.id_usuario
                   
                    where id_cargo ='$idcargo' ";

        $resultval = mysqli_query($conexion, $sqlval);
        $verval = mysqli_fetch_array($resultval);
        $datos = array(
            'q_lineas' => $ver[0],
            'modalidad' => $ver[1],
            'subtipo' => $ver[2],
            'descripcion' => $ver[3],
            'cargo_fijo' => $ver[4],
            'ruc' => $ver[5],
            'razon_social' => $ver[6],
            'contacto' => $ver[7],
            'telefono1' => $ver[8],
            'telefono2' => $ver[9],
            'direccion' => $ver[10],
            'coordenadas' => $ver[11],
            'zonal' => $ver[12],
            'personal' => $ver[13],
            'comentario' => $ver[14],
            'estado' => $ver[15],
            'fecha_ingreso' => $ver[16],
            'supervisor' => $versup[0],
            'fecha_validacion' => $ver[17],
            'validacion' => $ver[18],
            'validador' => $verval[0],
            'comentario_validador' => $ver[19],
            'estado_actual' => $ver[20],
            'peticion' => $ver[21],
            'contrata' => $ver[22],
            'fecha_liquidacion' => $ver[23],
            'nasignado' => $ver[24],
            'ejecutivo_telefonica' => $ver[31],
            'comentario_back' => $ver[26],
            'zonal_telefonica' => $ver[27],
            'fecha_actualizacion' => $ver[28],
            'tecnologia' => $ver[29],
            'caso_sf' => $ver[30],
            'nodo' => $ver[32],
            'ncfinanciera' => $ver[33],
            'ncfe' => $ver[34]
        );
        return $datos;
    }


    public function obtenCargoFijaBackAv($idcargo)
    {
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sql = "SELECT nc.q_lineas,
                nc.modalidad,
                nc.subtipo,
                nc.descripcion,
                nc.cargo_fijo,
                nc.ruc,
                nc.razon_social,
                nc.contacto,
                nc.telefono1,
                nc.telefono2,
                nc.direccion,
                nc.coordenadas,
                t.nombre,
                usu.personal,
                nc.comentario,
                nc.estado, 
                nc.fecha_ingreso,
                nc.fecha_validacion,
                nc.validacion,
                nc.comentario_validador,
                nc.estado, 
                nc.peticion,
                nc.contrata,
                nc.fecha_liquidacion,
                nc.nasignado, 
                nc.ejecutivo_telefonica, 
                nc.comentario_back, 
                nc.zonal_telefonica, 
                nc.fecha_actualizacion,
                nc.tecnologia,
                nc.caso_sf,
                nc.back,
                nc.nodo,
                nc.id_cartera,
                nc.mes,

                nc.estado
                from nc_avanzado as nc 
                inner join usuario as usu on nc.id_usuario=usu.id_usuario
                left join tienda as t on t.id_tienda=nc.zonal
                where id_cargo ='$idcargo' ";

        $result = mysqli_query($conexion, $sql);
        $ver = mysqli_fetch_array($result);

        $sqlsup = "SELECT sup.nombre
                    from nc_avanzado as nc inner join supervisor as sup on nc.id_supervisor=sup.id_supervisor
                   
                    where id_cargo ='$idcargo' ";

        $resultsup = mysqli_query($conexion, $sqlsup);
        $versup = mysqli_fetch_array($resultsup);


        $sqlval = "SELECT usu.personal
                    from nc_avanzado as nc inner join usuario as usu on nc.id_validador=usu.id_usuario
                   
                    where id_cargo ='$idcargo' ";

        $resultval = mysqli_query($conexion, $sqlval);
        $verval = mysqli_fetch_array($resultval);


        $datos = array(
            'q_lineas' => $ver[0],
            'modalidad' => $ver[1],
            'subtipo' => $ver[2],
            'descripcion' => $ver[3],
            'cargo_fijo' => $ver[4],
            'ruc' => $ver[5],
            'razon_social' => $ver[6],
            'contacto' => $ver[7],
            'telefono1' => $ver[8],
            'telefono2' => $ver[9],
            'direccion' => $ver[10],
            'coordenadas' => $ver[11],
            'zonal' => $ver[12],
            'personal' => $ver[13],
            'comentario' => $ver[14],
            'estado' => $ver[15],
            'fecha_ingreso' => $ver[16],
            'supervisor' => $versup[0],
            'fecha_validacion' => $ver[17],
            'validacion' => $ver[18],
            'validador' => $verval[0],
            'comentario_validador' => $ver[19],
            'estado_actual' => $ver[20],
            'peticion' => $ver[21],
            'contrata' => $ver[22],
            'fecha_liquidacion' => $ver[23],
            'nasignado' => $ver[24],
            'ejecutivo_telefonica' => $ver[33],
            'comentario_back' => $ver[26],
            'zonal_telefonica' => $ver[27],
            'fecha_actualizacion' => $ver[28],
            'tecnologia' => $ver[29],
            'caso_sf' => $ver[30],
            'back' => $ver[31],
            'nodo' => $ver[32],
            //'caso_sf' => $ver[35],
            'mes' => $ver[34]
        );
        return $datos;
    }


    public function agregarncfBack($datos)
    {

        date_default_timezone_set("America/Lima");
        $hoy = date("Y-m-d");

        $obj = new conectar();
        $conexion = $obj->conexion();


        $sqlnotadecargo = "UPDATE nc_fija SET 
                estado='$datos[2]',
                peticion='$datos[3]',
                contrata='$datos[4]',
                fecha_actualizacion='$hoy', 
                fecha_liquidacion='$datos[5]', 
                nasignado='$datos[6]', 
                comentario_back='$datos[9]', 
                zonal_telefonica='$datos[8]', 
                id_back='$datos[0]',
                caso_sf='$datos[10]',
                id_cartera='$datos[7]',
                nodo='$datos[11]',
                ncfinanciera='$datos[12]',
                ncfe='$datos[13]'
                
            WHERE id_cargo='$datos[1]'
                                    ";

        $rpta = mysqli_query($conexion, $sqlnotadecargo);

        return $rpta;
    }

    public function agregarncfBackAv($datos)
    {

        date_default_timezone_set("America/Lima");
        $hoy = date("Y-m-d");

        $obj = new conectar();
        $conexion = $obj->conexion();


        $sqlnotadecargo = "UPDATE nc_avanzado SET estado='$datos[2]',peticion='$datos[3]',
            contrata='$datos[4]',fecha_actualizacion='$hoy' , fecha_liquidacion='$datos[5]' 
            , nasignado='$datos[6]', comentario_back='$datos[9]' , id_cartera='$datos[7]'
            , zonal_telefonica='$datos[8]', id_back='$datos[0]',caso_sf='$datos[10]',nodo='$datos[11]', 
            mes='$datos[12]'

            WHERE id_cargo='$datos[1]'
                                    ";

        $rpta = mysqli_query($conexion, $sqlnotadecargo);

        return $rpta;
    }


    public function agregarNCfijacomentario($datos)
    {


        $obj = new conectar();
        $conexion = $obj->conexion();


        $sqlnotadecargo = "UPDATE nc_fija SET comentario='$datos[1]',telefono1='$datos[2]',
            telefono2='$datos[3]'
            WHERE id_cargo='$datos[0]' ";

        $rpta = mysqli_query($conexion, $sqlnotadecargo);

        return $rpta;
    }

    public function agregarNCfijacomentarioAv($datos)
    {


        $obj = new conectar();
        $conexion = $obj->conexion();


        $sqlnotadecargo = "UPDATE nc_avanzado SET comentario='$datos[1]',telefono1='$datos[2]',
            telefono2='$datos[3]'
            WHERE id_cargo='$datos[0]' ";

        $rpta = mysqli_query($conexion, $sqlnotadecargo);

        return $rpta;
    }


    public function agregarNCmovil($datos)
    {

        date_default_timezone_set("America/Lima");
        $hoy = date("Y-m-d");

        $obj = new conectar();
        $conexion = $obj->conexion();


        $sqlLLAMADA = "INSERT INTO nc_movil (   id_usuario, 
                                                id_supervisor,
                                                fecha_ingreso, 
                                                ruc,
                                                razon_social,
                                                q_lineas,
                                                cargo_fijo,
                                                modalidad,tipo,
                                                contacto,
                                                telefono1,
                                                telefono2,
                                                estado,
                                                validacion,
                                                zonal,
                                                comentario_ejecutivo,
                                                oportunidad,
                                                nodo,
                                                nivel_negociacion,
                                                fecha_estimada, 
                                                ejecutivo_tdp, 
                                                tipo_producto, 
                                                etapa
                                                )
                                    values ('$datos[0]',
                                            '$datos[1]',
                                            '$hoy',
                                            '$datos[5]',
                                            '$datos[6]',
                                            '$datos[2]',
                                            '$datos[4]',
                                            '$datos[3]',
                                            'MOVIL',
                                            '$datos[7]',
                                            '$datos[8]',
                                            '$datos[9]',
                                            'PENDIENTE',
                                            'PENDIENTE',
                                            '$datos[10]',
                                            '$datos[11]',
                                            '$datos[12]',
                                            'SINNODO',
                                            '$datos[14]',
                                            '$datos[13]',                                                
                                            '$datos[15]',
                                            '$datos[16]',
                                            '$datos[17]'
                                            )";
        $rpta = mysqli_query($conexion, $sqlLLAMADA);
        return $rpta;
    }


    public function agregarNCmovilcomentario($datos)
    {
        $obj = new conectar();
        $conexion = $obj->conexion();
        $sqlnotadecargo = " UPDATE nc_movil 
                            SET comentario_ejecutivo='$datos[1]',
                                telefono1='$datos[2]',
                                telefono2='$datos[3]',
                                q_lineas='$datos[4]',
                                cargo_fijo='$datos[5]',
                                nivel_negociacion='$datos[6]',
                                fecha_estimada='$datos[7]',
                                oportunidad='$datos[8]',
                                etapa='$datos[9]'
                            WHERE id_cargo='$datos[0]' ";
        $rpta = mysqli_query($conexion, $sqlnotadecargo);
        return $rpta;
    }
    public function obtenCargoMovilBack($idcargo)
    {
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sql = "SELECT nc.q_lineas,
                        nc.modalidad,
                        nc.cargo_fijo,
                        nc.ruc,
                        nc.razon_social,
                        nc.contacto,
                        nc.telefono1,
                        nc.telefono2,
                        t.nombre,
                        usu.personal,
                        nc.comentario_ejecutivo,
                        nc.estado, 
                        nc.fecha_ingreso,
                        nc.fecha_validacion,
                        nc.validacion,
                        nc.comentario_validador,
                        nc.estado, 
                        nc.ejecutivo_telefonica, 
                        nc.comentario_back, 
                        nc.zonal_telefonica, 
                        nc.fecha_actualizacion, 
                        nc.oportunidad,
                        nc.id_cartera,
                        nc.nodo,
                        nc.nivel_negociacion,
                        nc.fecha_estimada,
                        nc.casofwa,
                        car.propietario,
                        nc.tipo_producto,
                        nc.etapa,
                        nc.comision,
                        nc.observacion,
                        nc.descuento,
                        nc.fecha_liquidacion,
                        nc.comentario_liquidador,
                        nc.oportunidad,
                        nc.ciclo,
                        nc.cuenta_financiera
                        from nc_movil as nc 
                        inner join usuario as usu on nc.id_usuario=usu.id_usuario
                        left join tienda as t on t.id_tienda=nc.zonal
                        left join cartera as car on car.id_cartera = nc.ejecutivo_tdp
                        where id_cargo ='$idcargo' ";

        $result = mysqli_query($conexion, $sql);
        $ver = mysqli_fetch_array($result);

        $sqlsup = "SELECT sup.nombre
                    from nc_movil as nc inner join supervisor as sup on nc.id_supervisor=sup.id_supervisor
                    where id_cargo ='$idcargo' ";
        $resultsup = mysqli_query($conexion, $sqlsup);
        $versup = mysqli_fetch_array($resultsup);
        $sqlval = "SELECT usu.personal
                    from nc_movil as nc inner join usuario as usu on nc.id_validador=usu.id_usuario
                    where id_cargo ='$idcargo' ";
        $resultval = mysqli_query($conexion, $sqlval);
        $verval = mysqli_fetch_array($resultval);
        $datos = array(
            'q_lineas' => $ver[0],
            'modalidad' => $ver[1],
            'cargo_fijo' => $ver[2],
            'ruc' => $ver[3],
            'razon_social' => $ver[4],
            'contacto' => $ver[5],
            'telefono1' => $ver[6],
            'telefono2' => $ver[7],
            'zonal' => $ver[8],
            'personal' => $ver[9],
            'comentario' => $ver[10],
            'estado' => $ver[11],
            'fecha_ingreso' => $ver[12],
            'supervisor' => $versup[0],
            'fecha_validacion' => $ver[13],
            'validacion' => $ver[14],
            'validador' => $verval[0],
            'comentario_validador' => $ver[15],
            'estado_actual' => $ver[16],
            'ejecutivo_telefonica' => $ver[22],
            'comentario_back' => $ver[18],
            'zonal_telefonica' => $ver[19],
            'fecha_actualizacion' => $ver[20],
            'oportunidad' => $ver[21],
            'nodo' => $ver[23],
            'nivel_negociacion' => $ver[24],
            'fecha_estimada' => $ver[25],
            'casofwa' => $ver[26],
            'ejecutivo_tdp' => $ver[27],
            'tipo_producto' => $ver[28],
            'etapa' => $ver[29],
            'comision' => $ver[30],
            'observacion' => $ver[31],
            'descuento' => $ver[32],
            'fecha_liquidacion' => $ver[33],
            'comentario_liquidador' => $ver[34],
            'ciclo' => $ver[36],
            'cuenta_financiera' => $ver[37]
        );
        return $datos;
    }

    public function obtenLiquidacion($idcargo)
    {
        $obj = new conectar();
        $conexion = $obj->conexion();
        $sql = "SELECT nc.q_lineas,
                        nc.modalidad,
                        nc.cargo_fijo,
                        nc.ruc,
                        nc.razon_social,
                        nc.contacto,
                        nc.telefono1,
                        nc.telefono2,
                        t.nombre,
                        usu.personal,
                        nc.comentario_ejecutivo,
                        nc.estado, 
                        nc.fecha_ingreso,
                        nc.fecha_validacion,
                        nc.validacion,
                        nc.comentario_validador,
                        nc.estado, 
                        nc.ejecutivo_telefonica, 
                        nc.comentario_back, 
                        nc.zonal_telefonica, 
                        nc.fecha_actualizacion, 
                        nc.oportunidad,
                        nc.id_cartera,
                        nc.nodo,
                        nc.nivel_negociacion,
                        nc.fecha_estimada,
                        nc.casofwa,
                        nc.ejecutivo_tdp,
                        nc.tipo_producto,
                        nc.etapa,
                        nc.tipo_etapa,
                        nc.comision,
                        nc.observacion,
                        nc.descuento,
                        nc.fecha_liquidacion,
                        nc.comentario_liquidador
                        from nc_movil as nc 
                        inner join usuario as usu on nc.id_usuario=usu.id_usuario
                        left join tienda as t on t.id_tienda=nc.zonal
                        where id_cargo ='$idcargo' ";
        $result = mysqli_query($conexion, $sql);
        $ver = mysqli_fetch_array($result);
        $sqlsup = "SELECT sup.nombre
                    from nc_movil as nc inner join supervisor as sup on nc.id_supervisor=sup.id_supervisor
                    where id_cargo ='$idcargo' ";
        $resultsup = mysqli_query($conexion, $sqlsup);
        $versup = mysqli_fetch_array($resultsup);
        $sqlval = "SELECT usu.personal
                    from nc_movil as nc inner join usuario as usu on nc.id_validador=usu.id_usuario
                    where id_cargo ='$idcargo' ";
        $resultval = mysqli_query($conexion, $sqlval);
        $verval = mysqli_fetch_array($resultval);
        $datos = array(
            'q_lineas' => $ver[0],
            'modalidad' => $ver[1],
            'cargo_fijo' => $ver[2],
            'ruc' => $ver[3],
            'razon_social' => $ver[4],
            'contacto' => $ver[5],
            'telefono1' => $ver[6],
            'telefono2' => $ver[7],
            'zonal' => $ver[8],
            'personal' => $ver[9],
            'comentario' => $ver[10],
            'estado' => $ver[11],
            'fecha_ingreso' => $ver[12],
            'supervisor' => $versup[0],
            'fecha_validacion' => $ver[13],
            'validacion' => $ver[14],
            'validador' => $verval[0],
            'comentario_validador' => $ver[15],
            'estado_actual' => $ver[16],
            'ejecutivo_telefonica' => $ver[22],
            'comentario_back' => $ver[18],
            'zonal_telefonica' => $ver[19],
            'fecha_actualizacion' => $ver[20],
            'oportunidad' => $ver[21],
            'nodo' => $ver[23],
            'nivel_negociacion' => $ver[24],
            'fecha_estimada' => $ver[25],
            'casofwa' => $ver[26],
            'ejecutivo_tdp' => $ver[27],
            'tipo_producto' => $ver[28],
            'etapa' => $ver[29],
            'tipo_etapa' => $ver[30],
            'comision' => $ver[31],
            'observacion' => $ver[32],
            'descuento' => $ver[33],
            'fecha_liquidacion' => $ver[34],
            'comentario_liquidador' => $ver[35]
        );
        return $datos;
    }

    public function agregarliquidacion($datos)
    {
        date_default_timezone_set("America/Lima");
        $obj = new conectar();
        $conexion = $obj->conexion();
        $sqlnotadecargo = "UPDATE nc_movil 
        SET         comision='$datos[1]',
                    observacion='$datos[2]', 
                    descuento='$datos[3]', 
                    fecha_liquidacion='$datos[4]',
                    comentario_liquidador='$datos[5]'
            WHERE id_cargo='$datos[0]' ";
        $rpta = mysqli_query($conexion, $sqlnotadecargo);
        return $rpta;
    }

    public function agregarNCmovilcomentarioback($datos)
    {
        $obj = new conectar();
        $conexion = $obj->conexion();
        $sqlnotadecargo = "UPDATE nc_movil 
                            SET q_lineas='$datos[1]',
                                cargo_fijo='$datos[2]'
            WHERE id_cargo='$datos[0]' ";
        $rpta = mysqli_query($conexion, $sqlnotadecargo);
        return $rpta;
    }

    public function obtenCargoMovil($idcargo)
    {
        $obj = new conectar();
        $conexion = $obj->conexion();

        $sql = "SELECT  nc.q_lineas,
                        nc.modalidad,
                        nc.cargo_fijo,
                        nc.ruc,
                        nc.razon_social,
                        nc.contacto,
                        nc.telefono1,
                        nc.telefono2,
                        t.nombre,
                        usu.personal,
                        nc.comentario_ejecutivo,
                        nc.estado, 
                        nc.nivel_negociacion, 
                        nc.fecha_estimada,
                        car.propietario,
                        nc.tipo_producto,
                        nc.etapa,
                        nc.oportunidad
                        from nc_movil as nc 
                        inner join usuario as usu on nc.id_usuario = usu.id_usuario
                        left join tienda as t on t.id_tienda=nc.zonal
                        left join cartera as car on car.id_cartera = nc.ejecutivo_tdp
                        where id_cargo ='$idcargo' ";
        $result = mysqli_query($conexion, $sql);
        $ver = mysqli_fetch_array($result);

        $datos = array(
            'q_lineas' => $ver[0],
            'modalidad' => $ver[1],
            'cargo_fijo' => $ver[2],
            'ruc' => $ver[3],
            'razon_social' => $ver[4],
            'contacto' => $ver[5],
            'telefono1' => $ver[6],
            'telefono2' => $ver[7],
            'zonal' => $ver[8],
            'personal' => $ver[9],
            'comentario' => $ver[10],
            'estado' => $ver[11],
            'nivel_negociacion' => $ver[12],
            'fecha_estimada' => $ver[13],
            'ejecutivo_tdp' => $ver[14],
            'tipo_producto' => $ver[15],
            'etapa' => $ver[16],
            'oportunidad' => $ver[17]
        );
        return $datos;
    }


    public function agregarncmValidacion($datos)
    {
        date_default_timezone_set("America/Lima");
        $hoy = date("Y-m-d");
        $obj = new conectar();
        $conexion = $obj->conexion();
        $sqlnotadecargo = "UPDATE nc_movil 
            SET validacion='$datos[2]',
                id_validador='$datos[1]',
                fecha_validacion='$hoy', 
                comentario_validador='$datos[3]',
                q_lineas='$datos[5]',
                cargo_fijo='$datos[6]'  
            WHERE id_cargo='$datos[4]' ";
        $rpta = mysqli_query($conexion, $sqlnotadecargo);
        return $rpta;
    }

    public function agregarncmBack($datos)
    {
        date_default_timezone_set("America/Lima");
        $hoy = date("Y-m-d");
        $obj = new conectar();
        $conexion = $obj->conexion();
        $sqlnotadecargo = "UPDATE nc_movil 
        SET         estado='$datos[2]',
                    fecha_actualizacion='$datos[6]' , 
                    comentario_back='$datos[5]', 
                    id_cartera='$datos[3]',
                    zonal_telefonica='$datos[4]', 
                    id_back='$datos[0]',
                    oportunidad='$datos[7]',
                    nodo='$datos[8]',
                    casofwa='$datos[9]',
                    etapa='$datos[10]',
                    ciclo='$datos[11]',
                    cuenta_financiera='$datos[12]'
            WHERE id_cargo='$datos[1]' ";
        $rpta = mysqli_query($conexion, $sqlnotadecargo);
        return $rpta;
    }

    public function obtenDatosEjecutivo($dat)
    {
        $datos = array();
        $obj = new conectar();
        $conexion = $obj->conexion();
        $sql = " SELECT id_cartera,nombre,propietario,estado,DNI FROM cartera WHERE id_tienda='$dat' and estado=1 order by propietario asc ";
        $result = mysqli_query($conexion, $sql);
        while ($row = mysqli_fetch_array($result)) {
            $datos[] = $row;
        }
        return $datos;
    }

    public function obtenDatosTipoEtapa($dat)
    {
        $datos = array();
        $obj = new conectar();
        $conexion = $obj->conexion();
        $sql = " SELECT id_tipo_etapa, descripcion, estado FROM tipo_etapa WHERE id_etapa='$dat' and estado = 1 order by descripcion asc";
        $result = mysqli_query($conexion, $sql);
        while ($row = mysqli_fetch_array($result)) {
            $datos[] = $row;
        }
        return $datos;
    }

    public function obtenDatosCartera($dat)
    {
        $datos = array();
        $obj = new conectar();
        $conexion = $obj->conexion();
        $sql = " SELECT id_cartera,nombre,DNI FROM cartera WHERE  id_cartera='$dat[0]'  and estado=1 ";
        $result = mysqli_query($conexion, $sql);
        $ver = mysqli_fetch_array($result);
        $datos = array(
            'id_cartera' => $ver[0],
            'nombre' => $ver[1],
            'DNI' => $ver[2]
        );
        return $datos;
    }

    public function obtenDatosEjecutivoCartera()
    {
        $obj = new conectar();
        $conexion = $obj->conexion();
        $datos = array();
        $sql = "SELECT id_cartera,nombre,propietario,DNI FROM cartera ";
        $result = mysqli_query($conexion, $sql);
        while ($row = mysqli_fetch_array($result)) {
            $datos[] = $row;
        }
        return $datos;
    }
}
