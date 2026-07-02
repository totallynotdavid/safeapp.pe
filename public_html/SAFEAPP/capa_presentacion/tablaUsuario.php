<?php 
session_start();
$idusu=$_SESSION ["idusu"];
require_once "../capa_conexion/conexion.php";
$obj= new conectar();
$conexion=$obj->conexion();

$sql="SELECT u.id_usuario, u.usuario, u.clave, r.nombre_rol, u.personal, u.estado,s.nombre from usuario as u
inner join rol as r on r.id_rol=u.id_rol inner join supervisor as s on s.id_supervisor=u.id_supervisor where u.estado=1

 ";

$result=mysqli_query($conexion,$sql);

?>
<form class="form form-horizontal" >
<div class="panel panel-primary">
 <div class="panel-body">
 <div>
	<table class="table table-hover table-condensed table-bordered" id="iddatatable">
		<thead style="background-color: #dc3545;color: white; font-weight: bold;">
			<tr>
				<th>ID</th>
				<th>USUARIO</th>
				<th>ROL</th>
				<th>NOMBRE</th>
				<th>ESTADO</th>
				<th>SUPERVISOR</th>
				<th>MODIFICAR</th>

			</tr>
		</thead>
		
			<tbody >
			<?php 
			while ($mostrar=mysqli_fetch_array($result)) {
				?>
				<tr >
					<td><?php echo $mostrar[0] ?></td>
					<td><?php echo $mostrar[1] ?></td>	
					<td><?php echo $mostrar[3] ?></td>
					<td><?php echo $mostrar[4] ?></td>
					<td><?php if($mostrar[5]=='1'){echo "Habilitado";}else{echo "Inhabilitado";} ?></td>
					<td><?php echo $mostrar[6] ?></td>
					<td style="text-align: center;">
						<span class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modalEditar" onclick="FrmActualizar('<?php echo $mostrar[0] ?>')">
							<span class="fa fa-pencil-square-o"></span>
						</span>
						
					</td>
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
	$(document).ready(function() {
		$('#iddatatable').DataTable();
	} );
</script>