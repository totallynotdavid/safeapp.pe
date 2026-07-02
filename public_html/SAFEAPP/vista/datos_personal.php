<?php

session_start();
$idusu = $_SESSION["idusu"];
require_once "../capa_conexion/conexion.php";
$obj = new conectar();
$conexion = $obj->conexion();

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Usuario</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <?php
    include 'scriptsup.php';
    ?>
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
        <!-- Contenido de la página -->
        <div class="content-wrapper">
            <div class="col-lg-12" style="background-color: #00a65a;color: white; font-weight: bold;text-align: center ">DATOS PERSONAL</div>
            </br>
            <section class="content">
                <div class="table-responsive">
                    </br>
                    <div id="tablaDatatable"></div>
                </div>
            </section>



            <!-- Modal -->
            <div class="modal fade" id="modalEditar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Actualizar usuario</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" id='cerrar3'>
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="frmActualizar">
                                <input type="hidden" class="form-control input-sm" id="id_usu" name="id_usu">

                                <label>Nombre completo:</label>
                                <input type="text" class="form-control input-sm" id="personalA" name="personalA">

                                <label>Fecha de nacimiento:</label>
                                <input type="date" class="form-control input-sm" id="fecha_nacimientoA" name="fecha_nacimientoA">

                                <label>Teléfono:</label>
                                <input type="text" class="form-control input-sm" id="telefonoA" name="telefonoA">

                                <label>Correo:</label>
                                <input type="text" class="form-control input-sm" id="correoA" name="correoA">

                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal" id='cerrarA'>Cerrar</button>
                            <button type="button" class="btn btn-warning" id="btnActualizarP">Actualizar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido de la página -->

        <footer class="main-footer">
            <div class="pull-right hidden-xs">
                <b>Version</b> 1.0
            </div>
            <strong>Copyright &copy; <?php echo date('Y'); ?> <a href="#">Sistema de Llamadas</a>.</strong>
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
    <script src="js/datos_personal.js"></script>
</body>

</html>