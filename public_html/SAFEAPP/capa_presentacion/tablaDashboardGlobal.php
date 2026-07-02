<?php
session_start();
$idusu = $_SESSION ["idusu"];
require_once "../capa_conexion/conexion.php";

$obj = new conectar();
$conexion = $obj->conexion();

$ano=$_GET["ano"];
$periodo=$_GET["periodo"];

$sqldatos = "SELECT us.personal,sup.nombre,
CASE WHEN us.id_usuario=AVANZADO.usu THEN ROUND(AVANZADO.suma,2) ELSE 0 END as 'AVANZADO' ,
CASE WHEN us.id_usuario=FIJA.usu THEN ROUND(FIJA.suma,2) ELSE 0 END as 'FIJA' ,
CASE WHEN us.id_usuario=MOVIL.usu THEN ROUND(MOVIL.suma,2) ELSE 0 END as 'MOVIL' ,
us.id_usuario

FROM usuario us 

left join 
(SELECT id_usuario as usu ,SUM(cargo_fijo) as suma from nc_avanzado as ncp WHERE estado='LIQUIDADO' and year(ncp.fecha_liquidacion)='$ano' and month(ncp.fecha_liquidacion)='$periodo' and ncp.validacion='PROCEDE' GROUP BY ncp.id_usuario) as AVANZADO on AVANZADO.usu=us.id_usuario
left join 
(SELECT id_usuario as usu ,SUM(cargo_fijo) as suma from nc_fija as ncp WHERE estado='LIQUIDADO' and year(ncp.fecha_liquidacion)='$ano' and month(ncp.fecha_liquidacion)='$periodo' and ncp.validacion='PROCEDE' GROUP BY ncp.id_usuario) as FIJA on FIJA.usu=us.id_usuario

left join 
(SELECT id_usuario as usu ,SUM(cargo_fijo) as suma from nc_movil as ncp WHERE estado='ACTIVADO' and month(ncp.fecha_actualizacion)='$periodo' and year(ncp.fecha_actualizacion)='$ano' and ncp.validacion='PROCEDE' GROUP BY ncp.id_usuario) as MOVIL on MOVIL.usu=us.id_usuario

left join supervisor sup on sup.id_supervisor=us.id_supervisor
GROUP BY us.id_usuario";

$resultdatos = mysqli_query($conexion, $sqldatos);

?>
<form class="form form-horizontal"  >
    <div class="panel panel-primary">
        <div class="panel-body">
            <div>
                <table class="table table-hover table-condensed table-bordered" id="iddatatable">
                    <thead style="background-color: #478dbc;color: white; font-weight: bold;">
                        <tr>
                            <th>SUPERVISOR</th>
                            <th>USUARIO</th>
                            
                            <th>LIQ AVANZADO</th>
                            <th>LIQ FIJA</th>
                            <th>LIQ MOVIL</th>
                         
                            <th>TOTAL</th>
                   
                   

                        </tr>
                    </thead>

                    <tbody >
                        <?php

                        

                        while ($mostrar = mysqli_fetch_array($resultdatos)) {
                            
                        $total=  $mostrar[2] +$mostrar[3]+$mostrar[4] ;
                        if ( $total == 0) {
                            +1;
                        }else{
                            ?>
                            <tr >
                                <td><?php echo $mostrar[1] ?></td>

                                <td >
                         <?php echo $mostrar[0] ?>
                                 
                                </td>    
                                <td><?php echo $mostrar[2] ?></td>
                                <td><?php echo $mostrar[3] ?></td>
                                <td><?php echo $mostrar[4] ?></td>
                                
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