<?php

require_once "../capa_conexion/conexion.php";

$obj = new conectar();
$conexion = $obj->conexion();

$ano = $_GET["ano"];
$periodo = $_GET["periodo"];
$ncestado = $_GET["ncestadof"];
$tienda = $_GET["tienda"];

if ($ncestado === 'LIQUIDADO') {

    $sqlncMovil = " SELECT  ncf.id_cargo,
                            ncf.fecha_actualizacion, 
                            ncf.ruc,
                            ncf.razon_social,
                            ncf.modalidad,
                            ncf.cargo_fijo, 
                            ncf.estado,
                            ncf.validacion,
                            ncf.oportunidad,
                            ncf.ejecutivo_telefonica,
                            q_lineas,
                            car.propietario, 
                            usu.personal,
                            ncf.nodo, 
                            ncf.casofwa,
                            ncf.comision,
                            ncf.observacion,
                            ncf.descuento,
                            ncf.fecha_liquidacion
                            from nc_movil as ncf 
                            left join cartera as car on car.id_cartera=ncf.id_cartera 
                            left join usuario as usu on usu.id_usuario=ncf.id_usuario
                            where ncf.estado='$ncestado' 
                            and year(ncf.fecha_actualizacion)='$ano'
                            and month(ncf.fecha_actualizacion)='$periodo'
                            and ncf.validacion='PROCEDE' 
                            ORDER BY ncf.fecha_actualizacion DESC";
} else {

    if ($ncestado === 'DEUDA') {
        $sqlncMovil = " SELECT  ncf.id_cargo,
                                ncf.fecha_actualizacion, 
                                ncf.ruc,
                                ncf.razon_social,
                                ncf.modalidad,
                                ncf.cargo_fijo, 
                                ncf.estado,
                                ncf.validacion,
                                ncf.oportunidad,
                                ncf.ejecutivo_telefonica,
                                q_lineas,
                                car.propietario, 
                                usu.personal,
                                ncf.nodo, 
                                ncf.casofwa,
                                ncf.comision,
                                ncf.observacion,
                                ncf.descuento,
                                ncf.fecha_liquidacion
                                from nc_movil as ncf 
                                left join cartera as car on car.id_cartera=ncf.id_cartera 
                                left join usuario as usu on usu.id_usuario=ncf.id_usuario
                                where ncf.estado='$ncestado'
                                and year(ncf.fecha_actualizacion)='$ano'
                                and month(ncf.fecha_actualizacion)='$periodo' 
                                and ncf.validacion='PROCEDE' 
                                ORDER BY ncf.fecha_actualizacion DESC";
    } else {

        if ($ncestado === 'BAJA') {
            $sqlncMovil = " SELECT  ncf.id_cargo,
                                ncf.fecha_actualizacion, 
                                ncf.ruc,
                                ncf.razon_social,
                                ncf.modalidad,
                                ncf.cargo_fijo, 
                                ncf.estado,
                                ncf.validacion,
                                ncf.oportunidad,
                                ncf.ejecutivo_telefonica,
                                q_lineas,
                                car.propietario, 
                                usu.personal,
                                ncf.nodo, 
                                ncf.casofwa,
                                ncf.comision,
                                ncf.observacion,
                                ncf.descuento,
                                ncf.fecha_liquidacion
                                from nc_movil as ncf 
                                left join cartera as car on car.id_cartera=ncf.id_cartera 
                                left join usuario as usu on usu.id_usuario=ncf.id_usuario
                                where ncf.estado='$ncestado'
                                and year(ncf.fecha_actualizacion)='$ano'
                                and month(ncf.fecha_actualizacion)='$periodo'
                                and ncf.validacion='PROCEDE' 
                                ORDER BY ncf.fecha_actualizacion DESC";
        } else {
            if ($ncestado === 'BAJA PARCIAL') {
                $sqlncMovil = " SELECT  ncf.id_cargo,
                                ncf.fecha_actualizacion, 
                                ncf.ruc,
                                ncf.razon_social,
                                ncf.modalidad,
                                ncf.cargo_fijo, 
                                ncf.estado,
                                ncf.validacion,
                                ncf.oportunidad,
                                ncf.ejecutivo_telefonica,
                                q_lineas,
                                car.propietario, 
                                usu.personal,
                                ncf.nodo, 
                                ncf.casofwa,
                                ncf.comision,
                                ncf.observacion,
                                ncf.descuento,
                                ncf.fecha_liquidacion
                                from nc_movil as ncf 
                                left join cartera as car on car.id_cartera=ncf.id_cartera 
                                left join usuario as usu on usu.id_usuario=ncf.id_usuario
                                where ncf.estado='$ncestado' 
                                and year(ncf.fecha_actualizacion)='$ano'
                                and month(ncf.fecha_actualizacion)='$periodo'
                                and ncf.validacion='PROCEDE' 
                                ORDER BY ncf.fecha_actualizacion DESC";
            } else {
                if ($ncestado === 'PORT OUT') {
                    $sqlncMovil = " SELECT  ncf.id_cargo,
                                ncf.fecha_actualizacion, 
                                ncf.ruc,
                                ncf.razon_social,
                                ncf.modalidad,
                                ncf.cargo_fijo, 
                                ncf.estado,
                                ncf.validacion,
                                ncf.oportunidad,
                                ncf.ejecutivo_telefonica,
                                q_lineas,
                                car.propietario, 
                                usu.personal,
                                ncf.nodo, 
                                ncf.casofwa,
                                ncf.comision,
                                ncf.observacion,
                                ncf.descuento,
                                ncf.fecha_liquidacion
                                from nc_movil as ncf 
                                left join cartera as car on car.id_cartera=ncf.id_cartera 
                                left join usuario as usu on usu.id_usuario=ncf.id_usuario
                                where ncf.estado='$ncestado' 
                                and year(ncf.fecha_actualizacion)='$ano'
                                and month(ncf.fecha_actualizacion)='$periodo'
                                and ncf.validacion='PROCEDE' 
                                ORDER BY ncf.fecha_actualizacion DESC";
                } else {
                    if ($ncestado === 'SUSPENSION') {
                        $sqlncMovil = " SELECT  ncf.id_cargo,
                                ncf.fecha_actualizacion, 
                                ncf.ruc,
                                ncf.razon_social,
                                ncf.modalidad,
                                ncf.cargo_fijo, 
                                ncf.estado,
                                ncf.validacion,
                                ncf.oportunidad,
                                ncf.ejecutivo_telefonica,
                                q_lineas,
                                car.propietario, 
                                usu.personal,
                                ncf.nodo, 
                                ncf.casofwa,
                                ncf.comision,
                                ncf.observacion,
                                ncf.descuento,
                                ncf.fecha_liquidacion
                                from nc_movil as ncf 
                                left join cartera as car on car.id_cartera=ncf.id_cartera 
                                left join usuario as usu on usu.id_usuario=ncf.id_usuario
                                where ncf.estado='$ncestado' 
                                and year(ncf.fecha_actualizacion)='$ano'
                                and month(ncf.fecha_actualizacion)='$periodo'
                                and ncf.validacion='PROCEDE' 
                                ORDER BY ncf.fecha_actualizacion DESC";
                    } else {
                        if ($ncestado === 'ACUERDO COMERCIAL') {
                            $sqlncMovil = " SELECT  ncf.id_cargo,
                                ncf.fecha_actualizacion, 
                                ncf.ruc,
                                ncf.razon_social,
                                ncf.modalidad,
                                ncf.cargo_fijo, 
                                ncf.estado,
                                ncf.validacion,
                                ncf.oportunidad,
                                ncf.ejecutivo_telefonica,
                                q_lineas,
                                car.propietario, 
                                usu.personal,
                                ncf.nodo, 
                                ncf.casofwa,
                                ncf.comision,
                                ncf.observacion,
                                ncf.descuento,
                                ncf.fecha_liquidacion
                                from nc_movil as ncf 
                                left join cartera as car on car.id_cartera=ncf.id_cartera 
                                left join usuario as usu on usu.id_usuario=ncf.id_usuario
                                where ncf.estado='$ncestado' 
                                and year(ncf.fecha_actualizacion)='$ano'
                                and month(ncf.fecha_actualizacion)='$periodo'
                                and ncf.validacion='PROCEDE' 
                                ORDER BY ncf.fecha_actualizacion DESC";
                        } else {
                            $sqlncMovil = " SELECT  ncf.id_cargo,
                                    ncf.fecha_actualizacion, 
                                    ncf.ruc,
                                    ncf.razon_social,
                                    ncf.modalidad,
                                    ncf.cargo_fijo, 
                                    ncf.estado,
                                    ncf.validacion,
                                    ncf.oportunidad,
                                    ncf.ejecutivo_telefonica,
                                    q_lineas,
                                    car.propietario, 
                                    usu.personal,
                                    ncf.nodo, 
                                    ncf.casofwa,
                                    ncf.comision,
                                    ncf.observacion,
                                    ncf.descuento,
                                    ncf.fecha_liquidacion
                                    from nc_movil as ncf 
                                    left join cartera as car on car.id_cartera=ncf.id_cartera 
                                    left join usuario as usu on usu.id_usuario=ncf.id_usuario
                                    where  ncf.estado in ('INGRESADO','PROCESANDO','PENDIENTE','DELIVERY','BOLSA','SALIDAANTICIPADA', 'LIQUIDADO') and ncf.validacion='PROCEDE' 
                                    UNION 

                            SELECT  ncf.id_cargo,
                                    ncf.fecha_actualizacion, 
                                    ncf.ruc,
                                    ncf.razon_social,
                                    ncf.modalidad,
                                    ncf.cargo_fijo, 
                                    ncf.estado,
                                    ncf.validacion,
                                    ncf.oportunidad,
                                    ncf.ejecutivo_telefonica,
                                    q_lineas,
                                    car.propietario, 
                                    usu.personal,
                                    ncf.nodo, 
                                    ncf.casofwa,
                                    ncf.comision,
                                    ncf.observacion,
                                    ncf.descuento,
                                    ncf.fecha_liquidacion
                                    from nc_movil as ncf 
                                    left join cartera as car on car.id_cartera=ncf.id_cartera 
                                    left join usuario as usu on usu.id_usuario=ncf.id_usuario
                                    where ncf.estado='ACTIVADO' 
                                    and ncf.validacion='PROCEDE' ";
                        }
                    }
                }
            }
        }
    }
}

