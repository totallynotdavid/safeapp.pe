<?php

session_start();

if (isset($_SESSION['idusu'])) {
    $idusu = $_SESSION['idusu'];
    require_once '../capa_conexion/conexion.php';
    $obj = new conectar();
    $conexion = $obj->conexion();

    $sql = "SELECT id_tienda from asigna where id_usuario='$idusu' and estado='0'";

    $result = mysqli_query($conexion, $sql);

    $ver = mysqli_fetch_array($result);

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
        <link rel="stylesheet" type="text/css"
            href="../capa_presentacion/librerias/datatable/dataTables.bootstrap4.min.css">
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
                <div class="col-lg-12"
                    style="background-color: #fc9c1c;color: white; font-weight: bold;text-align: center ">VALIDACION QUIEBRE
                </div>


                </br>
                <form class="form form-horizontal" id='frmrepexportarquiebrevalidacion'
                    action="../procesos/repexportarquiebrevalidacion.php" method="post">
                    <input type="hidden" class="form-control input-sm" id="tiendaf" name="tiendaf"
                        value="<?php echo $tienda ?>">
                    <ol class="breadcrumb">
                        <div class="form-group">

                            <div class="col-xs-1">
                                <label>PERIODO</label>
                            </div>
                            <div class="col-xs-2">
                                <select name="ncano" class="form-control" id='ncano'>
                                    <option value="2021">2021</option>
                                    <option value="2022">2022</option>
                                    <option value="2023">2023</option>
                                    <option value="2024">2024</option>
                                    <option value="2025">2025</option>
                                    <option value="2026" selected>2026</option>
                                </select>
                            </div>
                            <div class="col-xs-2">
                                <select name="ncperiodo" class="form-control" id='ncperiodo'>
                                    <option value="01">ENERO</option>
                                    <option value="02">FEBRERO</option>
                                    <option value="03">MARZO</option>
                                    <option value="04">ABRIL</option>
                                    <option value="05">MAYO</option>
                                    <option value="06">JUNIO</option>
                                    <option value="07">JULIO</option>
                                    <option value="08">AGOSTO</option>
                                    <option value="09">SETIEMBRE</option>
                                    <option value="10">OCTUBRE</option>
                                    <option value="11">NOVIEMBRE</option>
                                    <option value="12">DICIEMBRE</option>
                                </select>
                            </div>
                            <div class="col-xs-1">
                                <label>VALIDACION</label>
                            </div>
                            <div class="col-xs-2">
                                <select name="ncvalidacionf" class="form-control" id='ncvalidacionf'>
                                    <option value="PENDIENTE">PENDIENTE</option>
                                    <option value="ATENDIDO">ATENDIDO</option>
                                    <option value="PROCESANDO">PROCESANDO</option>
                                    <option value="DEVUELTO">DEVUELTO</option>
                                    <option value="TODO" selected>TODO</option>
                                </select>
                            </div>
                            <div class="col-xs-2">
                                <button type="button" class="btn btn-warning" id="btnfiltrar">FILTRAR</button>
                            </div>
                            <div class="col-xs-2">
                                <button type="button" class="btn btn-warning" id="btnexportar">EXPORTAR</button>
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
            <div class="modal fade" id="modalEditar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel "
                aria-hidden="true" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" id='cerrar1'>
                                <span aria-hidden="true">&times;</span>
                            </button>
                            </br>
                            <div class="col-lg-12"
                                style="background-color: #fc9c1c;color: white; font-weight: bold;text-align: center ">
                                VALIDACION QUIEBRE</div>
                        </div>
                        <div class="tab-content">
                            <div class="tab-content tab-pane fade in active " id="llamada">
                                <div class="modal-body">
                                    <form id="frmNCM" method="post">
                                        <div class="row">
                                            <!--- div para elegir el tipo de respuesta-->
                                            <div class="col-xs-6">
                                                <input type="hidden" class="form-control input-sm" id="idusu" name="idusu"
                                                    value="<?php echo $idusu ?>">
                                                <input type="hidden" class="form-control input-sm" id="tienda" name="tienda"
                                                    value="<?php echo $tienda ?>">
                                                <input type="hidden" class="form-control input-sm" id="idquiebre"
                                                    name="idquiebre">
                                                <input type="hidden" class="form-control input-sm" id="ncestado"
                                                    name="ncestado">
                                                <label>Fecha Registro</label> <img
                                                    title="Fecha en que el ejecutivo ingresó el quiebre"
                                                    src="../vista/img/informacion.png" alt="" width="15" height="15">
                                                <input type="date" disabled="" class="form-control input-sm"
                                                    id="fecha_registro_quiebre" name="fecha_registro_quiebre">
                                            </div>
                                            <div class="col-xs-6">
                                                <label>Activación servicio</label> <img
                                                    title="Fecha en que se activó el servicio al cliente"
                                                    src="../vista/img/informacion.png" alt="" width="15" height="15">
                                                <input type="date" class="form-control input-sm" id="fechaActivacions"
                                                    name="fechaActivacions" disabled="">
                                            </div>
                                        </div>
                                        <!--- div para elegir el tipo de respuesta-->
                                        <div id="nuevocliente" style="display:show">
                                            <!--- div si la respuesta es titular interesado-->
                                            <div class="row">
                                                <div class="col-xs-6">
                                                    <label>RUC</label>
                                                    <input type="text" disabled="" class="form-control input-sm" id="ncrucs"
                                                        name="ncrucs">
                                                </div>
                                                <div class="col-xs-6">
                                                    <label>Razon Social</label>
                                                    <input type="text" disabled="" class="form-control input-sm"
                                                        id="ncrazonsocials" name="ncrazonsocials" required>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xs-6">
                                                    <label>Servicio</label>
                                                    <input type="text" disabled="" class="form-control input-sm"
                                                        id="quiebre_servicios" name="quiebre_servicios">
                                                </div>
                                                <div class="col-xs-6">
                                                    <label>Tipo de Avería</label>
                                                    <input type="text" disabled="" class="form-control input-sm"
                                                        id="quiebre_tipo_averias" name="quiebre_tipo_averias">
                                                </div>
                                            </div>
                                        </div>
                                        <!--- div si la respuesta es titular interesado-->
                                        <div id="titular" style="display:show">
                                            <!--- div si la respuesta es titular interesado-->
                                            <div class="row">
                                                <div class="col-xs-4">
                                                    <label>Problema</label>
                                                    <input type="text" disabled="" class="form-control input-sm"
                                                        id="quiebre_problemas" name="quiebre_problemas">
                                                </div>
                                                <div class="col-xs-4">
                                                    <label>Detalle</label>
                                                    <input type="text" disabled="" class="form-control input-sm"
                                                        id="quiebre_detalles" name="quiebre_detalles" required>
                                                </div>
                                                <div class="col-xs-4">
                                                    <label>Back</label>
                                                    <input type="text" disabled="" class="form-control input-sm"
                                                        id="quiebre_backs" name="quiebre_backs" required>
                                                </div>
                                            </div>
                                            <br>
                                            <div class="row">
                                                <!-- <div class="col-xs-4">
                                                    <label>Ticket de Quiebre</label> <img title="Se genera un ticket solo si el cliente cuenta con más de 3 meses con el servicio" src="../vista/img/informacion.png" alt="" width="15" height="15">
                                                    <input type="text" disabled="" class="form-control input-sm" id="quiebre_tickets" name="quiebre_tickets" required>
                                                </div> -->
                                                <div class="col-xs-4">
                                                    <label>Fecha Inicio avería</label> <img
                                                        title="Fecha desde que el cliente presenta la avería"
                                                        src="../vista/img/informacion.png" alt="" width="15" height="15">
                                                    <input type="date" disabled="" class="form-control input-sm"
                                                        id="fechaInicios" name="fechaInicios">
                                                </div>

                                                <div class="col-xs-4">
                                                    <label>Fecha de Ticket</label>
                                                    <input type="date" disabled="" class="form-control input-sm"
                                                        id="fechaTickets" name="fechaTickets" required>
                                                </div>

                                                <div class="col-xs-4">
                                                    <label>Número de Ticket</label>
                                                    <input type="text" disabled="" class="form-control input-sm"
                                                        id="quiebre_numero_tickets" name="quiebre_numero_tickets" required>
                                                </div>
                                            </div>
                                        </div>
                                        <!--- div si la respuesta es titular interesado-->
                                        <div id="comun" style="display:show">
                                            <!--- div de campos en comun-->
                                            <div class="row">
                                                <div class="col-xs-4">
                                                    <label>Contacto 1</label>
                                                    <input type="text" disabled="" class="form-control input-sm"
                                                        id="quiebre_contacto1" name="quiebre_contacto1">
                                                </div>
                                                <div class="col-xs-4">
                                                    <label>Celular 1</label>
                                                    <input type="text" disabled="" class="form-control input-sm"
                                                        id="quiebre_celular1" name="quiebre_celular1">
                                                </div>
                                                <div class="col-xs-4">
                                                    <label>Correo 1</label>
                                                    <input type="text" disabled="" class="form-control input-sm"
                                                        id="quiebre_correo1" name="quiebre_correo1">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xs-4">
                                                    <label>Contacto 2</label>
                                                    <input type="text" disabled="" class="form-control input-sm"
                                                        id="quiebre_contacto2" name="quiebre_contacto2">
                                                </div>
                                                <div class="col-xs-4">
                                                    <label>Celular 2</label>
                                                    <input type="text" disabled="" class="form-control input-sm"
                                                        id="quiebre_celular2" name="quiebre_celular2">
                                                </div>
                                                <div class="col-xs-4">
                                                    <label>Correo 2</label>
                                                    <input type="text" disabled="" class="form-control input-sm"
                                                        id="quiebre_correo2" name="quiebre_correo2">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xs-4">
                                                    <label>Ejecutivo</label>
                                                    <input type="text" disabled="" class="form-control input-sm"
                                                        id="quiebre_ejecutivo" name="quiebre_ejecutivo">
                                                </div>
                                                <div class="col-xs-4">
                                                    <label>Q de líneas</label>
                                                    <input type="text" disabled="" class="form-control input-sm"
                                                        id="quiebre_numero_problemas" name="quiebre_numero_problemas">
                                                </div>
                                                <div class="col-xs-4">
                                                    <label>Región</label>
                                                    <input type="text" disabled="" class="form-control input-sm"
                                                        id="ncregions" name="ncregions">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xs-12">
                                                    <label for="comment">Comentario Ejecutivo:</label>
                                                    <div class="form-group">
                                                        <textarea disabled="" class="form-control" rows="3"
                                                            id="ncobservacioness" name="ncobservacioness"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xs-6">
                                                    <label for="comment">VALIDACION:</label>
                                                    <select name="ncvalidacions" class="form-control" id='ncvalidacions'>
                                                        <option value="PENDIENTE" selected>PENDIENTE</option>
                                                        <option value="ATENDIDO">ATENDIDO</option>
                                                        <option value="PROCESANDO">PROCESANDO</option>
                                                        <option value="DEVUELTO">DEVUELTO</option>
                                                    </select>
                                                </div>
                                                <div class="col-xs-6">
                                                    <label>CASO SF</label>
                                                    <input type="text" class="form-control input-sm" id="casosf"
                                                        name="casosf" required>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xs-12">
                                                    <label for="comment">Comentario Validador:</label>
                                                    <div class="form-group">
                                                        <textarea class="form-control" rows="3" id="ncobservacionesval"
                                                            name="ncobservacionesval"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--- div de campos en comun-->
                                    </form>
                                </div>
                                <div class="modal-footer" id="botones">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal"
                                        id='cerrar2'>Cerrar</button>
                                    <button type="button" class="btn btn-warning" id="btnRegistrar">Registrar</button>
                                </div>
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
        <script type="text/javascript" src="js/validacion_quiebre.js"></script>


    </body>

    </html>
<?php
} else {
    header('Location: /SAFEAPP/vista/login.php');
}
