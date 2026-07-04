<?php

require_once '../capa_logica/sesion.php';
$idusu = $_SESSION['idusu'];
require_once '../capa_conexion/conexion.php';

$obj = new conectar();
$conexion = $obj->conexion();

header('Content-Type: application/xls');
header('Content-Disposition: attachment; filename= Reporte_Quiebre.xls');

$ano = $_POST['ncano'];
$periodo = $_POST['ncperiodo'];
$validacionf = $_POST['ncvalidacionf'];
$tienda = $_POST['tiendaf'];

if ($validacionf === 'PENDIENTE') {
    $sqlEXCEL = " SELECT    usu.personal, 
                            ncquiebre.fecha_registro,  
                            ncquiebre.fecha_activacion,
                            ncquiebre.fecha_inicio_averia,
                            ncquiebre.ruc, 
                            ncquiebre.razon_social, 
                            ncquiebre.servicio,
                            ncquiebre.tipo_averia, 
                            ncquiebre.problema_equipo, 
                            ncquiebre.detalle_equipo, 
                            ncquiebre.ticket_atencion, 
                            ncquiebre.fecha_ticket_atencion, 
                            ncquiebre.numero_ticket, 
                            ncquiebre.contacto1, 
                            ncquiebre.celular1,
                            ncquiebre.correo1, 
                            ncquiebre.contacto2, 
                            ncquiebre.celular2,
                            ncquiebre.correo2,
                            usu.personal,
                            ncquiebre.numero_problema, 
                            t.nombre,
                            ncquiebre.comentario_ejecutivo,
                            usu.personal,     
                            ncquiebre.estado,
                            ncquiebre.casosf,
                            ncquiebre.comentario_validador,
                            ncquiebre.back
                            FROM quiebre as ncquiebre 
                            inner join usuario as usu on usu.id_usuario = ncquiebre.id_usuario 
                            left join tienda as t on t.id_tienda = ncquiebre.zonal_telefonica
                            where year(ncquiebre.fecha_registro)<='$ano' 
                            and ncquiebre.estado='$validacionf' 
                            and ncquiebre.zonal_telefonica='$tienda'
                            ORDER BY ncquiebre.fecha_ingreso DESC";
} else if ($validacionf === 'ATENDIDO') {
    $sqlEXCEL = " SELECT    usu.personal, 
                            ncquiebre.fecha_registro,  
                            ncquiebre.fecha_activacion,
                            ncquiebre.fecha_inicio_averia,
                            ncquiebre.ruc, 
                            ncquiebre.razon_social, 
                            ncquiebre.servicio,
                            ncquiebre.tipo_averia, 
                            ncquiebre.problema_equipo, 
                            ncquiebre.detalle_equipo, 
                            ncquiebre.ticket_atencion, 
                            ncquiebre.fecha_ticket_atencion, 
                            ncquiebre.numero_ticket, 
                            ncquiebre.contacto1, 
                            ncquiebre.celular1,
                            ncquiebre.correo1, 
                            ncquiebre.contacto2, 
                            ncquiebre.celular2,
                            ncquiebre.correo2,
                            usu.personal,
                            ncquiebre.numero_problema, 
                            t.nombre,
                            ncquiebre.comentario_ejecutivo,
                            usu.personal,     
                            ncquiebre.estado,
                            ncquiebre.casosf,
                            ncquiebre.comentario_validador,
                            ncquiebre.back
                            FROM quiebre as ncquiebre 
                            inner join usuario as usu on usu.id_usuario = ncquiebre.id_usuario 
                            left join tienda as t on t.id_tienda = ncquiebre.zonal_telefonica
                            where month(ncquiebre.fecha_registro)='$periodo' 
                            and year(ncquiebre.fecha_registro)<='$ano' 
                            and ncquiebre.estado='$validacionf' 
                            and ncquiebre.zonal_telefonica='$tienda'
                            ORDER BY ncquiebre.fecha_ingreso DESC";
} else if ($validacionf === 'CURSO') {
    $sqlEXCEL = " SELECT    usu.personal, 
                            ncquiebre.fecha_registro,  
                            ncquiebre.fecha_activacion,
                            ncquiebre.fecha_inicio_averia,
                            ncquiebre.ruc, 
                            ncquiebre.razon_social, 
                            ncquiebre.servicio,
                            ncquiebre.tipo_averia, 
                            ncquiebre.problema_equipo, 
                            ncquiebre.detalle_equipo, 
                            ncquiebre.ticket_atencion, 
                            ncquiebre.fecha_ticket_atencion, 
                            ncquiebre.numero_ticket, 
                            ncquiebre.contacto1, 
                            ncquiebre.celular1,
                            ncquiebre.correo1, 
                            ncquiebre.contacto2, 
                            ncquiebre.celular2,
                            ncquiebre.correo2,
                            usu.personal,
                            ncquiebre.numero_problema, 
                            t.nombre,
                            ncquiebre.comentario_ejecutivo,
                            usu.personal,     
                            ncquiebre.estado,
                            ncquiebre.casosf,
                            ncquiebre.comentario_validador,
                            ncquiebre.back
                            FROM quiebre as ncquiebre 
                            inner join usuario as usu on usu.id_usuario = ncquiebre.id_usuario 
                            left join tienda as t on t.id_tienda = ncquiebre.zonal_telefonica
                            where month(ncquiebre.fecha_registro)='$periodo' 
                            and year(ncquiebre.fecha_registro)<='$ano' 
                            and ncquiebre.estado='$validacionf' 
                            and ncquiebre.zonal_telefonica='$tienda'
                            ORDER BY ncquiebre.fecha_ingreso DESC";
} else if ($validacionf === 'DEVUELTO') {
    $sqlEXCEL = " SELECT    usu.personal, 
                            ncquiebre.fecha_registro,  
                            ncquiebre.fecha_activacion,
                            ncquiebre.fecha_inicio_averia,
                            ncquiebre.ruc, 
                            ncquiebre.razon_social, 
                            ncquiebre.servicio,
                            ncquiebre.tipo_averia, 
                            ncquiebre.problema_equipo, 
                            ncquiebre.detalle_equipo, 
                            ncquiebre.ticket_atencion, 
                            ncquiebre.fecha_ticket_atencion, 
                            ncquiebre.numero_ticket, 
                            ncquiebre.contacto1, 
                            ncquiebre.celular1,
                            ncquiebre.correo1, 
                            ncquiebre.contacto2, 
                            ncquiebre.celular2,
                            ncquiebre.correo2,
                            usu.personal,
                            ncquiebre.numero_problema, 
                            t.nombre,
                            ncquiebre.comentario_ejecutivo,
                            usu.personal,     
                            ncquiebre.estado,
                            ncquiebre.casosf,
                            ncquiebre.comentario_validador,
                            ncquiebre.back
                            FROM quiebre as ncquiebre 
                            inner join usuario as usu on usu.id_usuario = ncquiebre.id_usuario 
                            left join tienda as t on t.id_tienda = ncquiebre.zonal_telefonica
                            where month(ncquiebre.fecha_registro)='$periodo' 
                            and year(ncquiebre.fecha_registro)<='$ano' 
                            and ncquiebre.estado='$validacionf' 
                            and ncquiebre.zonal_telefonica='$tienda'
                            ORDER BY ncquiebre.fecha_ingreso DESC";
} else {
    $sqlEXCEL = " SELECT    usu.personal, 
                            ncquiebre.fecha_registro,  
                            ncquiebre.fecha_activacion,
                            ncquiebre.fecha_inicio_averia,
                            ncquiebre.ruc, 
                            ncquiebre.razon_social, 
                            ncquiebre.servicio,
                            ncquiebre.tipo_averia, 
                            ncquiebre.problema_equipo, 
                            ncquiebre.detalle_equipo, 
                            ncquiebre.ticket_atencion, 
                            ncquiebre.fecha_ticket_atencion, 
                            ncquiebre.numero_ticket, 
                            ncquiebre.contacto1, 
                            ncquiebre.celular1,
                            ncquiebre.correo1, 
                            ncquiebre.contacto2, 
                            ncquiebre.celular2,
                            ncquiebre.correo2,
                            usu.personal,
                            ncquiebre.numero_problema, 
                            t.nombre,
                            ncquiebre.comentario_ejecutivo,
                            usu.personal,
                            ncquiebre.estado,
                            ncquiebre.casosf,
                            ncquiebre.comentario_validador,
                            ncquiebre.back
                            FROM quiebre as ncquiebre 
                            inner join usuario as usu on usu.id_usuario = ncquiebre.id_usuario
                            left join tienda as t on t.id_tienda = ncquiebre.zonal_telefonica 
                            where  year(ncquiebre.fecha_registro)<='$ano' 
                            and ncquiebre.estado='PENDIENTE'  
                            and ncquiebre.zonal_telefonica='$tienda'

                            UNION

                    SELECT  usu.personal, 
                            ncquiebre.fecha_registro,  
                            ncquiebre.fecha_activacion,
                            ncquiebre.fecha_inicio_averia,
                            ncquiebre.ruc, 
                            ncquiebre.razon_social, 
                            ncquiebre.servicio,
                            ncquiebre.tipo_averia, 
                            ncquiebre.problema_equipo, 
                            ncquiebre.detalle_equipo, 
                            ncquiebre.ticket_atencion, 
                            ncquiebre.fecha_ticket_atencion, 
                            ncquiebre.numero_ticket, 
                            ncquiebre.contacto1, 
                            ncquiebre.celular1,
                            ncquiebre.correo1, 
                            ncquiebre.contacto2, 
                            ncquiebre.celular2,
                            ncquiebre.correo2,
                            usu.personal,
                            ncquiebre.numero_problema, 
                            t.nombre,
                            ncquiebre.comentario_ejecutivo,
                            usu.personal,
                            ncquiebre.estado,
                            ncquiebre.casosf,
                            ncquiebre.comentario_validador,
                            ncquiebre.back
                            FROM quiebre as ncquiebre 
                            inner join usuario as usu on usu.id_usuario = ncquiebre.id_usuario 
                            left join tienda as t on t.id_tienda = ncquiebre.zonal_telefonica
                            and year(ncquiebre.fecha_registro)='$ano' 
                            and ncquiebre.estado='ATENDIDO' 
                            and ncquiebre.zonal_telefonica='$tienda'

                            UNION

                    SELECT  usu.personal, 
                            ncquiebre.fecha_registro,  
                            ncquiebre.fecha_activacion,
                            ncquiebre.fecha_inicio_averia,
                            ncquiebre.ruc, 
                            ncquiebre.razon_social, 
                            ncquiebre.servicio,
                            ncquiebre.tipo_averia, 
                            ncquiebre.problema_equipo, 
                            ncquiebre.detalle_equipo, 
                            ncquiebre.ticket_atencion, 
                            ncquiebre.fecha_ticket_atencion, 
                            ncquiebre.numero_ticket, 
                            ncquiebre.contacto1, 
                            ncquiebre.celular1,
                            ncquiebre.correo1, 
                            ncquiebre.contacto2, 
                            ncquiebre.celular2,
                            ncquiebre.correo2,
                            usu.personal,
                            ncquiebre.numero_problema, 
                            t.nombre,
                            ncquiebre.comentario_ejecutivo,
                            usu.personal,
                            ncquiebre.estado,
                            ncquiebre.casosf,
                            ncquiebre.comentario_validador,
                            ncquiebre.back
                            FROM quiebre as ncquiebre 
                            inner join usuario as usu on usu.id_usuario = ncquiebre.id_usuario 
                            left join tienda as t on t.id_tienda = ncquiebre.zonal_telefonica
                            and year(ncquiebre.fecha_registro)='$ano' 
                            and ncquiebre.estado='PROCESANDO' 
                            and ncquiebre.zonal_telefonica='$tienda'

                            UNION

                    SELECT  usu.personal, 
                            ncquiebre.fecha_registro,  
                            ncquiebre.fecha_activacion,
                            ncquiebre.fecha_inicio_averia,
                            ncquiebre.ruc, 
                            ncquiebre.razon_social, 
                            ncquiebre.servicio,
                            ncquiebre.tipo_averia, 
                            ncquiebre.problema_equipo, 
                            ncquiebre.detalle_equipo, 
                            ncquiebre.ticket_atencion, 
                            ncquiebre.fecha_ticket_atencion, 
                            ncquiebre.numero_ticket, 
                            ncquiebre.contacto1, 
                            ncquiebre.celular1,
                            ncquiebre.correo1, 
                            ncquiebre.contacto2, 
                            ncquiebre.celular2,
                            ncquiebre.correo2,
                            usu.personal,
                            ncquiebre.numero_problema, 
                            t.nombre,
                            ncquiebre.comentario_ejecutivo,
                            usu.personal,
                            ncquiebre.estado,
                            ncquiebre.casosf,
                            ncquiebre.comentario_validador,
                            ncquiebre.back
                            FROM quiebre as ncquiebre 
                            inner join usuario as usu on usu.id_usuario = ncquiebre.id_usuario 
                            left join tienda as t on t.id_tienda = ncquiebre.zonal_telefonica
                            and year(ncquiebre.fecha_registro)='$ano' 
                            and ncquiebre.estado='DEVUELTO' 
                            and ncquiebre.zonal_telefonica='$tienda' ";
}