$resultncMovil = mysqli_query($conexion, $sqlncMovil);


?>

<form class="form form-horizontal">
    <div class="panel panel-primary">
        <div class="panel-body">
            <div>
                <table class="table table-hover table-condensed table-bordered" id="iddatatable">
                    <thead style="background-color: #0051FF;color: white; font-weight: bold;">
                        <tr>
                            <th>FECHA ACTUALIZACIÓN</th>
                            <th>RUC</th>
                            <th>RAZON SOCIAL</th>
                            <th>MODALIDAD</th>
                            <th>Q_LINEAS</th>
                            <th>CARGO FIJO</th>
                            <th>OPORTUNIDAD</th>
                            <th>EJECUTIVO TDP</th>
                            <th>EJECUTIVO REAL</th>
                            <th>NODO</th>
                            <th>ESTADO</th>
                            <th>OBSERVACIÓN</th>
                            <th>DETALLE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($mostrar = mysqli_fetch_array($resultncMovil)) {
                        ?>
                            <tr>
                                <td><?php echo $mostrar[1] ?></td>
                                <td><?php echo $mostrar[2] ?></td>
                                <td><?php echo $mostrar[3] ?></td>
                                <td><?php echo $mostrar[4] ?></td>
                                <td><?php echo $mostrar[10] ?></td>
                                <td><?php echo $mostrar[5] ?></td>
                                <td><?php echo $mostrar[8] ?></td>
                                <td><?php echo $mostrar[11] ?></td>
                                <td><?php echo $mostrar[12] ?></td>
                                <td><?php echo $mostrar[13] ?></td>
                                <td><?php echo $mostrar[6] ?></td>
                                <td><?php echo $mostrar[16] ?></td>
                                <td style="text-align: center;">
                                    <span class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalEditar" onclick="TraerDatosTabla('<?php echo $mostrar[0] ?>')"> <span class="fa fa-check-circle"></span>
                                    </span>
                                </td>
                            </tr>
                        <?php
                        }
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