<?php
   session_start();
$idusu = $_SESSION ["idusu"];
require_once "../capa_conexion/conexion.php";

$obj = new conectar();
$conexion = $obj->conexion();

header("Content-Type: application/xls");
header("Content-Disposition: attachment; filename= reporteseguimientoM.xls"); 

$idusu=$_GET["idusu"];
$ano=$_GET["ano"];
$periodo=$_GET["periodo"];



$sqlEXCEL = " SELECT usu.personal, sup.nombre, nc.fecha_ingreso, nc.fecha_actualizacion,nc.ruc, nc.razon_social, nc.modalidad,nc.tipo,nc.q_lineas, nc.cargo_fijo,nc.contacto, nc.telefono1, nc.telefono2, nc.comentario_ejecutivo, nc.estado, nc.validacion,nc.fecha_validacion, nc.comentario_validador,nc.ejecutivo_telefonica, t.nombre,nc.comentario_back,nc.oportunidad  ,car.nombre,car.propietario,car.DNI,nc.nodo FROM nc_movil as nc inner join usuario as usu on usu.id_usuario= nc.id_usuario left JOIN supervisor as sup on sup.id_supervisor= nc.id_supervisor LEFT JOIN tienda as t on t.id_tienda=nc.zonal_telefonica LEFT JOIN cartera as car on car.id_cartera=nc.id_cartera
where  year(nc.fecha_ingreso)<='$ano' and month(nc.fecha_ingreso)<='$periodo' and nc.estado in ('INGRESADO','PROCESANDO','PENDIENTE','DELIVERY','BOLSA','SALIDAANTICIPADA') and nc.validacion='PROCEDE' and nc.id_usuario='$idusu'
UNION 

SELECT usu.personal, sup.nombre, nc.fecha_ingreso, nc.fecha_actualizacion,nc.ruc, nc.razon_social, nc.modalidad,nc.tipo,nc.q_lineas, nc.cargo_fijo,nc.contacto, nc.telefono1, nc.telefono2, nc.comentario_ejecutivo, nc.estado, nc.validacion,nc.fecha_validacion, nc.comentario_validador,nc.ejecutivo_telefonica, t.nombre,nc.comentario_back,nc.oportunidad,car.nombre,car.propietario,car.DNI,nc.nodo FROM nc_movil as nc inner join usuario as usu on usu.id_usuario= nc.id_usuario left JOIN supervisor as sup on sup.id_supervisor= nc.id_supervisor LEFT JOIN tienda as t on t.id_tienda=nc.zonal_telefonica LEFT JOIN cartera as car on car.id_cartera=nc.id_cartera
where month(nc.fecha_actualizacion)='$periodo' and year(nc.fecha_actualizacion)='$ano' and nc.estado='ACTIVADO' and nc.validacion='PROCEDE' and nc.id_usuario='$idusu'

UNION 

SELECT usu.personal, sup.nombre, nc.fecha_ingreso, nc.fecha_actualizacion,nc.ruc, nc.razon_social, nc.modalidad,nc.tipo,nc.q_lineas, nc.cargo_fijo,nc.contacto, nc.telefono1, nc.telefono2, nc.comentario_ejecutivo, nc.estado, nc.validacion,nc.fecha_validacion, nc.comentario_validador,nc.ejecutivo_telefonica, t.nombre,nc.comentario_back,nc.oportunidad,car.nombre,car.propietario,car.DNI,nc.nodo FROM nc_movil as nc inner join usuario as usu on usu.id_usuario= nc.id_usuario left JOIN supervisor as sup  on sup.id_supervisor= nc.id_supervisor LEFT JOIN tienda as t on t.id_tienda=nc.zonal_telefonica LEFT JOIN cartera as car on car.id_cartera=nc.id_cartera
where month(nc.fecha_ingreso)='$periodo' and year(nc.fecha_ingreso)='$ano'  and nc.estado='CAIDA' and nc.validacion='PROCEDE' 
and nc.id_usuario='$idusu'
";

  


    $resultEXCEL=mysqli_query($conexion,$sqlEXCEL);

?>

<table class="table table-hover table-condensed table-bordered" id="iddatatable">
                    <thead style="background-color: #dc3545;color: blue; font-weight: bold;">
                        <tr>
                           
                            <th>PERSONAL</th> 
                            <th>SUPERVISOR</th>
                            <th>FECHA INGRESO</th>
                            <th>FECHA ULT ACTUALIZACION</th>
                            <th>RUC</th>
                            <th>RAZON SOCIAL</th>
                            <th>MODALIDAD</th>
                            <th>TIPO</th>                           
                            <th>Q_LINEAS</th>
                            <th>CARGO_FIJO</th>                            
                            <th>CONTACTO</th>
                            <th>TELEFONO 1</th>
                            <th>TELEFONO 2</th>
                            <th>COMENTARIO EJECUTIVO</th>
                            <th>ESTADO</th>
                            <th>VALIDACION</th>
                            <th>FECHA VALIDACION</th>
                            <th>COMENTARIO VALIDADOR</th>                        
                            <th>EJECUTIVO TELEFONICA</th>
                            <th>ZONAL TELEFONICA</th>
                            <th>COMENTARIO BACK</th>
                            <th>OPORTUNIDAD</th>
                            <th>CARTERA</th>
                            <th>DNI</th>
                            <th>NODO</th>


                          
                        </tr>
                    </thead>

                    <tbody >
                        <?php
                        while ($mostrar = mysqli_fetch_array($resultEXCEL)) {
                            ?>
                            <tr >
                            <td><?php echo utf8_decode($mostrar[0]) ?></td>
                            <td><?php echo utf8_decode($mostrar[1]) ?></td>
                            <td><?php echo utf8_decode($mostrar[2]) ?></td>
                            <td><?php echo utf8_decode($mostrar[3]) ?></td>
                            <td><?php echo utf8_decode($mostrar[4]) ?></td>
                            <td><?php echo utf8_decode($mostrar[5]) ?></td>
                            <td><?php echo utf8_decode($mostrar[6]) ?></td>
                            <td><?php echo utf8_decode($mostrar[7]) ?></td>
                            <td><?php echo utf8_decode($mostrar[8]) ?></td>
                            <td><?php echo utf8_decode($mostrar[9]) ?></td>
                            <td><?php echo utf8_decode($mostrar[10]) ?></td>
                            <td><?php echo utf8_decode($mostrar[11]) ?></td>
                            <td><?php echo utf8_decode($mostrar[12]) ?></td>
                            <td><?php echo utf8_decode($mostrar[13]) ?></td>
                            <td><?php echo utf8_decode($mostrar[14]) ?></td>
                            <td><?php echo utf8_decode($mostrar[15]) ?></td>
                            <td><?php echo utf8_decode($mostrar[16]) ?></td>
                            <td><?php echo utf8_decode($mostrar[17]) ?></td>
                            <td><?php echo utf8_decode($mostrar[23]) ?></td>
                            <td><?php echo utf8_decode($mostrar[19]) ?></td>
                            <td><?php echo utf8_decode($mostrar[20]) ?></td>
                            <td><?php echo utf8_decode($mostrar[21]) ?></td>
                            <td><?php echo utf8_decode($mostrar[22]) ?></td>
                            <td><?php echo utf8_decode($mostrar[24]) ?></td>
                            <td><?php echo utf8_decode($mostrar[25]) ?></td>

                             
                            </tr>
                            <?php
                        }
                        ?>                              
                    </tbody>
                </table>