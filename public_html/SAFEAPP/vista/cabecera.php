<?php

$sql = "SELECT u.id_usuario, u.personal, r.nombre_rol, u.id_rol,u.sexo,s.nombre from usuario u 
inner join rol r on (r.id_rol = u.id_rol) inner join supervisor as s on 
(s.id_supervisor=u.id_supervisor)
 where u.id_usuario='$idusu'";

$result = mysqli_query($conexion, $sql);
if ($result === false) {
    die(print_r(mysqli_error(), true));
}
while ($row = mysqli_fetch_array($result)) {
    $personal = $row['personal'];
    $rol = $row['nombre_rol'];
    $idrol = $row['id_rol'];
    $sexo = $row['sexo'];
    $supervisor = $row[5];
}

?>
<header class="main-header">

    <a href="principal.php" class="logo"><b>INFINITY</b></a> 
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top" role="navigation">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </a>
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <!-- Messages: style can be found in dropdown.less-->
                <!-- Notifications: style can be found in dropdown.less -->
                <!-- Tasks: style can be found in dropdown.less -->

                <!-- User Account: style can be found in dropdown.less -->
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                   <?php if ($sexo === 'M') { ?> 
                        <img src="../capa_presentacion/img/1.png" class="user-image" alt="User Image"/>

                        <?php } else { ?>

                        <img src="../capa_presentacion/img/2.png" class="user-image" alt="User Image"/>
                        <?php } ?>
                        <span class="hidden-xs"><?php echo $personal ?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <li class="user-header">



                         <?php if ($sexo === 'M') { ?> 
                        <img src="../capa_presentacion/img/1.png" class="img-circle" alt="User Image" />

                        <?php } else { ?>

                          <img src="../capa_presentacion/img/2.png" class="img-circle" alt="User Image" />
                        <?php } ?>
                     

                            <p>
                                <?php echo $personal; ?>
                                <br>
                                <small><?php echo $rol; ?></small>
                            </p>
                        </li>
                        <!-- Menu Body -->
                        <li class="user-body">
                            <div class="col-xs-12 text-center">
                                <a href="#"><i class="fa fa-key text-primary"></i> <span class="text-primary">Cambiar mi contraseña</span></a>
                            </div>
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <div class="pull-left " >
                                <a href="principal.php"  class="btn btn-default btn-flat"><i class="fa fa-fw fa-institution"></i> Tienda</a>
                            </div>
                            <div class="pull-right ">
                                <a href="../capa_presentacion/logout.php" class="btn btn-default btn-flat"><i class="fa fa-power-off"></i> Cerrar Sesión</a>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</header>
