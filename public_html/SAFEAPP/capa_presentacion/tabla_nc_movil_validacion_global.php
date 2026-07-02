<?php

require_once "../capa_conexion/conexion.php";

$obj = new conectar();
$conexion = $obj->conexion();

$ano=$_GET["ano"];
$periodo=$_GET["periodo"];
$validacionf=$_GET["validacionf"];
$tienda=$_GET["tienda"];


if ($validacionf==='PENDIENTE') {
    $sqlncMovil = " SELECT ncf.id_cargo,ncf.fecha_ingreso, ncf.ruc,ncf.razon_social,ncf.modalidad,
 ncf.cargo_fijo, ncf.estado,ncf.validacion,s.nombre,u.personal
 from nc_movil as ncf left join supervisor as s on ncf.id_supervisor=s.id_supervisor
 left join usuario as u on u.id_usuario=ncf.id_usuario
where  year(ncf.fecha_ingreso)<='$ano' 
and ncf.validacion='$validacionf' 
ORDER BY ncf.fecha_ingreso DESC";
}else{

if ($validacionf==='PROCEDE') {

 $sqlncMovil = " SELECT ncf.id_cargo,ncf.fecha_ingreso, ncf.ruc,ncf.razon_social,ncf.modalidad,
 ncf.cargo_fijo, ncf.estado,ncf.validacion,s.nombre,u.personal
 from nc_movil as ncf left join supervisor as s on ncf.id_supervisor=s.id_supervisor
  left join usuario as u on u.id_usuario=ncf.id_usuario
where month(ncf.fecha_validacion)='$periodo' and year(ncf.fecha_validacion)='$ano' 
and ncf.validacion='$validacionf' 
ORDER BY ncf.fecha_ingreso DESC";


}else{

    if ($validacionf==='RECHAZADO') {
        
 $sqlncMovil = " SELECT ncf.id_cargo,ncf.fecha_ingreso, ncf.ruc,ncf.razon_social,ncf.modalidad,
 ncf.cargo_fijo, ncf.estado,ncf.validacion,s.nombre,u.personal
 from nc_movil as ncf left join supervisor as s on ncf.id_supervisor=s.id_supervisor
  left join usuario as u on u.id_usuario=ncf.id_usuario
where month(ncf.fecha_ingreso)='$periodo' and year(ncf.fecha_ingreso)='$ano' 
and ncf.validacion='$validacionf' 
ORDER BY ncf.fecha_ingreso DESC";


            }else{

 $sqlncMovil = " SELECT ncf.id_cargo,ncf.fecha_ingreso, ncf.ruc,ncf.razon_social,ncf.modalidad,
 ncf.cargo_fijo, ncf.estado,ncf.validacion,s.nombre,u.personal
 from nc_movil as ncf left join supervisor as s on ncf.id_supervisor=s.id_supervisor
  left join usuario as u on u.id_usuario=ncf.id_usuario
where year(ncf.fecha_ingreso)<='$ano' 
and ncf.validacion='PENDIENTE'  

UNION

SELECT ncf.id_cargo,ncf.fecha_ingreso, ncf.ruc,ncf.razon_social,ncf.modalidad,
ncf.cargo_fijo, ncf.estado,ncf.validacion,s.nombre,u.personal
from nc_movil as ncf left join supervisor as s on ncf.id_supervisor=s.id_supervisor
 left join usuario as u on u.id_usuario=ncf.id_usuario
where month(ncf.fecha_validacion)='$periodo' and year(ncf.fecha_validacion)='$ano' 
and ncf.validacion='PROCEDE'  

UNION

SELECT ncf.id_cargo,ncf.fecha_ingreso, ncf.ruc,ncf.razon_social,ncf.modalidad,
ncf.cargo_fijo, ncf.estado,ncf.validacion,s.nombre,u.personal
from nc_movil as ncf left join supervisor as s on ncf.id_supervisor=s.id_supervisor
 left join usuario as u on u.id_usuario=ncf.id_usuario
where month(ncf.fecha_ingreso)='$periodo' and year(ncf.fecha_ingreso)='$ano' 
and ncf.validacion='RECHAZADO' 

";


    }

}



}


$resultncMovil = mysqli_query($conexion, $sqlncMovil);


?>

<form class="form form-horizontal" >
    <div class="panel panel-primary">
        <div class="panel-body">
            <div>
                       <table class="table table-hover table-condensed table-bordered" id="iddatatable">
                    <thead style="background-color: #00a65a;color: white; font-weight: bold;">
                        <tr>
                            <th>INGRESO</th>
                            <th>RUC</th>
                            <th>RAZON SOCIAL</th>
                            <th>EJECUTIVO REAL</th>
                            
                            <th>SUPERVISOR</th>
                            <th>CARGO FIJO</th>
                            <th>ESTADO</th>
                            <th>VALIDADO</th>
                            <th>DETALLE</th>
                            
                            
                        </tr>
                    </thead>
                     <tbody >
                       <?php
                        while ($mostrar = mysqli_fetch_array($resultncMovil)) {
                            ?>
                            <tr >
                                <td><?php echo $mostrar[1] ?></td>
                                <td><?php echo $mostrar[2] ?></td>
                                <td><?php echo $mostrar[3] ?></td>
                                <td><?php echo $mostrar[9] ?></td>
                                <td><?php echo $mostrar[8] ?></td>
                                <td><?php echo $mostrar[5] ?></td>
                                <td><?php echo $mostrar[6] ?></td>
                                <td><?php echo $mostrar[7] ?></td>
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