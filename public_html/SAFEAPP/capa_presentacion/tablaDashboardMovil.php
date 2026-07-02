<?php
session_start();
$idusu = $_SESSION ["idusu"];
require_once "../capa_conexion/conexion.php";

$obj = new conectar();
$conexion = $obj->conexion();

$ano=$_GET["ano"];
$periodo=$_GET["periodo"];

$sqldatos = "SELECT u.personal,
CASE WHEN nc.id_usuario=PENDIENTE.usu THEN ROUND(PENDIENTE.suma,2) ELSE 0 END as 'PENDIENTE' ,
CASE WHEN nc.id_usuario=PROCESANDO.usu THEN ROUND(PROCESANDO.suma,2) ELSE 0 END as 'PROCESANDO',
CASE WHEN nc.id_usuario=INGRESADO.usu THEN ROUND(INGRESADO.suma,2) ELSE 0 END as 'INGRESADO',
CASE WHEN nc.id_usuario=DELIVERY.usu THEN ROUND(DELIVERY.suma,2) ELSE 0 END as 'DELIVERY',
CASE WHEN nc.id_usuario=ACTIVADO.usu THEN ROUND(ACTIVADO.suma,2) ELSE 0 END as 'ACTIVADO',
CASE WHEN nc.id_usuario=CAIDA.usu THEN ROUND(CAIDA.suma,2) ELSE 0 END as 'CAIDA',
CASE WHEN nc.id_usuario=BOLSA.usu THEN ROUND(BOLSA.suma,2) ELSE 0 END as 'BOLSA',
CASE WHEN nc.id_usuario=SALIDAANTICIPADA.usu THEN ROUND(SALIDAANTICIPADA.suma,2) ELSE 0 END as 'SALIDAANTICIPADA',
nc.id_usuario
FROM nc_movil nc left join (SELECT id_usuario as usu ,SUM(cargo_fijo) as suma FROM nc_movil as ncp WHERE estado='PENDIENTE' and year(ncp.fecha_ingreso)<='$ano' and month(ncp.fecha_ingreso)<='$periodo' and ncp.validacion='PROCEDE' GROUP BY ncp.id_usuario) as PENDIENTE on PENDIENTE.usu=nc.id_usuario
left join 
(SELECT id_usuario as usu ,SUM(cargo_fijo) as suma from nc_movil as ncp WHERE estado='PROCESANDO' 
    and year(ncp.fecha_ingreso)<='$ano' and month(ncp.fecha_ingreso)<='$periodo' and ncp.validacion='PROCEDE' GROUP BY ncp.id_usuario) as PROCESANDO on PROCESANDO.usu=nc.id_usuario
left join 
(SELECT id_usuario as usu ,SUM(cargo_fijo) as suma from nc_movil as ncp WHERE estado='INGRESADO' and year(ncp.fecha_ingreso)<='$ano' and month(ncp.fecha_ingreso)<='$periodo' and ncp.validacion='PROCEDE' GROUP BY ncp.id_usuario) as INGRESADO on INGRESADO.usu=nc.id_usuario
left join 
(SELECT id_usuario as usu ,SUM(cargo_fijo) as suma from nc_movil as ncp WHERE estado='DELIVERY' and year(ncp.fecha_ingreso)<='$ano' and month(ncp.fecha_ingreso)<='$periodo' and ncp.validacion='PROCEDE' GROUP BY ncp.id_usuario) as DELIVERY on DELIVERY.usu=nc.id_usuario
left join 
(SELECT id_usuario as usu ,SUM(cargo_fijo) as suma from nc_movil as ncp WHERE estado='ACTIVADO' and month(ncp.fecha_actualizacion)='$periodo' and year(ncp.fecha_actualizacion)='$ano' and ncp.validacion='PROCEDE' GROUP BY ncp.id_usuario) as ACTIVADO on ACTIVADO.usu=nc.id_usuario
left join 
(SELECT id_usuario as usu ,SUM(cargo_fijo) as suma from nc_movil as ncp WHERE estado='CAIDA' and month(ncp.fecha_ingreso)='$periodo' and year(ncp.fecha_ingreso)='$ano' and ncp.validacion='PROCEDE' GROUP BY ncp.id_usuario) as CAIDA on CAIDA.usu=nc.id_usuario
left join 
(SELECT id_usuario as usu ,SUM(cargo_fijo) as suma from nc_movil as ncp WHERE estado='BOLSA' and year(ncp.fecha_ingreso)<='$ano' and  month(ncp.fecha_ingreso)<='$periodo' and ncp.validacion='PROCEDE' GROUP BY ncp.id_usuario) as BOLSA on BOLSA.usu=nc.id_usuario
left join 
(SELECT id_usuario as usu ,SUM(cargo_fijo) as suma from nc_movil as ncp WHERE estado='SALIDAANTICIPADA' and year(ncp.fecha_ingreso)<='$ano' and month(ncp.fecha_ingreso)<='$periodo' and ncp.validacion='PROCEDE' GROUP BY ncp.id_usuario) as SALIDAANTICIPADA on SALIDAANTICIPADA.usu=nc.id_usuario
left join usuario u on u.id_usuario = nc.id_usuario
GROUP BY nc.id_usuario";

$resultdatos = mysqli_query($conexion, $sqldatos);

?>
<form class="form form-horizontal"  >
    <div class="panel panel-primary">
        <div class="panel-body">
            <div>
                <table class="table table-hover table-condensed table-bordered" id="iddatatable">
                    <thead style="background-color: #00a65a;color: white; font-weight: bold;">
                        <tr>
                            <th>USUARIO</th>
                            <th>PENDIENTE</th>
                            <th>PROCESANDO</th>
                            <th>INGRESADO</th>
                            <th>DELIVERY</th>
                            <th>ACTIVADO</th>
                            <th>CAIDA</th>
                            <th>BOLSA</th>
                            <th>SALIDA ANTICIPADA</th>
                            <th>TOTAL</th>
                   
                   

                        </tr>
                    </thead>

                    <tbody >
                        <?php

                        

                        while ($mostrar = mysqli_fetch_array($resultdatos)) {
                            
                        $total=  $mostrar[1] + $mostrar[2] +$mostrar[3]+$mostrar[4] + $mostrar[5] + $mostrar[6] +$mostrar[7] + $mostrar[8];
                        if ( $total == 0) {
                            +1;
                        }else{
                            ?>
                            <tr >
                                
                                <td >
                            <a href="../procesos/repexportardashboardMovil.php?idusu=<?php echo $mostrar[9] ?>&ano=<?php echo $ano ?>&periodo=<?php echo $periodo ?>"><?php echo $mostrar[0] ?></a> 
                                 
                                </td>    
                                <td><?php echo $mostrar[1] ?></td>
                                <td><?php echo $mostrar[2] ?></td>
                                <td><?php echo $mostrar[3] ?></td>
                                <td><?php echo $mostrar[4] ?></td>
                                <td><?php echo $mostrar[5] ?></td>
                                <td><?php echo $mostrar[6] ?></td>
                                <td><?php echo $mostrar[7] ?></td>
                                <td><?php echo $mostrar[8] ?></td>
                                <td><?php echo $total ?></td>
                                
                            

                            
                            </tr>
                            <?php
                        }}
                        ?>								
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        $('#iddatatable').DataTable();
    });
</script>