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
CASE WHEN nc.id_usuario=INSERTADO.usu THEN ROUND(INSERTADO.suma,2) ELSE 0 END as 'INSERTADO',
CASE WHEN nc.id_usuario=EMITIDO.usu THEN ROUND(EMITIDO.suma,2) ELSE 0 END as 'EMITIDO',
CASE WHEN nc.id_usuario=OBSERVADO.usu THEN ROUND(OBSERVADO.suma,2) ELSE 0 END as 'OBSERVADO',
CASE WHEN nc.id_usuario=INSTALADO.usu THEN ROUND(INSTALADO.suma,2) ELSE 0 END as 'INSTALADO',
CASE WHEN nc.id_usuario=LIQUIDADO.usu THEN ROUND(LIQUIDADO.suma,2) ELSE 0 END as 'LIQUIDADO',
CASE WHEN nc.id_usuario=CAIDA.usu THEN ROUND(CAIDA.suma,2) ELSE 0 END as 'CAIDA',

nc.id_usuario
FROM nc_avanzado nc left join (SELECT id_usuario as usu ,SUM(cargo_fijo) as suma FROM nc_avanzado as ncp WHERE estado='PENDIENTE' and year(ncp.fecha_validacion)<='$ano' and month(ncp.fecha_validacion)<='$periodo'  and ncp.validacion='PROCEDE' GROUP BY ncp.id_usuario) as PENDIENTE on PENDIENTE.usu=nc.id_usuario
left join 
(SELECT id_usuario as usu ,SUM(cargo_fijo) as suma from nc_avanzado as ncp WHERE estado='INSERTADO' and year(ncp.fecha_validacion)<='$ano' and month(ncp.fecha_validacion)<='$periodo'  and ncp.validacion='PROCEDE' GROUP BY ncp.id_usuario) as INSERTADO on INSERTADO.usu=nc.id_usuario
left join 
(SELECT id_usuario as usu ,SUM(cargo_fijo) as suma from nc_avanzado as ncp WHERE estado='EMITIDO' and year(ncp.fecha_validacion)<='$ano' and  month(ncp.fecha_validacion)<='$periodo' and  ncp.validacion='PROCEDE' GROUP BY ncp.id_usuario) as EMITIDO on EMITIDO.usu=nc.id_usuario
left join 
(SELECT id_usuario as usu ,SUM(cargo_fijo) as suma from nc_avanzado as ncp WHERE estado='OBSERVADO' and year(ncp.fecha_validacion)<='$ano' and  month(ncp.fecha_validacion)<='$periodo' and ncp.validacion='PROCEDE' GROUP BY ncp.id_usuario) as OBSERVADO on OBSERVADO.usu=nc.id_usuario
left join 
(SELECT id_usuario as usu ,SUM(cargo_fijo) as suma from nc_avanzado as ncp WHERE estado='INSTALADO'  and year(ncp.fecha_validacion)<='$ano' and  month(ncp.fecha_validacion)<='$periodo'  and ncp.validacion='PROCEDE' GROUP BY ncp.id_usuario) as INSTALADO on INSTALADO.usu=nc.id_usuario
left join 
(SELECT id_usuario as usu ,SUM(cargo_fijo) as suma from nc_avanzado as ncp WHERE estado='LIQUIDADO' and year(ncp.fecha_liquidacion)='$ano' and month(ncp.fecha_liquidacion)='$periodo' and ncp.validacion='PROCEDE' GROUP BY ncp.id_usuario) as LIQUIDADO on LIQUIDADO.usu=nc.id_usuario

left join 
(SELECT id_usuario as usu ,SUM(cargo_fijo) as suma from nc_avanzado as ncp WHERE estado='CAIDO' and month(ncp.fecha_validacion)='$periodo' and year(ncp.fecha_validacion)='$ano' and ncp.validacion='PROCEDE' GROUP BY ncp.id_usuario) as CAIDA on CAIDA.usu=nc.id_usuario

left join usuario u on u.id_usuario = nc.id_usuario
GROUP BY nc.id_usuario";

$resultdatos = mysqli_query($conexion, $sqldatos);

?>
<form class="form form-horizontal"  >
    <div class="panel panel-primary">
        <div class="panel-body">
            <div>
                <table class="table table-hover table-condensed table-bordered" id="iddatatable">
                    <thead style="background-color: #615ca8; color: white; font-weight: bold;">
                        <tr>
                            <th>USUARIO</th>
                            <th>PENDIENTE</th>
                            <th>INSERTADO</th>
                            <th>EMITIDO</th>
                            <th>OBSERVADO</th>
                            <th>INSTALADO</th>
                            <th>LIQUIDADO</th>

                            <th>CAIDA</th>
                        
                            
                            <th>TOTAL</th>
                   
                   

                        </tr>
                    </thead>

                    <tbody >
                        <?php

                        

                        while ($mostrar = mysqli_fetch_array($resultdatos)) {
                            
                        $total=  $mostrar[1] + $mostrar[2] +$mostrar[3]+$mostrar[4] + $mostrar[5] + $mostrar[6] +$mostrar[7] ;
                        if ( $total == 0) {
                            +1;
                        }else{
                            ?>
                            <tr >
                                
                                <td >
                            <a href="../procesos/repexportardashboardAvz.php?idusu=<?php echo $mostrar[8] ?>&ano=<?php echo $ano ?>&periodo=<?php echo $periodo ?>"><?php echo $mostrar[0] ?></a> 
                                 
                                </td>    
                                <td><?php echo $mostrar[1] ?></td>
                                <td><?php echo $mostrar[2] ?></td>
                                <td><?php echo $mostrar[3] ?></td>
                                <td><?php echo $mostrar[4] ?></td>
                                <td><?php echo $mostrar[5] ?></td>
                                <td><?php echo $mostrar[6] ?></td>
                                <td><?php echo $mostrar[7] ?></td>
                               
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