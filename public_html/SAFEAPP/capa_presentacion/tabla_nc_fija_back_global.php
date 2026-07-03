<?php

require_once '../capa_conexion/conexion.php';

$obj = new conectar();
$conexion = $obj->conexion();

$ano = $_GET['ano'];
$periodo = $_GET['periodo'];
$ncestado = $_GET['ncestadof'];
$tienda = $_GET['tienda'];

if ($tienda === 'TODO') {
    if (
        $ncestado === 'OBSERVADO'
        || $ncestado === 'XINGRESAR'
        || $ncestado === 'PENDIENTE'
        || $ncestado === 'AGENDADO'
        || $ncestado === 'INICIADO'
        || $ncestado === 'BOLSA'
        || $ncestado === 'ARBITRAJE'
        || $ncestado === 'ADR'
    ) {
        $sqlncFija = " SELECT   ncf.id_cargo,
                                ncf.fecha_ingreso, 
                                ncf.ruc,
                                ncf.razon_social,
                                ncf.peticion,
                                ncf.subtipo, 
                                ncf.descripcion,
                                
                                ncf.direccion, 
                                ncf.estado,
                                ncf.contrata,
                                ncf.ejecutivo_telefonica,
                                car.propietario, 
                                usu.personal,
                                ncf.nodo,
                                ncf.ncfe
                                from nc_fija as ncf 
                                left join cartera as car on car.id_cartera=ncf.id_cartera 
                                left join usuario as usu on usu.id_usuario=ncf.id_usuario
                                where  year(ncf.fecha_validacion)<='$ano' and ncf.estado=
                                '$ncestado' and ncf.validacion='PROCEDE' 
                                ORDER BY ncf.fecha_ingreso DESC";
    } else {
        if ($ncestado === 'COMPLETADO') {
            $sqlncFija = " SELECT   ncf.id_cargo,
                                    ncf.fecha_ingreso, 
                                    ncf.ruc,
                                    ncf.razon_social,
                                    ncf.peticion,
                                    ncf.subtipo,   
                                    ncf.descripcion,
                                    
                                    ncf.direccion, 
                                    ncf.estado,
                                    ncf.contrata,
                                    ncf.ejecutivo_telefonica,
                                    car.propietario, 
                                    usu.personal,
                                    ncf.nodo, 
                                    ncf.ncfe
                                    from nc_fija as ncf 
                                    left join cartera as car on car.id_cartera=ncf.id_cartera 
                                    left join usuario as usu on usu.id_usuario=ncf.id_usuario
                                    where month(ncf.fecha_liquidacion)='$periodo' 
                                    and year(ncf.fecha_liquidacion)='$ano' 
                                    and ncf.estado= '$ncestado' 
                                    and ncf.validacion='PROCEDE'   
                                    ORDER BY ncf.fecha_ingreso DESC";
        } else {
            if ($ncestado === 'CANCELADO') {
                $sqlncFija = " SELECT   ncf.id_cargo,
                                        ncf.fecha_ingreso, 
                                        ncf.ruc,
                                        ncf.razon_social,
                                        ncf.peticion,
                                        ncf.subtipo, 
                                        ncf.descripcion, 
                                        
                                        ncf.direccion, 
                                        ncf.estado,
                                        ncf.contrata,
                                        ncf.ejecutivo_telefonica,
                                        car.propietario, 
                                        usu.personal,
                                        ncf.nodo, 
                                        ncf.ncfe
                                        from nc_fija as ncf 
                                        left join cartera as car on car.id_cartera=ncf.id_cartera 
                                        left join usuario as usu on usu.id_usuario=ncf.id_usuario
                                        where month(ncf.fecha_validacion)='$periodo' 
                                        and year(ncf.fecha_validacion)='$ano' 
                                        and ncf.estado=
'$ncestado' and ncf.validacion='PROCEDE'  
ORDER BY ncf.fecha_ingreso DESC";
            } else {
                $sqlncFija = " SELECT   ncf.id_cargo,
                                        ncf.fecha_ingreso, 
                                        ncf.ruc,
                                        ncf.razon_social,
                                        ncf.peticion,
                                        ncf.subtipo, 
                                        ncf.descripcion, 
                                        
                                        ncf.direccion, 
                                        ncf.estado,
                                        ncf.contrata,
                                        ncf.ejecutivo_telefonica,
                                        car.propietario, 
                                        usu.personal,
                                        ncf.nodo, 
                                        ncf.ncfe
                                        from nc_fija as ncf 
                                        left join cartera as car on car.id_cartera=ncf.id_cartera 
                                        left join usuario as usu on usu.id_usuario=ncf.id_usuario
                                        where  year(ncf.fecha_validacion)<='$ano' 
                                        and ncf.estado in ('OBSERVADO','PENDIENTE','XINGRESAR','AGENDADO','INICIADO','BOLSA', 'ARBITRAJE','ADR') 
                                        and ncf.validacion='PROCEDE'  


UNION 

                            SELECT      ncf.id_cargo,
                                        ncf.fecha_ingreso, 
                                        ncf.ruc,
                                        ncf.razon_social,
                                        ncf.peticion,
                                        ncf.subtipo, 
                                        ncf.descripcion, 
                                        
                                        ncf.direccion, 
                                        ncf.estado,
                                        ncf.contrata,
                                        ncf.ejecutivo_telefonica,
                                        car.propietario, 
                                        usu.personal,
                                        ncf.nodo, 
                                        ncf.ncfe
                                        from nc_fija as ncf 
                                        left join cartera as car on car.id_cartera=ncf.id_cartera 
                                        left join usuario as usu on usu.id_usuario=ncf.id_usuario
                                        where month(ncf.fecha_liquidacion)='$periodo' 
                                        and year(ncf.fecha_liquidacion)='$ano' 
                                        and ncf.estado='COMPLETADO' and ncf.validacion='PROCEDE'  


UNION 

                            SELECT      ncf.id_cargo,
                                        ncf.fecha_ingreso, 
                                        ncf.ruc,
                                        ncf.razon_social,
                                        ncf.peticion,
                                        ncf.subtipo, 
                                        ncf.descripcion, 
                                        
                                        ncf.direccion, 
                                        ncf.estado,
                                        ncf.contrata,
                                        ncf.ejecutivo_telefonica,
                                        car.propietario, 
                                        usu.personal,
                                        ncf.nodo, 
                                        ncf.ncfe
                                        from nc_fija as ncf 
                                        left join cartera as car on car.id_cartera=ncf.id_cartera 
                                        left join usuario as usu on usu.id_usuario=ncf.id_usuario
                                        where month(ncf.fecha_validacion)='$periodo' 
                                        and year(ncf.fecha_validacion)='$ano' 
                                        and ncf.estado= 'CANCELADO' and ncf.validacion='PROCEDE'  ";
            }
        }
    }
} else {
    if (
        $ncestado === 'OBSERVADO'
        || $ncestado === 'PENDIENTE'
        || $ncestado === 'XINGRESAR'
        || $ncestado === 'AGENDADO'
        || $ncestado === 'INICIADO'
        || $ncestado === 'BOLSA'
        || $ncestado === 'ARBITRAJE'
        || $ncestado === 'ADR'
    ) {
        $sqlncFija = " SELECT   ncf.id_cargo,
                                ncf.fecha_ingreso, 
                                ncf.ruc,
                                ncf.razon_social,
                                ncf.peticion,
                                ncf.subtipo, 
                                ncf.descripcion, 
                                
                                ncf.direccion, 
                                ncf.estado,
                                ncf.contrata,
                                ncf.ejecutivo_telefonica,
                                car.propietario, 
                                usu.personal,
                                ncf.nodo, 
                                ncf.ncfe
                                from nc_fija as ncf 
                                left join cartera as car on car.id_cartera=ncf.id_cartera 
                                left join usuario as usu on usu.id_usuario=ncf.id_usuario
                                where  year(ncf.fecha_validacion)<='$ano' 
                                and ncf.estado='$ncestado' 
                                and ncf.validacion='PROCEDE' 
                                and ncf.zonal='$tienda'
                                ORDER BY ncf.fecha_ingreso DESC";
    } else {
        if ($ncestado === 'COMPLETADO') {
            $sqlncFija = " SELECT ncf.id_cargo,ncf.fecha_ingreso, ncf.ruc,ncf.razon_social,ncf.peticion,
 ncf.subtipo, ncf.descripcion , ncf.direccion, ncf.estado,ncf.contrata,ncf.ejecutivo_telefonica,car.propietario, usu.personal,ncf.nodo, ncf.ncfe
 from nc_fija as ncf left join cartera as car on car.id_cartera=ncf.id_cartera left join usuario as usu on usu.id_usuario=ncf.id_usuario
where month(ncf.fecha_liquidacion)='$periodo' and year(ncf.fecha_liquidacion)='$ano' and ncf.estado=
'$ncestado' and ncf.validacion='PROCEDE'   and ncf.zonal='$tienda'
ORDER BY ncf.fecha_ingreso DESC";
        } else {
            if ($ncestado === 'CANCELADO') {
                $sqlncFija = " SELECT ncf.id_cargo,ncf.fecha_ingreso, ncf.ruc,ncf.razon_social,ncf.peticion,
 ncf.subtipo, ncf.descripcion ,  ncf.direccion, ncf.estado,ncf.contrata,ncf.ejecutivo_telefonica,car.propietario, usu.personal,ncf.nodo, ncf.ncfe
 from nc_fija as ncf left join cartera as car on car.id_cartera=ncf.id_cartera left join usuario as usu on usu.id_usuario=ncf.id_usuario
where month(ncf.fecha_validacion)='$periodo' and year(ncf.fecha_validacion)='$ano' and ncf.estado=
'$ncestado' and ncf.validacion='PROCEDE'  and ncf.zonal='$tienda'
ORDER BY ncf.fecha_ingreso DESC";
            } else {
                $sqlncFija = " SELECT ncf.id_cargo,ncf.fecha_ingreso, ncf.ruc,ncf.razon_social,ncf.peticion,
 ncf.subtipo, ncf.descripcion ,  ncf.direccion, ncf.estado,ncf.contrata,ncf.ejecutivo_telefonica,car.propietario, usu.personal,ncf.nodo, ncf.ncfe
 from nc_fija as ncf left join cartera as car on car.id_cartera=ncf.id_cartera left join usuario as usu on usu.id_usuario=ncf.id_usuario
where  year(ncf.fecha_validacion)<='$ano' and 
ncf.estado in ('OBSERVADO','PENDIENTE','XINGRESAR','AGENDADO','INICIADO','BOLSA', 'ARBITRAJE') and ncf.validacion='PROCEDE'  and ncf.zonal='$tienda'


UNION 

SELECT ncf.id_cargo,ncf.fecha_ingreso, ncf.ruc,ncf.razon_social,ncf.peticion,
 ncf.subtipo, ncf.descripcion ,  ncf.direccion, ncf.estado,ncf.contrata,ncf.ejecutivo_telefonica,car.propietario, usu.personal,ncf.nodo, ncf.ncfe
 from nc_fija as ncf left join cartera as car on car.id_cartera=ncf.id_cartera left join usuario as usu on usu.id_usuario=ncf.id_usuario
where month(ncf.fecha_liquidacion)='$periodo' and year(ncf.fecha_liquidacion)='$ano' and ncf.estado=
'COMPLETADO' and ncf.validacion='PROCEDE'  and ncf.zonal='$tienda'

UNION 

SELECT ncf.id_cargo,ncf.fecha_ingreso, ncf.ruc,ncf.razon_social,ncf.peticion,
 ncf.subtipo, ncf.descripcion ,  ncf.direccion, ncf.estado,ncf.contrata,ncf.ejecutivo_telefonica,car.propietario, usu.personal,ncf.nodo, ncf.ncfe
 from nc_fija as ncf left join cartera as car on car.id_cartera=ncf.id_cartera left join usuario as usu on usu.id_usuario=ncf.id_usuario
where month(ncf.fecha_validacion)='$periodo' and year(ncf.fecha_validacion)='$ano' and ncf.estado=
'CANCELADO' and ncf.validacion='PROCEDE'  and ncf.zonal='$tienda'


";
            }
        }
    }
}

