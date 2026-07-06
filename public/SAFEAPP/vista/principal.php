<?php

session_start();
if (isset($_SESSION['idusu'])) {
    $idusu = $_SESSION['idusu'];
    require_once '../capa_conexion/conexion.php';
    $obj = new conectar();
    $conexion = $obj->conexion();

    // $sql = "SELECT u.id_usuario, u.personal, r.nombre_rol, u.id_rol from usuario u inner join rol r on (r.id_rol = u.id_rol) where u.id_usuario='$idusu'";
    // $result = sqlsrv_query($conexion, $sql);
    // if ($result === false) {
    //     die(print_r(sqlsrv_errors(), true));
    // }
    // while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
    //     $personal = $row['personal'];
    //     $rol = $row['nombre_rol'];
    //     $idrol = $row['id_rol'];
    // }

    $sql = "SELECT a.id_tienda,t.nombre from asigna as a left join tienda as t on t.id_tienda=a.id_tienda
 where  a.estado='0' and id_usuario='$idusu'";

    $result = mysqli_query($conexion, $sql);

    $ver = mysqli_fetch_array($result);

    $tienda = $ver[0];
    $nombretienda = $ver[1];

    ?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Administrador|Dashboard</title>
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
                <section class="content">
                   
                         <div id="page-wrapper">
                <div class="container-fluid">


                    <div class="row">
                        <div class="col-lg-12">
                           
                             
                         
                            <ol class="breadcrumb" >
                                <li class="active">
                                    USUARIO: &nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-user-circle"></i><?php echo
                                        $personal
                                    ; ?>
                                </li>
                            </ol>
                            <ol class="breadcrumb">
                                <li class="active">
                                    CARGO:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <i class="fa fa-id-card"></i><?php echo
                                        $rol
                                    ; ?>
                                </li>
                            </ol>
                              <ol class="breadcrumb">
                                <li class="active">
                                    SUPERVISOR:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <i class="fa fa-id-card"></i><?php echo
                                        $supervisor
                                    ; ?>
                                </li>
                            </ol>
                            <ol class="breadcrumb">
                                <li class="active">
                                    REGION: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a onclick="cambiartie()"><i class="fa fa-bank"></i><?php echo
                                        $nombretienda
                                    ; ?></a> 
                                </li>
                            </ol>

                            

                         

                        </div>
                    </div>
                </div>

            </div>

                   
                       
                </section>
            </div>
            <!-- Contenido de la página -->

<div class="modal fade" id="modalTienda" tabindex="-1" role="dialog" 
     aria-labelledby="exampleModalLabel " aria-hidden="true">
<div class="modal-dialog" role="document">
<div class="modal-content">
<div class="modal-header">

<button type="button" class="close" data-dismiss="modal" aria-label="Close" id='cerrar1'>
<span aria-hidden="true">&times;</span>
</button>
</div>
<div class="modal-body">
    
 <form method="post" id="frmTienda">
                <div class="row">
                        <div class="form-group col-xs-12">
                          <label>Usuario</label>
                <input type="text"  class="form-control input-sm" id="personal" 
                name="personal" value="<?php echo $personal ?>" disabled="">
                <input type="hidden"  class="form-control input-sm" id="idusu" name="idusu" value="<?php echo $idusu ?>">
                           
                          
                        </div>
                    </div>
               

            <div class="row">
          <div class="form-group col-xs-12">
           <label>Tienda Actual</label>
              <input type="text" name="tiendaact" id="tiendaact" class="form-control input-sm" disabled=""
              value="<?php echo $nombretienda ?>">
              <input type="hidden"  class="form-control input-sm" id="idtiendaact" name="idtiendaact" value="<?php echo
                  $tienda
              ?>">
           
          </div>
            </div>
        <div class="row">
          <div class="form-group col-xs-12">
            <label>Tienda</label>
              <select name="nvatienda" id="nvatienda" class="form-control input-sm"></select>
           
          </div>
            </div>
           
          
          
        </form>




</div>
<div class="modal-footer" id="botones" >
<button type="button" class="btn btn-secondary" data-dismiss="modal" id='cerrar2'>Cerrar</button>
<button type="button" class="btn btn-warning" id="cambiar" >Cambiar</button>
</div>

</div>
</div>
</div>



            <footer class="main-footer">
                <div class="pull-right hidden-xs">
                    <b>Version</b> 1.0
                </div>
                <strong>Copyright &copy; <?php echo date('Y'); ?> <a href="#">Sistema comercial</a>.</strong>
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
           <script src="../capa_presentacion/librerias/alertify/alertify.js"></script>
         <script type="text/javascript" src="js/principal.js"></script>
    </body>
</html>
<?php
} else {
    header('Location: /SAFEAPP/vista/login.php');
}
