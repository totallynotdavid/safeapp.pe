<?php

require_once "../capa_conexion/conexion.php";

$obj = new conectar();
$conexion = $obj->conexion();

$ano=$_GET["ano"];
$periodo=$_GET["periodo"];
$estado=$_GET["estado"];
$idusu=$_GET["idusu"];

if ($estado==='PENDIENTE' || $estado==='INSERTADO' || $estado==='EMITIDO' || $estado==='OBSERVADO' || 
    $estado==='INSTALADO' || $estado==='AGENDADO') {

    $sqlncFija = " SELECT ncf.id_cargo,ncf.fecha_ingreso, ncf.ruc,ncf.razon_social,ncf.modalidad,
 ncf.subtipo, ncf.descripcion , ncf.cargo_fijo, ncf.estado,ncf.validacion,ncf.back
 from nc_avanzado as ncf 
where   year(ncf.fecha_ingreso)<='$ano' and 
ncf.id_usuario='$idusu' and ncf.estado='$estado'
ORDER BY ncf.fecha_ingreso DESC";
}else{

if ($estado==='LIQUIDADO') {
 $sqlncFija = " SELECT ncf.id_cargo,ncf.fecha_ingreso, ncf.ruc,ncf.razon_social,ncf.modalidad,
 ncf.subtipo, ncf.descripcion , ncf.cargo_fijo, ncf.estado,ncf.validacion,ncf.back
 from nc_avanzado as ncf 
where month(ncf.fecha_liquidacion)='$periodo' and year(ncf.fecha_liquidacion)='$ano' and 
ncf.id_usuario='$idusu' and ncf.estado='$estado'
ORDER BY ncf.fecha_ingreso DESC";

  }else{

if ($estado==='CAIDO') {
   
    $sqlncFija = " SELECT ncf.id_cargo,ncf.fecha_ingreso, ncf.ruc,ncf.razon_social,ncf.modalidad,
 ncf.subtipo, ncf.descripcion , ncf.cargo_fijo, ncf.estado,ncf.validacion,ncf.back
 from nc_avanzado as ncf 
where month(ncf.fecha_ingreso)='$periodo' and year(ncf.fecha_ingreso)='$ano' and 
ncf.id_usuario='$idusu' and ncf.estado='$estado'
ORDER BY ncf.fecha_ingreso DESC"; 
} else{

$sqlncFija = " SELECT ncf.id_cargo,ncf.fecha_ingreso, ncf.ruc,ncf.razon_social,ncf.modalidad,
 ncf.subtipo, ncf.descripcion , ncf.cargo_fijo, ncf.estado,ncf.validacion,ncf.back
 from nc_avanzado as ncf 
where  year(ncf.fecha_ingreso)<='$ano' and 
ncf.id_usuario='$idusu' and ncf.estado in ('PENDIENTE','INSERTADO','EMITIDO','OBSERVADO','INSTALADO', 'AGENDADO') 

UNION 

SELECT ncf.id_cargo,ncf.fecha_ingreso, ncf.ruc,ncf.razon_social,ncf.modalidad,
 ncf.subtipo, ncf.descripcion , ncf.cargo_fijo, ncf.estado,ncf.validacion,ncf.back
 from nc_avanzado as ncf 
where month(ncf.fecha_liquidacion)='$periodo' and year(ncf.fecha_liquidacion)='$ano' and 
ncf.id_usuario='$idusu' and ncf.estado='LIQUIDADO'

UNION 

SELECT ncf.id_cargo,ncf.fecha_ingreso, ncf.ruc,ncf.razon_social,ncf.modalidad,
 ncf.subtipo, ncf.descripcion , ncf.cargo_fijo, ncf.estado,ncf.validacion,ncf.back
 from nc_avanzado as ncf 
where month(ncf.fecha_ingreso)='$periodo' and year(ncf.fecha_ingreso)='$ano' and 
ncf.id_usuario='$idusu' and ncf.estado='CAIDO'
";
}

}


}


$resultncFija = mysqli_query($conexion, $sqlncFija);


?>

<form class="form form-horizontal" >
    <div class="panel panel-primary">
        <div class="panel-body">
            <div>
                       <table class="table table-hover table-condensed table-bordered" id="iddatatable">
                    <thead style="background-color: #615ca8;color: white; font-weight: bold;">
                        <tr>
                            <th>INGRESO</th>
                            <th>RUC</th>
                            <th>RAZON SOCIAL</th>
                            <th>MODALIDAD</th>
                            <th>SUBTIPO</th>
                            <th>DESCRIPCION</th>
                            <th>CARGO FIJO</th>
                            <th>ESTADO</th>
                            <th>VALIDADO</th>
                            <th>BACK</th>
                            <th>DETALLE</th>
                            
                            
                        </tr>
                    </thead>
                     <tbody >
                       <?php
                        while ($mostrar = mysqli_fetch_array($resultncFija)) {
                            ?>
                            <tr >
                                <td><?php echo $mostrar[1] ?></td>
                                <td><?php echo $mostrar[2] ?></td>
                                <td><?php echo $mostrar[3] ?></td>
                                <td><?php echo $mostrar[4] ?></td>
                                <td><?php echo $mostrar[5] ?></td>
                                <td><?php echo $mostrar[6] ?></td>
                                <td><?php echo $mostrar[7] ?></td>
                                <td><?php echo $mostrar[8] ?></td>
                                <td><?php echo $mostrar[9] ?></td>
                                <td><?php echo $mostrar[10] ?></td>
<td style="text-align: center;">
    <span class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalSeguimiento" onclick="TraerDatosTabla('<?php echo $mostrar[0] ?>')"> <span class="fa fa-check-circle"></span>
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
    $(document).ready(function () {
        $('#iddatatable').DataTable();
    });
</script>