<?php 


$sqlmenu = "SELECT m.id_menu,m.descripcion,m.icono FROM  acceso as a inner join menu as m on m.id_menu = a.id_menu where a.id_usuario='$idusu' 
    and m.estado=0 and m.id_ref=0";

?>
<section class="sidebar ">
    <!-- Sidebar user panel -->
    <!-- sidebar menu: : style can be found in sidebar.less -->
    <ul class="sidebar-menu">
        <li class="header">Menú Principal</li>
<?php 

$resultmenu = mysqli_query($conexion, $sqlmenu);

while ($row = mysqli_fetch_array($resultmenu)) {

?>

        <li class="treeview">
            <a href="#">
                <i class="<?php echo $row["icono"]; ?>"></i> <span><?php echo  $row["descripcion"]; ?></span>
                <i class="fa fa-angle-left pull-right"></i>
            </a>

            <?php
            $id=$row["id_menu"];

            $sqlsubmenu = "SELECT m.id_menu,m.descripcion,m.url,m.icono FROM  acceso as a inner join menu as m 
            on m.id_menu = a.id_menu where a.id_usuario='$idusu' 
                 and m.estado=0 and m.id_ref='$id' order by m.descripcion ";


                 $resultsubmenu = mysqli_query($conexion, $sqlsubmenu);

                    while ($row2 = mysqli_fetch_array($resultsubmenu)) {

                ?>

             <ul class="treeview-menu">

                <li><a href="<?php echo $row2["url"]; ?>"><i class="<?php echo $row2["icono"]; ?>"></i><?php echo $row2["descripcion"];?>
                </a></li>
            </ul>
         


<?php 

 }


 ?>

   </li>
 

 <?php
  }



?>
       </ul>  
<!--