<?php

require_once "../capa_conexion/conexion.php";

$obj = new conectar();
$conexion = $obj->conexion();

$param=$_GET["ruc"];

$sqlplanta = " SELECT d.cliente_movistar, d.q_movistar,d.q_entel,d.q_claro, d.q_bitel
 from datos as d where d.ruc='$param'
";



$resultplanta = mysqli_query($conexion, $sqlplanta);


?>

<form class="form form-horizontal" >
    <div class="panel panel-primary">
        <div class="panel-body">
            <div>
                       <table class="table table-hover table-condensed table-bordered" id="iddatatable">
                    <thead style="background-color: #dc3545;color: white; font-weight: bold;">
                        <tr>
                            <th>CLIENTE MOVISTAR</th>
                            <th>Q_MOVISTAR</th>
                            <th>Q_ENTEL</th>
                            <th>Q_CLARO</th>
                            <th>Q_OSIPTEL</th>
                            
                            
                        </tr>
                    </thead>
                     <tbody >
                       <?php
                        while ($mostrar = mysqli_fetch_array($resultplanta)) {
                            ?>
                            <tr >
                                <td><?php echo $mostrar[0] ?></td>
                                <td><?php echo $mostrar[1] ?></td>
                                <td><?php echo $mostrar[2] ?></td>
                                <td><?php echo $mostrar[3] ?></td>
                                <td><?php echo $mostrar[4] ?></td>
                              
                               
                        
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