<?php

session_start();
$idusu = $_SESSION['idusu'];
require_once '../capa_conexion/conexion.php';

$obj = new conectar();
$conexion = $obj->conexion();

$desde = $_GET['desde'];

$hasta = $_GET['hasta'];
$date_hasta = strtotime('+1 day', strtotime($hasta));
$date_hasta = date('Y-m-d', $date_hasta);

$sqldatos = "SELECT ll.fecha,ll.ruc, ll.razon_social,r.descripcion, ll.telefono,ll.comentario,ll.id_llamada,ll.tipo_data,ll.gestion 
from  llamada as ll inner join respuesta as r on ll.id_respuesta=r.id_respuesta

where ll.id_usuario='$idusu' 
and ll.fecha>='$desde' and ll.fecha<'$date_hasta'


ORDER BY ll.fecha ASC";

$resultdatos = mysqli_query($conexion, $sqldatos);

?>
<form class="form form-horizontal">
    <div class="panel panel-primary">
        <div class="panel-body">
            <div>
                <table class="table table-hover table-condensed table-bordered" id="iddatatable">
                    <thead style="background-color: #dc3545;color: white; font-weight: bold;">
                        <tr>
                            <th>FECHA</th>
                            <th>RUC</th>
                            <th>RAZON SOCIAL</th>
                            <th>RESPUESTA</th>
                            <th>TELEFONO</th>
                            <th>COMENTARIO</th>
                            <th>DATA</th>
                            <th>TIPO</th>

                            <th>GESTION</th>

                        </tr>
                    </thead>

                    <tbody>
                        <?php

                        while ($mostrar = mysqli_fetch_array($resultdatos)) { ?>
                            <tr>
                                <td><?php echo $mostrar[0] ?></td>
                                <td><?php echo $mostrar[1] ?></td>
                                <td><?php echo $mostrar[2] ?></td>
                                <td><?php echo $mostrar[3] ?></td>
                                <td><?php echo $mostrar[4] ?></td>
                                <td><?php echo $mostrar[5] ?></td>
                                <td><?php echo $mostrar[7] ?></td>
                                <td><?php echo $mostrar[8] ?></td>

                                <td style="text-align: center;">
                                    <span class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalEditar" onclick="TraerDatosTabla('<?php echo
                                        $mostrar[6]
                                    ?>')">
                                        <span class="fa fa-phone"></span>
                                    </span>
                                </td>
                            </tr>
                        <?php }
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
