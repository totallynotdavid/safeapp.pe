<?php

require_once "../capa_conexion/conexion.php";

$obj = new conectar();
$conexion = $obj->conexion();

$ruc = $_GET["ruc"];

$sqltelefono = "SELECT t.telefono,t.origen,t.comentario
 from telefono as t 
where t.ruc='$ruc'
  ";

$resultelefono = mysqli_query($conexion, $sqltelefono);


?>

<form class="form form-horizontal">
    <div class="panel panel-primary">
        <div class="panel-body">
            <div>
                <table class="table table-hover table-condensed table-bordered" id="iddatatable">
                    <thead style="background-color: #dc3545;color: white; font-weight: bold;">
                        <tr>
                            <th>TELEFONO</th>
                            <th>ORIGEN</th>
                            <th>COMENTARIO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($mostrar = mysqli_fetch_array($resultelefono)) {
                        ?>
                            <tr>
                                <td><?php echo $mostrar[0] ?></td>
                                <td><?php echo $mostrar[1] ?></td>
                                <td><?php echo $mostrar[2] ?></td>



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