<?php

session_start();

if (isset($_SESSION['idusu'])) {
    $idusu = $_SESSION['idusu'];
    require_once '../capa_conexion/conexion.php';
    $obj = new conectar();
    $conexion = $obj->conexion();
    $sqlsup = "SELECT id_supervisor from usuario where id_usuario='$idusu'";
    $ruc = "SELECT ruc from datos where id_usuario='$idusu'";
    $resultsup = mysqli_query($conexion, $sqlsup);
    $versup = mysqli_fetch_array($resultsup);
    $idsupervisor = $versup[0];
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
                style="background-color: #fc9c1c;color: white; font-weight: bold;text-align: center ">QUIEBRE</div>
            </br>
            <form class="form form-horizontal" id='frmagregar' action="../procesos/rephistoricousuexcel.php"
                method="post">
                <ol class="breadcrumb">
                    <div class="col-xs-4">
                        <input type="text" class="form-control input-sm" id="nvoruc" name="nvoruc"
                            placeholder="DIGITA EL RUC" required>
                    </div>
                    <div class="col-lg-2">
                        <button type="button" class="btn btn-warning" id="btnagregar">AGREGAR</button>
                    </div>
                </ol>
            </form>
            </br>
            <form class="form form-horizontal" id='frmrephistoricousu' action="../procesos/rephistoricousuexcel.php"
                method="post">
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
                        <!-- <div class="col-xs-2">
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
                            </div> -->
                        <div class="col-xs-1">
                            <label>VALIDACION</label>
                        </div>
                        <div class="col-xs-2">
                            <select name="ncestado" class="form-control" id='ncestado'>
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
                            QUIEBRE</div>
                    </div>
                    <div class="tab-content">
                        <div class="tab-content tab-pane fade in active ">
                            <div class="modal-body">
                                <form id="frmQuiebre" method="post">
                                    <div class="row">
                                        <!--- div para elegir el tipo de respuesta-->
                                        <div class="col-xs-6">
                                            <input type="hidden" class="form-control input-sm" id="idusu" name="idusu"
                                                value="<?php echo $idusu ?>">
                                            <input type="hidden" class="form-control input-sm" id="idsupervisor"
                                                name="idsupervisor" value="<?php echo $idsupervisor ?>">
                                            <input type="hidden" class="form-control input-sm" id="ncfruc"
                                                name="ncfruc">
                                            <input type="hidden" class="form-control input-sm" id="ncfrazonsocial"
                                                name="ncfrazonsocial">
                                            <input type="hidden" disabled="" class="form-control input-sm"
                                                id="fecha_registro" name="fecha_registro"
                                                value="<?php

                                                date_default_timezone_set('America/Lima');
                                                $hoy = date('Y-m-d');
                                                echo $hoy ?>">
                                        </div>
                                    </div>
                                    <!--- div para elegir el tipo de respuesta-->
                                    <div id="nuevocliente" style="display:show">
                                        <!--- div si la respuesta es titular interesado-->
                                        <div class="row">
                                            <div class="col-xs-4">
                                                <label>Activación Servicio</label> <img
                                                    title="Fecha desde que el cliente tiene el servicio. Si es mayor a 3 meses se le genera un ticket."
                                                    src="../vista/img/informacion.png" alt="" width="15" height="15">
                                                <input type="date" onchange="obtenerFecha()"
                                                    class="form-control input-sm" id="fechaActivacion"
                                                    name="fechaActivacion">
                                            </div>
                                            <div class="col-xs-3">
                                                <label>RUC</label>
                                                <input type="text" disabled="" class="form-control input-sm" id="ncruc"
                                                    name="ncruc" required>
                                            </div>
                                            <div class="col-xs-5">
                                                <label>Razón Social</label>
                                                <input type="text" disabled="" class="form-control input-sm"
                                                    id="ncrazonsocial" title="<?php echo $ruc ?>" name="ncrazonsocial">
                                            </div>
                                        </div>
                                        <br>
                                        <div class="row">
                                            <div class="col-xs-6">
                                                <label>Servicio</label>
                                                <select name="quiebre_servicio" class="form-control"
                                                    id='quiebre_servicio'>
                                                    <option value="FIJA" selected>FIJA</option>
                                                    <option value="MOVIL">MOVIL</option>
                                                    <option value="AVANZADO">AVANZADO</optionM>
                                                </select>
                                            </div>
                                            <div class="col-xs-6">
                                                <label>Tipo de Avería</label>
                                                <select name="quiebre_tipo_averia" class="form-control"
                                                    id='quiebre_tipo_averia'>
                                                    <option value="SIN TV" selected>SIN TV</option>
                                                    <option value="SIN DATOS">SIN DATOS</option>
                                                    <option value="NO PUEDE REALIZAR / RECIBIR LLAMADAS">NO PUEDE
                                                        REALIZAR / RECIBIR LLAMADAS</option>
                                                    <option value="SIN SERVICIO">SIN SERVICIO</option>
                                                    <option value="FALLA EQUIPO">FALLA EQUIPO</option>
                                                    <option value="DUPLICIDAD DE RECIBOS">DUPLICIDAD DE RECIBOS</option>
                                                    <option value="FACTURA DE EQUIPOS">FACTURA DE EQUIPOS</option>
                                                    <option value="DESCUENTO NO APLICADO">DESCUENTO NO APLICADO</option>
                                                    <option value="CASI">CASI</option>
                                                    <option value="CAEQ">CAEQ</option>
                                                    <option value="REGISTRO CCAA">REGISTRO CCAA</option>
                                                    <option value="INCIDENCIA PAGO APP">INCIDENCIA PAGO APP</option>
                                                    <option value="AJUSTE FACTURACION">AJUSTE DE FACTURACIÓN</option>
                                                    <option value="IMPACTO PAGOS">IMPACTO DE PAGOS</option>
                                                    <option value="SOLICITUD BAJA">SOLICITUD DE BAJA</option>
                                                    <option value="CAMBIO TITULARIDAD">CAMBIO DE TITULARIDAD</option>
                                                    <option value="AGRUPACION LINEAS">AGRUPACIÓN DE LÍNEAS</option>
                                                    <option value="RECLAMO FACTURACION">RECLAMO FACTURACIÓN</option>
                                                    <option value="OTROS">OTROS</option>
                                                </select>
                                            </div>
                                        </div>
                                        </br>
                                    </div>
                                    <!--- div si la respuesta es titular interesado-->
                                    <div id="titular" style="display:show">
                                        <!--- div si la respuesta es titular interesado-->
                                        <div class="row">
                                            <div class="col-xs-4">
                                                <label>Problemas con el equipo</label>
                                                <select name="quiebre_problemas" class="form-control"
                                                    onchange="mostrarOpciones()" id='quiebre_problemas'>
                                                    <option value="SI">SI</option>
                                                    <option value="NO">NO</option>
                                                </select>
                                            </div>
                                            <div class="col-xs-4">
                                                <label>Detalle del equipo</label>
                                                <select name="quiebre_detalle" class="form-control"
                                                    id='quiebre_detalle'>
                                                    <option value="DECO" selected>DECO</option>
                                                    <option value="BAM">BAM</option>
                                                    <option value="MODEM">MODEM</option>
                                                    <option value="CELULAR">CELULAR</option>
                                                    <option value="TELEFONO">TELEFONO</option>
                                                    <option value="OTROS">OTROS</option>
                                                </select>
                                            </div>

                                            <div class="col-xs-4">
                                                <label>BACK</label>
                                                <select name="quiebre_back" class="form-control" id='quiebre_back'>
                                                    <option value="FABRICIO" selected>FABRICIO PASSALACQUA</option>
                                                    <option value="DAYANA GOMEZ">DAYANA GOMEZ</option>
                                                    <option value="JULIO BRAVO">JULIO BRAVO</option>
                                                </select>
                                            </div>
                                        </div>
                                        <br>
                                        <div class="row">
                                            <!-- <div class="col-xs-4">
                                                    <label>Ticket de atención</label> <img title="Se genera un ticket solo si el cliente cuenta con más de 3 meses con el servicio" src="../vista/img/informacion.png" alt="" width="15" height="15">
                                                    <select name="quiebre_ticket" class="form-control" onchange="mostrarOpcionesTicket()" id='quiebre_ticket'>
                                                        <option value="SI" selected>SI</option>
                                                        <option value="NO">NO</option>
                                                    </select>
                                                </div> -->
                                            <div class="col-xs-4">
                                                <label>Inicio de avería</label> <img
                                                    title="Fecha desde que el cliente presenta el problema."
                                                    src="../vista/img/informacion.png" alt="" width="15" height="15">
                                                <input type="date" class="form-control input-sm" id="fechaInicio"
                                                    name="fechaInicio">
                                            </div>

                                            <div class="col-xs-5">
                                                <label>Fecha Ticket de atención</label>
                                                <input type="date" class="form-control input-sm" id="fechaTicket"
                                                    name="fechaTicket" style="display: none;">
                                            </div>
                                            <div class="col-xs-3">
                                                <label>Número de ticket</label>
                                                <input type="text" class="form-control input-sm"
                                                    id="quiebre_numero_ticket" name="quiebre_numero_ticket"
                                                    style="display: none;">
                                            </div>
                                        </div>
                                        <br>
                                        <div class="row">
                                            <div class="col-xs-4">
                                                <label>Contacto 1</label>
                                                <input type="text" class="form-control input-sm" id="quiebre_contacto1"
                                                    name="quiebre_contacto1" required>
                                            </div>
                                            <div class="col-xs-4">
                                                <label>Celular 1</label>
                                                <input type="number" class="form-control input-sm" id="quiebre_celular1"
                                                    name="quiebre_celular1" required>
                                            </div>
                                            <div class="col-xs-4">
                                                <label>Correo 1</label>
                                                <input type="text" class="form-control input-sm" id="quiebre_correo1"
                                                    name="quiebre_correo1" required>
                                            </div>
                                            <div class="col-xs-4">
                                                <label>Contacto 2 (Opcional)</label>
                                                <input type="text" class="form-control input-sm" id="quiebre_contacto2"
                                                    name="quiebre_contacto2" required>
                                            </div>
                                            <div class="col-xs-4">
                                                <label>Celular 2 (Opcional)</label>
                                                <input type="number" class="form-control input-sm" id="quiebre_celular2"
                                                    name="quiebre_celular2" required>
                                            </div>
                                            <div class="col-xs-4">
                                                <label>Correo 2 (Opcional)</label>
                                                <input type="text" class="form-control input-sm" id="quiebre_correo2"
                                                    name="quiebre_correo2" required>
                                            </div>
                                        </div>
                                        </br>
                                    </div>
                                    <!--- div si la respuesta es titular interesado-->
                                    <div id="comun" style="display:show">
                                        <!--- div de campos en comun-->
                                        <div class="row">
                                            <div class="col-xs-6">
                                                <label>Q lineas con avería</label> <img
                                                    title="Digitar la cantidad de números que presentan problema"
                                                    src="../vista/img/informacion.png" alt="" width="15" height="15">
                                                <input type="number" class="form-control input-sm"
                                                    id="quiebre_numero_problema" name="quiebre_numero_problema"
                                                    required>
                                            </div>



                                            <div class="col-xs-6">
                                                <label>Region</label>
                                                <select name="ncregion" class="form-control" id='ncregion'>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <label for="comment">Comentario</label>
                                                <div class="form-group">
                                                    <textarea class="form-control" rows="5" id="quiebre_observaciones"
                                                        name="quiebre_observaciones"></textarea>
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
        <!--- Modal seguimiento-->
        <div class="modal fade" id="modalSeguimiento" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel "
            aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" id='cerrar1'>
                            <span aria-hidden="true">&times;</span>
                        </button>
                        </br>
                        <div id="datos" class="col-lg-12"
                            style="background-color: #fc9c1c;color: white; font-weight: bold;text-align: center ">
                            SEGUMIENTO QUIEBRE</div>
                    </div>
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#producto" data-toggle="tab">DATO PRODUCTO</a></li>
                        <li><a href="#validacion" data-toggle="tab">GESTIÓN BACK</a></li>
                        <!-- <li><a href="#gestionback" data-toggle="tab">GESTION BACK</a></li> -->
                    </ul>
                    <div class="tab-content">
                        <div class="tab-content tab-pane fade in active " id="producto">
                            <div class="modal-body">
                                <form id="frmQuiebreMovilSeguimiento" method="post">
                                    <input type="hidden" class="form-control input-sm" id="idquiebre" name="idquiebre">
                                    <div class="row">
                                        <!--- div para elegir el tipo de respuesta-->
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-4">
                                            <label>Activacion Servicio</label> <img
                                                title="Fecha desde que el cliente tiene el servicio"
                                                src="../vista/img/informacion.png" alt="" width="15" height="15">
                                            <input type="text" disabled="" class="form-control input-sm"
                                                id="fechaActivacions" name="fechaActivacions">
                                        </div>
                                        <div class="col-xs-3">
                                            <label>RUC</label>
                                            <input type="text" disabled="" class="form-control input-sm" id="ncrucs"
                                                name="ncrucs" required>
                                        </div>
                                        <div class="col-xs-5">
                                            <label>Razon Social</label>
                                            <input type="text" disabled="" class="form-control input-sm"
                                                id="ncrazonsocials" name="ncrazonsocials">
                                        </div>
                                    </div>
                                    <!--- div para elegir el tipo de respuesta-->
                                    <div id="nuevocliente" style="display:show">
                                        <!--- div si la respuesta es titular interesado-->
                                        <div class="row">
                                            <div class="col-xs-6">
                                                <label>Servicio</label>
                                                <input type="text" disabled="" class="form-control input-sm"
                                                    id="quiebre_servicios" name="quiebre_servicios">
                                            </div>
                                            <div class="col-xs-6">
                                                <label>Tipo de avería</label>
                                                <input type="text" disabled="" class="form-control input-sm"
                                                    id="quiebre_tipo_averias" name="quiebre_tipo_averias" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-4">
                                                <label>Problemas con el equipo</label>
                                                <input type="text" disabled="" class="form-control input-sm"
                                                    id="quiebre_problemass" name="quiebre_problemass" required>
                                            </div>
                                            <div class="col-xs-4">
                                                <label>Detalle del equipo</label>
                                                <input type="text" disabled="" class="form-control input-sm"
                                                    id="quiebre_detalles" name="quiebre_detalles" required>
                                            </div>
                                            <div class="col-xs-4">
                                                <label>Back</label>
                                                <input type="text" disabled="" class="form-control input-sm"
                                                    id="quiebre_backs" name="quiebre_backs" required>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <!--- div si la respuesta es titular interesado-->
                                    <div class="row">
                                        <!-- <div class="col-xs-4">
                                                <label>Ticket de atención</label> <img title="Fecha desde que el cliente tiene el servicio" src="../vista/img/informacion.png" alt="" width="15" height="15">
                                                <input type="text" disabled="" name="quiebre_tickets" class="form-control" id='quiebre_tickets'>
                                                </input>
                                            </div> -->
                                        <div class="col-xs-4">
                                            <label>Inicio de Avería</label> <img
                                                title="Fecha desde que el cliente presenta el problema"
                                                src="../vista/img/informacion.png" alt="" width="15" height="15">
                                            <input type="text" disabled="" class="form-control input-sm"
                                                id="fechaInicios" name="fechaInicios">
                                        </div>
                                        <div class="col-xs-4">
                                            <label>Fecha Ticket de atención</label>
                                            <input type="date" disabled="" class="form-control input-sm"
                                                id="fechaTickets" name="fechaTickets">
                                        </div>
                                        <div class="col-xs-4">
                                            <label>Número de ticket</label>
                                            <input type="text" disabled="" class="form-control input-sm"
                                                id="quiebre_numero_tickets" name="quiebre_numero_tickets" required>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="row">
                                        <div class="col-xs-4">
                                            <label>Contacto 1</label>
                                            <input type="text" class="form-control input-sm" id="quiebre_contacto1s"
                                                name="quiebre_contacto1s" required>
                                        </div>
                                        <div class="col-xs-4">
                                            <label>Celular 1</label>
                                            <input type="number" class="form-control input-sm" id="quiebre_celular1s"
                                                name="quiebre_celular1s" required>
                                        </div>
                                        <div class="col-xs-4">
                                            <label>Correo 1</label>
                                            <input type="text" class="form-control input-sm" id="quiebre_correo1s"
                                                name="quiebre_correo1s" required>
                                        </div>
                                        <div class="col-xs-4">
                                            <label>Contacto 2</label>
                                            <input type="text" class="form-control input-sm" id="quiebre_contacto2s"
                                                name="quiebre_contacto2s" required>
                                        </div>
                                        <div class="col-xs-4">
                                            <label>Celular 2</label>
                                            <input type="number" class="form-control input-sm" id="quiebre_celular2s"
                                                name="quiebre_celular2s" required>
                                        </div>
                                        <div class="col-xs-4">
                                            <label>Correo 2</label>
                                            <input type="text" class="form-control input-sm" id="quiebre_correo2s"
                                                name="quiebre_correo2s" required>
                                        </div>
                                    </div>
                                    <div id="comun" style="display:show">
                                        <!--- div de campos en comun-->
                                        <div class="row">
                                            <div class="col-xs-6">
                                                <label>Q líneas con avería</label> <img
                                                    title="Cantidad de números que presentan problema"
                                                    src="../vista/img/informacion.png" alt="" width="15" height="15">
                                                <input type="number" class="form-control input-sm"
                                                    id="quiebre_numero_problemas" name="quiebre_numero_problemas"
                                                    required>
                                            </div>
                                            <div class="col-xs-6">
                                                <label>Region</label>
                                                <input type="text" disabled="" name="ncregions"
                                                    class="form-control input-sm" id='ncregions'>
                                                </input>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <label for="comment">Comentario</label>
                                                <div class="form-group">
                                                    <textarea class="form-control" rows="5" id="ncobservacioness"
                                                        name="ncobservacioness"></textarea>
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
                                <button type="button" class="btn btn-warning" id="btnActualizar">Actualizar</button>
                            </div>
                        </div>
                        <div class="tab-content tab-pane" id="validacion">
                            <div class="modal-body">
                                <form id="frmQuiebreValidacion" method="post">
                                    <div class="row">
                                        <div class="col-xs-6">
                                            <label>RUC</label>
                                            <input type="text" disabled="" class="form-control input-sm" id="ncrucss"
                                                name="ncrucss">
                                        </div>
                                        <div class="col-xs-6">
                                            <label>Razón Social</label>
                                            <input type="text" disabled="" class="form-control input-sm" id="ncrazonss"
                                                name="ncrazonss">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-4">
                                            <label>Servicio</label>
                                            <input type="text" disabled="" class="form-control input-sm"
                                                id="quiebre_servicioss" name="quiebre_servicioss">
                                        </div>
                                        <div class="col-xs-4">
                                            <label>Tipo de Avería</label>
                                            <input type="text" disabled="" class="form-control input-sm"
                                                id="quiebre_tipo_averiass" name="quiebre_tipo_averiass">
                                        </div>
                                        <div class="col-xs-4">
                                            <label>Caso SF</label>
                                            <input type="text" disabled="" class="form-control input-sm" id="casosf"
                                                name="casosf">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-4">
                                            <label>Fecha Validacion</label>
                                            <input type="date" disabled="" class="form-control input-sm"
                                                id="fechavalidacions" name="fechavalidacions">
                                        </div>
                                        <div class="col-xs-4">
                                            <label>Validador</label>
                                            <input type="text" class="form-control input-sm" id="ncvalidadors"
                                                name="ncvalidadors" disabled="">
                                        </div>
                                        <div class="col-xs-4">
                                            <label>Estado del caso</label>
                                            <input type="text" disabled="" class="form-control input-sm"
                                                id="ncvalidacions" name="ncvalidacions">
                                        </div>
                                    </div>
                                    <div id="comun" style="display:show">
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <label for="comment">Comentario Validador:</label>
                                                <div class="form-group">
                                                    <textarea class="form-control" rows="5" id="ncobservacionesvals"
                                                        name="ncobservacionesvals" disabled=""></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
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
    <script type="text/javascript" src="../vista/js/quiebre.js"></script>
</body>

</html>
<?php
} else {
    header('Location: /SAFEAPP/vista/login.php');
}