$resultEXCEL = mysqli_query($conexion, $sqlEXCEL);

?>

<table class="table table-hover table-condensed table-bordered" id="iddatatable">
    <thead style="background-color: #dc3545;color: blue; font-weight: bold;">
        <tr>
            <th>PERSONAL</th>
            <th>FECHA REGISTRO QUIEBRE</th>
            <th>FECHA ACTIVACION DEL SERVICIO</th>
            <th>FECHA INICIO DE AVERIA</th>
            <th>RUC</th>
            <th>RAZON SOCIAL</th>
            <th>SERVICIO</th>
            <th>TIPO DE AVERIA</th>
            <th>PROBLEMA</th>
            <th>DETALLE</th>
            <th>TICKET DE QUIEBRE</th>
            <th>FECHA DE TICKET</th>
            <th>NUMERO DE TICKET</th>
            <th>CONTACTO1</th>
            <th>CELULAR1</th>
            <th>CORREO1</th>
            <th>CONTACTO2</th>
            <th>CELULAR2</th>
            <th>CORREO2</th>
            <th>EJECUTIVO</th>
            <th>NUMERO DE PROBLEMA</th>
            <th>REGION</th>
            <th>COMENTARIO EJECUTIVO</th>
            <th>VALIDADOR</th>
            <th>VALIDACION</th>
            <th>CASO SF</th>
            <th>COMENTARIO VALIDADOR</th>
            <th>BACK</th>
        </tr>
    </thead>

    <tbody>
        <?php

        while ($mostrar = mysqli_fetch_array($resultEXCEL)) { ?>
            <tr>
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
                <td><?php echo utf8_decode($mostrar[18]) ?></td>
                <td><?php echo utf8_decode($mostrar[19]) ?></td>
                <td><?php echo utf8_decode($mostrar[20]) ?></td>
                <td><?php echo utf8_decode($mostrar[21]) ?></td>
                <td><?php echo utf8_decode($mostrar[22]) ?></td>
                <td><?php echo utf8_decode($mostrar[23]) ?></td>
                <td><?php echo utf8_decode($mostrar[24]) ?></td>
                <td><?php echo utf8_decode($mostrar[25]) ?></td>
                <td><?php echo utf8_decode($mostrar[26]) ?></td>
                <td><?php echo utf8_decode($mostrar[27]) ?></td>
            </tr>
        <?php }
        ?>
    </tbody>
</table>