$resultncFija = mysqli_query($conexion, $sqlncFija);

?>

<form class="form form-horizontal">
    <div class="panel panel-primary">
        <div class="panel-body">
            <div>
                <table class="table table-hover table-condensed table-bordered" id="iddatatable">
                    <thead style="background-color: #dc3545;color: white; font-weight: bold;">
                        <tr>
                            <th>INGRESO</th>
                            <th>RUC</th>
                            <th>RAZON SOCIAL</th>
                            <th>PETICION</th>
                            <th>SUBTIPO</th>
                            <th>DESCRIPCION</th>


                            <th>DIRECCION</th>
                            <th>ESTADO</th>
                            <th>CONTRATA</th>
                            <th>EJECUTIVO TDP</th>
                            <th>EJECUTIVO REAL</th>
                            <th>NODO</th>
                            <th>DETALLE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        while ($mostrar = mysqli_fetch_array($resultncFija)) { ?>
                            <tr>
                                <td><?php echo $mostrar[1] ?></td>
                                <td><?php echo $mostrar[2] ?></td>
                                <td><?php echo $mostrar[3] ?></td>
                                <td><?php echo $mostrar[4] ?></td>
                                <td><?php echo $mostrar[5] ?></td>
                                <td><?php echo $mostrar[6] ?></td>
                                <td><?php echo $mostrar[7] ?></td>
                                <td><?php echo $mostrar[8] ?></td>
                                <td><?php echo $mostrar[9] ?></td>
                                <td><?php echo $mostrar[11] ?></td>
                                <td><?php echo $mostrar[12] ?></td>
                                <td><?php echo $mostrar[13] ?></td>



                                <td style="text-align: center;">
                                    <span class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalEditar" onclick="TraerDatosTabla('<?php echo
                                        $mostrar[0]
                                    ?>')"> <span class="fa fa-check-circle"></span>
                                    </span>
                                </td>


                            </tr>
                        <?php }
                        ?>


                    </tbody>

                </table>
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function() {
        $('#iddatatable').DataTable();
    });
</script>
