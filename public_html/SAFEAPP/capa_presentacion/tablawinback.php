<?php

require_once "../capa_conexion/conexion.php";

$obj = new conectar();
$conexion = $obj->conexion();

$param=$_GET["ruc"];

$sqlwinback = " SELECT w.telefono,w.periodo,w.operador,w.fecha_portout
 from winback as w where w.ruc='$param'
";



$resultwinback = mysqli_query($conexion, $sqlwinback);


?>

<form class="form form-horizontal" >
    <div class="panel panel-primary">
        <div class="panel-body">
            <div>
                       <table class="table table-hover table-condensed table-bordered" id="iddatatable">
                    <thead style="background-color: #dc3545;color: white; font-weight: bold;">
                        <tr>
                            <th>TELEFONO</th>
                            <th>PERIODO</th>
                            <th>OPERADOR</th>
                            <th>FECHA PORTOUT</th>
                            
                            
                            
                        </tr>
                    </thead>
                     <tbody >
                       <?php
                        while ($mostrar = mysqli_fetch_array($resultwinback)) {
                            ?>
                            <tr >
                                <td><?php echo $mostrar[0] ?></td>
                                <td><?php echo $mostrar[1] ?></td>
                                <td><?php echo $mostrar[2] ?></td>
                                <td><?php echo $mostrar[3] ?></td>
                           
                              
                               
                        
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