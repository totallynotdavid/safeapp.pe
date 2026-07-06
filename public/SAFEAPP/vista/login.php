<?php

session_start();
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Sistema Llamadas</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- Bootstrap 3.3.2 -->
    <link href="../util/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css" />
    <!-- Font Awesome Icons -->
    <link href="../util/lte/css/font-awesome.css" rel="stylesheet" type="text/css" />
    <!-- Theme style -->
    <link href="../util/lte/css/AdminLTE.css" rel="stylesheet" type="text/css" />
    <!-- iCheck -->
    <link href="../util/lte/plugins/iCheck/square/blue.css" rel="stylesheet" type="text/css" />
    <style>
            .login-page, .register-page {
                 background-image: url("../capa_presentacion/img/fondo3.jpg");
                  /* Full height */
                  height: 100%; 
                  /* Center and scale the image nicely */
                  background-position: center;
                  background-repeat: no-repeat;
                  background-size: cover;
            } 

            .login-box-body, .register-box-body{
                background: #fff;
            }
    </style>
  </head>
  <body class="login-page">
    <div class="login-box">
      <div class="login-logo">
        <center>
         <!-- <img src="../capa_presentacion/img/LogoMovistar.png" width="400" height="100"> -->        
        </center>
        <a href="#"><b style="color: #00a9e0;  ">INFINITY</b></a>
      </div><!-- /.login-logo -->
      <div class="login-box-body">
        <p class="login-box-msg">Ingrese sus datos para iniciar sesión</p>
        <form method="post">
          <div class="form-group has-feedback">
              <input type="text" name="user" id="user" class="form-control input-sm" required autofocus placeholder="Ingresar Código">
            <span class="glyphicon glyphicon-user form-control-feedback"></span>
          </div>
          <div class="form-group has-feedback">
              <input type="password" name="pass" id="pass" class="form-control input-sm" required placeholder="Ingresar Contraseña">
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
          </div>
          <div class="row">
            <div class="col-xs-12">    
              <input type="button" name="login" id="login" value="Ingresar" class="btn btn-primary btn-block">                   
            </div><!-- /.col -->
            <br>
            <span id="result"></span>
          </div>
        </form>
      </div><!-- /.login-box-body -->
    </div><!-- /.login-box -->

    <!-- jQuery 2.1.3 -->
    <script src="../util/jquery/jquery.min.js"></script>
    <!-- Bootstrap 3.3.2 JS -->
    <script src="../util/bootstrap/js/bootstrap.js" type="text/javascript"></script>
    <script src="js/login.js"></script>
  </body>
</html>
