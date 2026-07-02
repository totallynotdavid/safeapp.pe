<?php
session_start();
$idusu = $_SESSION["idusu"];
require_once "../capa_conexion/conexion.php";

$obj = new conectar();
$conexion = $obj->conexion();

$sql = "SELECT id_tienda from asigna where id_usuario='$idusu' and estado='0'";

$result = mysqli_query($conexion, $sql);


$ver = mysqli_fetch_array($result);

$tienda = $ver[0];


$sqldatos = "SELECT  d.ruc,d.razon_social,d.zonal,d.nodo,d.origen,d.tipo_data,d.cliente_movistar from datos as d 
where d.id_usuario='$idusu' and d.id_tienda='$tienda' and d.estado='0' and d.tipo_data='FIJA' LIMIT 50";

$resultdatos = mysqli_query($conexion, $sqldatos);

?>
<form class="form form-horizontal">
    <div class="panel panel-primary">
        <div class="panel-body">
            <div>
                <table class="table table-hover table-condensed table-bordered" id="iddatatable">
                    <thead style="background-color: #dc3545;color: white; font-weight: bold;">
                        <tr>
                            <th>RUC</th>
                            <th>RAZON SOCIAL</th>
                            <th>ZONAL</th>
                            <th>NODO</th>
                            <th>ORIGEN</th>
                            <th>DATA</th>
                            <th>MOVISTAR</th>

                            <th>GESTION</th>

                        </tr>
                    </thead>

                    <tbody>
                        <?php

                        while ($mostrar = mysqli_fetch_array($resultdatos)) {
                        ?>
                            <tr>
                                <td><?php echo $mostrar[0] ?></td>
                                <td><?php echo $mostrar[1] ?></td>
                                <td><?php echo $mostrar[2] ?></td>
                                <td><?php echo $mostrar[3] ?></td>
                                <td><?php echo $mostrar[4] ?></td>
                                <td><?php echo $mostrar[5] ?></td>
                                <td><?php echo $mostrar[6] ?></td>


                                <td style="text-align: center;">
                                    <span class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalEditar" onclick="TraerDatosTabla('<?php echo $mostrar[0] ?>')"> <span class="fa fa-phone"></span>
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
    $(document).ready(function() {
        $('#iddatatable').DataTable();
    });
</script>