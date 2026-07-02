<?php
session_start();

 if(isset( $_SESSION ["idusu"])){

$idusu = $_SESSION ["idusu"];
require_once "../capa_conexion/conexion.php";
$obj = new conectar();
$conexion = $obj->conexion();

$sql = "SELECT id_tienda from asigna where id_usuario='$idusu' and estado='0'";

$result = mysqli_query($conexion, $sql);

$ver = mysqli_fetch_array($result);

$tienda=$ver[0];


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

            <div class="col-lg-12"
                style="background-color: #dc3545;color: white; font-weight: bold;text-align: center ">DASHBOARD ONE
                CHANNEL REAL</div>


            </br>
            <form class="form form-horizontal" id='frmrepexportarback' action="../procesos/repexportarbackglobal.php"
                method="post">

                <ol class="breadcrumb">
                    <div class="form-group">

                        <div class="col-xs-2">
                            <label>PERIODO</label>
                        </div>
                        <div class="col-xs-2">
                            <select name="ncano" class="form-control" id='ncano'>
                                <option value="2021">2021</option>
                                <option value="2022">2022</option>
                                <option value="2025">2025</option>
                                <option value="2026" selected>2026</option>

                            </select>
                        </div>

                        <div class="col-xs-4">
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


                        <div class="col-xs-4">
                            <button type="button" class="btn btn-success" id="btnfiltrar">FILTRAR</button>
                        </div>




                    </div>
                </ol>

            </form>


            <section class="content">
                <div class="table-responsive">
                    <div class="col-lg-12"
                        style="background-color: #478dbc ;color: white; font-weight: bold;text-align: center ">RESUMEN
                        GESTION ONECHANNEL</div>
                    <div id="tablaDatatableGlobal"></div>
                    <div class="col-lg-12"
                        style="background-color: #00a65a;color: white; font-weight: bold;text-align: center ">RESUMEN
                        GESTION MOVIL</div>
                    <div id="tablaDatatableMovil"></div>

                    <div class="col-lg-12"
                        style="background-color: #dc3545;color: white; font-weight: bold;text-align: center ">RESUMEN
                        GESTION FIJA TRADICIONAL</div>
                    <div id="tablaDatatableFija"></div>
                    <div class="col-lg-12"
                        style="background-color: #615ca8;color: white; font-weight: bold;text-align: center ">RESUMEN
                        GESTION FIJA AVANZADA</div>
                    <div id="tablaDatatableAvz"></div>
                </div>
            </section>


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
    <script type="text/javascript" src="js/dashboard.js"></script>


</body>

</html>
<?php } else {


header('Location: /SAFEAPP/vista/login.php');

    
    }?>