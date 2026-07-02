<?php

require_once "../capa_conexion/conexion.php";

$obj = new conectar();
$conexion = $obj->conexion();

$ano=$_GET["ano"];
$periodo=$_GET["periodo"];
$validacionf=$_GET["validacionf"];
$tienda=$_GET["tienda"];

if ($validacionf==='PENDIENTE') {
    $sqlncFija = " SELECT ncf.id_cargo,ncf.fecha_ingreso, ncf.ruc,ncf.razon_social,ncf.modalidad,
 ncf.subtipo, ncf.descripcion , ncf.cargo_fijo, ncf.estado,ncf.validacion
 from nc_fija as ncf 
where  year(ncf.fecha_ingreso)<='$ano' 
and ncf.validacion='$validacionf' and ncf.zonal='$tienda'
ORDER BY ncf.fecha_ingreso DESC";
}else{

if ($validacionf==='PROCEDE') {

 $sqlncFija = " SELECT ncf.id_cargo,ncf.fecha_ingreso, ncf.ruc,ncf.razon_social,ncf.modalidad,
 ncf.subtipo, ncf.descripcion , ncf.cargo_fijo, ncf.estado,ncf.validacion
 from nc_fija as ncf 
where month(ncf.fecha_validacion)='$periodo' and year(ncf.fecha_validacion)='$ano' 
and ncf.validacion='$validacionf' and ncf.zonal='$tienda'
ORDER BY ncf.fecha_ingreso DESC";


}else{

    if ($validacionf==='RECHAZADO') {
        
 $sqlncFija = " SELECT ncf.id_cargo,ncf.fecha_ingreso, ncf.ruc,ncf.razon_social,ncf.modalidad,
 ncf.subtipo, ncf.descripcion , ncf.cargo_fijo, ncf.estado,ncf.validacion
 from nc_fija as ncf 
where month(ncf.fecha_ingreso)='$periodo' and year(ncf.fecha_ingreso)='$ano' 
and ncf.validacion='$validacionf'  and ncf.zonal='$tienda'
ORDER BY ncf.fecha_ingreso DESC";


            }else{

 $sqlncFija = " SELECT ncf.id_cargo,ncf.fecha_ingreso, ncf.ruc,ncf.razon_social,ncf.modalidad,
 ncf.subtipo, ncf.descripcion , ncf.cargo_fijo, ncf.estado,ncf.validacion
 from nc_fija as ncf 
where  year(ncf.fecha_ingreso)<='$ano' 
and ncf.validacion='PENDIENTE'  and ncf.zonal='$tienda'

UNION

SELECT ncf.id_cargo,ncf.fecha_ingreso, ncf.ruc,ncf.razon_social,ncf.modalidad,
ncf.subtipo, ncf.descripcion , ncf.cargo_fijo, ncf.estado,ncf.validacion
from nc_fija as ncf 
where month(ncf.fecha_validacion)='$periodo' and year(ncf.fecha_validacion)='$ano' 
and ncf.validacion='PROCEDE' and ncf.zonal='$tienda'

UNION

SELECT ncf.id_cargo,ncf.fecha_ingreso, ncf.ruc,ncf.razon_social,ncf.modalidad,
ncf.subtipo, ncf.descripcion , ncf.cargo_fijo, ncf.estado,ncf.validacion
from nc_fija as ncf 
where month(ncf.fecha_ingreso)='$periodo' and year(ncf.fecha_ingreso)='$ano' 
and ncf.validacion='RECHAZADO' and ncf.zonal='$tienda'

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
                    <thead style="background-color: #dc3545;color: white; font-weight: bold;">
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
    $(document).ready(function () {
        $('#iddatatable').DataTable();
    });
</script>