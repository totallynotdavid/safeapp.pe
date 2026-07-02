<?php
session_start();
if (isset($_SESSION["idusu"])) {
    $idusu = $_SESSION["idusu"];
    require_once "../capa_conexion/conexion.php";
    $obj = new conectar();
    $conexion = $obj->conexion();
    $sql = "SELECT id_tienda from asigna where id_usuario='$idusu' and estado='0'";
    $result = mysqli_query($conexion, $sql);
    $ver = mysqli_fetch_array($result);
    $sqlsup = "SELECT id_supervisor from usuario where id_usuario='$idusu'";
    $resultsup = mysqli_query($conexion, $sqlsup);
    $versup = mysqli_fetch_array($resultsup);
    $idsupervisor = $versup[0];
    $tienda = $ver[0];
?>

    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html>

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>LLAMADA | Dashboard</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <?php
        include 'scriptsup.php';
        ?>
        <link rel="stylesheet" type="text/css" href="../capa_presentacion/librerias/datatable/dataTables.bootstrap4.min.css">


        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
<![endif]-->
    </head>

    <body class="skin-blue">
        <!-- Site wrapper -->
        <div class="wrapper">

            <?php
            include 'cabecera.php';
            ?>

            <!-- =============================================== -->

            <!-- Left side column. contains the sidebar -->
            <aside class="main-sidebar">
                <!-- sidebar: style can be found in sidebar.less -->
                <?php
                include 'menu.php';
                ?>
                <!-- /.sidebar -->
            </aside>

            <!-- =============================================== -->

            <!-- Contenido de la pÃ¡gina -->
            <div class="content-wrapper">
                <div class="col-lg-12" style="background-color: #00a65a;color: white; font-weight: bold;text-align: center ">REPORTE GESTION EJECUTIVOS</div> </br>
                <form class="form form-horizontal" id='frmrephistoricousu' action="../procesos/rephistoricousuexcel.php" method="post">
                    <input type="hidden" class="form-control input-sm" id="idsupervisor" name="idsupervisor" value="<?php echo $idsupervisor ?>">
                    <input type="hidden" class="form-control input-sm" id="idusu" name="idusu" value="<?php echo $idusu ?>">
                    <ol class="breadcrumb">
                        <div class="form-group">
                            <div class="col-lg-1">
                                <label>Desde</label>
                            </div>
                            <div class="col-lg-2">
                                <input type="date" class="form-control input-sm text-uppercase" id="desde" name="desde">
                            </div>
                            <div class="col-lg-1">
                                <label>Hasta</label>
                            </div>
                            <div class="col-lg-2">
                                <input type="date" class="form-control input-sm text-uppercase" id="hasta" name="hasta">
                            </div>

                            <div class="col-lg-2">
                                <button type="button" class="btn btn-success" id="mostrar">MOSTRAR</button>
                            </div>
                            <div class="col-xs-2">
                                <button type="submit" class="btn btn-success" id="excel">EXPORTAR</button>
                            </div>
                        </div>
                    </ol>

                </form>

                <section class="content">
                    <div class="table-responsive">
                        <div id="tablaDatatable"></div>
                    </div>
                </section>

            </div>
            <!-- Modal -->
            <div class="modal fade" id="modalEditar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel " aria-hidden="true" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">


                        <div class="modal-header">

                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" id='cerrar1'>
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div id="datos" class="col-lg-12" style=" text-align:center "></div>


                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#llamada" data-toggle="tab">LLAMADA</a></li>
                            <li><a href="#telefonos" data-toggle="tab">TELEFONOS</a></li>
                            <li><a href="#historial" data-toggle="tab">HISTORIAL</a></li>
                            <li><a href="#planta" data-toggle="tab">PLANTA</a></li>
                            <li><a href="#winback" data-toggle="tab">WINBACK</a></li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-content tab-pane fade in active " id="llamada">
                                <div class="modal-body">
                                    <form id="frmLlamada" method="post">

                                        <div class="row">
                                            <!--- div para elegir el tipo de respuesta-->

                                            <input type="hidden" class="form-control input-sm" id="idusu" name="idusu" value="<?php echo $idusu ?>">
                                            <input type="hidden" class="form-control input-sm" id="ruc" name="ruc">
                                            <input type="hidden" class="form-control input-sm" id="razon_social" name="razon_social">
                                            <input type="hidden" class="form-control input-sm" id="tipo_data" name="tipo_data">

                                            <input type="hidden" class="form-control input-sm" id="tienda" name="tienda" value="<?php echo $tienda ?>">

                                            <input type="hidden" disabled="" class="form-control input-sm" id="fechahoy" name="fechahoy" value="<?php
                                                                                                                                                date_default_timezone_set("America/Lima");
                                                                                                                                                $hoy = date("Y-m-d H:i:s");
                                                                                                                                                echo $hoy ?>">


                                            <div class="col-xs-4">
                                                <label>Telefono</label>
                                                <input type="number" class="form-control input-sm" id="telefono" name="telefono" required>

                                            </div>

                                            <div class="col-xs-3">
                                                <label>Tipo Gestión</label>
                                                <select name="gestion" class="form-control input-sm" id='gestion'>
                                                    <option value="LLAMADA" selected>LLAMADA</option>
                                                    <option value="VISITA">VISITA</option>
                                                </select>


                                            </div>
                                            <div class="col-xs-5">
                                                <label>Respuesta</label>
                                                <div class="form-group">
                                                    <select name="respuesta" id="respuesta" class="form-control input-sm"></select>
                                                </div>
                                            </div>

                                        </div>
                                        <!--- div para elegir el tipo de respuesta-->




                                        <div id="titular" style="display:none">
                                            <!--- div si la respuesta es titular interesado-->

                                            <div class="row">


                                                <div class="col-xs-4">
                                                    <label>Q Lineas</label>
                                                    <input type="number" class="form-control input-sm" id="qlineas" name="qlineas" required>

                                                </div>


                                                <div class="col-xs-8">
                                                    <label>Nombre contacto</label>
                                                    <input type="text" class="form-control input-sm" id="nomcontacto" name="nomcontacto">

                                                </div>


                                            </div>
                                            <div class="row">

                                                <div class="col-xs-12">
                                                    <label>Correo</label>
                                                    <input type="email" class="form-control input-sm" id="correorl" name="correorl">

                                                </div>



                                            </div>
                                            </br>


                                        </div>
                                        <!--- div si la respuesta es titular interesado-->



                                        <div id="comun" style="display:none">
                                            <!--- div de campos en comun-->

                                            <div class="row">
                                                <div class="col-xs-4">
                                                    <label>Q Claro</label>
                                                    <input type="number" class="form-control input-sm" id="qclaro" name="qclaro" required>

                                                </div>

                                                <div class="col-xs-4">
                                                    <label>Q Entel</label>
                                                    <input type="number" class="form-control input-sm" id="qentel" name="qentel" required>

                                                </div>
                                                <div class="col-xs-4">
                                                    <label>Q Bitel</label>
                                                    <input type="number" class="form-control input-sm" id="qbitel" name="qbitel" required>

                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xs-12">
                                                    <label for="comment">Comentario:</label>
                                                    <div class="form-group">
                                                        <textarea class="form-control" rows="2" id="comentario" name="comentario"></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <!--- div de campos en comun-->


                                    </form>
                                </div>
                                <div class="modal-footer" id="botones">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal" id='cerrar2'>Cerrar</button>
                                    <button type="button" class="btn btn-warning" id="btnRegistrar" style="display:none">Registrar</button>
                                </div>

                            </div>


                            <div class="tab-content tab-pane" id="historial">



                                <section class="content">
                                    <div class="table-responsive">
                                        <div id="tablaDatatablehistorial">



                                        </div>
                                    </div>
                                </section>
                            </div>

                            <div class="tab-content tab-pane" id="planta">



                                <section class="content">
                                    <div class="table-responsive">
                                        <div id="tablaDatatableplanta">

                                        </div>
                                    </div>
                                </section>
                            </div>

                            <div class="tab-content tab-pane" id="winback">



                                <section class="content">
                                    <div class="table-responsive">
                                        <div id="tablaDatatablewinback">



                                        </div>
                                    </div>
                                </section>
                            </div>

                            <div class="tab-content tab-pane" id="telefonos">



                                <section class="content">
                                    <div class="table-responsive">
                                        <div id="tablaDatatabletelefono">



                                        </div>
                                    </div>
                                </section>
                            </div>


                        </div>
                    </div>
                </div>
            </div>


            <!-- Contenido de la pÃ¡gina -->

            <footer class="main-footer">
                <div class="pull-right hidden-xs">
                    <b>Version</b> 1.0
                </div>
                <strong>Copyright &copy; <?php echo date('Y'); ?> <a href="#">Sistema Complementario</a>.</strong>
            </footer>
        </div><!-- ./wrapper -->

        <!-- jQuery 2.1.3 -->
        <script src="../util/jquery/jquery.min.js"></script>
        <!-- Bootstrap 3.3.2 JS -->
        <script src="../util/bootstrap/js/bootstrap.js" type="text/javascript"></script>
        <!-- SlimScroll -->
        <script src="../util/lte/plugins/slimScroll/jquery.slimScroll.min.js" type="text/javascript"></script>
        <!-- FastClick -->
        <script src='../util/lte/plugins/fastclick/fastclick.min.js'></script>
        <!-- AdminLTE App -->
        <script src="../util/lte/js/app.js" type="text/javascript"></script>
        <!-- Temas -->
        <script src="../util/lte/js/demo.js" type="text/javascript"></script>

        <script src="../capa_presentacion/librerias/datatable/jquery.dataTables.min.js"></script>
        <script src="../capa_presentacion/librerias/datatable/dataTables.bootstrap4.min.js"></script>
        <script src="../capa_presentacion/librerias/alertify/alertify.js"></script>
        <script type="text/javascript" src="js/historicousu.js"></script>


    </body>

    </html>

<?php } else {


    header('Location: /SAFEAPP/vista/login.php');
} ?>