<?php

session_start();
$idusu=$_SESSION ["idusu"];
require_once "../capa_conexion/conexion.php";
$obj= new conectar();
$conexion=$obj->conexion();



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
  <body class="skin-red">
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
            <section class="content">
            
              <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#agregarnuevosdatosmodal" 
              id="nuevo" name="nuevo" >
                <span class="fa fa-plus-square"> Agregar</span>
              </button>
            </span>
            
            <div class="table-responsive">         
              <div id="tablaDatatable"></div> 
            </div>
            </section>
  <!-- Modal -->
  <div class="modal fade" id="agregarnuevosdatosmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Agrega nuevo usuario</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"  id='cerrar1'>
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="frmnuevo">
            <label>Usuario:</label>
            <input type="text" class="form-control input-sm" id="nombre" name="nombre">
            <label>Contraseña: </label>
            <input type="password" class="form-control input-sm" id="clave" name="clave">

            <label>Rol: </label>

            <select name="selcombo" class="form-control" id='selcombo'>

            </select>
            <label>Nombre completo:</label>
            <input type="text" class="form-control input-sm" id="personal" name="personal">
            <label>¿Habilitado?</label>
            <div class="form-group">
                  <div class="radio">
                    <label>
                      <input type="radio" name="estado" id="estado1" value="0" checked=""> Sí
                    </label>
                  </div>
                  <div class="radio">
                    <label>
                      <input type="radio" name="estado" id="estado2" value="1"> No
                    </label>
                  </div>
            </div>  
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal" id='cerrar2'>Cerrar</button>
          <button type="button" id="btnAgregarnuevo" class="btn btn-primary">Agregar nuevo</button>
        </div>
      </div>
    </div>
  </div>


  <!-- Modal -->
  <div class="modal fade" id="modalEditar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Actualizar usuario</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="frmnuevoU">
            <label>Usuario:</label>
            <input type="text" class="form-control input-sm" id="nombreU" name="nombreU">
            <label>Contraseña: </label>
            <input type="password" class="form-control input-sm" id="claveU" name="claveU">
            <label>Seleccione Rol: </label>
            <select name="selcomboU" class="form-control">
            </select>
            <label>Nombre completo del personal:</label>
            <input type="text" class="form-control input-sm" id="personalU" name="personalU">
            <label>¿Habilitado?</label>
            <div class="form-group">
                  <div class="radio">
                    <label>
                      <input type="radio" name="optionsRadios" id="estadoU" value="1" checked=""> Sí
                    </label>
                  </div>
                  <div class="radio">
                    <label>
                      <input type="radio" name="optionsRadios" id="estado1U" value="0"> No
                    </label>
                  </div>
            </div>  
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-warning" id="btnActualizar">Actualizar</button>
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
    <script src="js/usuario.js"></script>
  </body>
</html>
