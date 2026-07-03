<?php

session_start();
$idusu = $_SESSION['idusu'];
require_once '../capa_conexion/conexion.php';

$obj = new conectar();
$conexion = $obj->conexion();

header('Content-Type: application/xls');
header('Content-Disposition: attachment; filename= ReporteHistorico.xls');

$supervisor = $_POST['supervisor'];
$idusu = $_POST['idusu'];

$desde = $_POST['desde'];
$hasta = $_POST['hasta'];
$date_hasta = strtotime('+1 day', strtotime($hasta));
$date_hasta = date('Y-m-d', $date_hasta);

$sqlEXCEL = "SELECT u.personal, 
                    ll.fecha,
                    ll.ruc, 
                    ll.razon_social,
                    r.descripcion, 
                    ll.nombre_contacto,
                    ll.telefono,
                    ll.comentario,
                    ll.id_llamada,
                    ll.tipo_data,
                    ll.gestion 
                    FROM llamada AS ll 
                    INNER JOIN respuesta AS r ON ll.id_respuesta = r.id_respuesta 
                    INNER JOIN usuario AS u ON ll.id_usuario = u.id_usuario
                    WHERE u.id_supervisor = '$supervisor'
                    AND ll.fecha >= '$desde' AND ll.fecha < '$date_hasta' 
                    ORDER BY ll.fecha DESC";

$resultEXCEL = mysqli_query($conexion, $sqlEXCEL);

?>

<table class="table table-hover table-condensed table-bordered" id="iddatatable">
    <thead style="background-color: #dc3545;color: blue; font-weight: bold;">
        <tr>
            <th>PERSONAL</th>
            <th>FECHA</th>
            <th>RUC</th>
            <th>RAZON SOCIAL</th>
            <th>DESCRIPCION</th>
            <th>NOMBRE CONTACTO</th>
            <th>TELEFONO</th>
            <th>COMENTARIO</th>
            <th>LLAMADA</th>
            <th>TIPO DATA</th>
            <th>GESTION</th>
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

            </tr>
            <?php }
        ?>
    </tbody>
</table>
