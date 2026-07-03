<?php

session_start();

if (isset($_SESSION['idusu'])) {
    $idusu = $_SESSION['idusu'];
    require_once '../capa_conexion/conexion.php';
    $obj = new conectar();
    $conexion = $obj->conexion();
    $sqlsup = "SELECT id_supervisor from usuario where id_usuario='$idusu'";
    $resultsup = mysqli_query($conexion, $sqlsup);
    $versup = mysqli_fetch_array($resultsup);
    $idsupervisor = $versup[0];
    $conexion->set_charset('utf8');
    $tienda = $versup[0];
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
                style="background-color: #00a65a;color: white; font-weight: bold;text-align: center ">NOTA DE CARGO
                MOVIL</div>
            </br>
            <form class="form form-horizontal" id='frmagregar' action="../procesos/rephistoricousuexcel.php"
                method="post">
                <ol class="breadcrumb">
                    <div class="col-xs-4">
                        <input type="text" class="form-control input-sm" id="nvoruc" name="nvoruc"
                            placeholder="DIGITA EL RUC" required>
                    </div>
                    <div class="col-lg-2">
                        <button type="button" class="btn btn-success" id="btnagregar">AGREGAR</button>
                    </div>
                </ol>
            </form>
            </br>
            <form class="form form-horizontal" id='frmMovil' action="../procesos/repExportarNotaCargoMovil.php"
                method="post">
                <input type="hidden" class="form-control input-sm" id="tiendaf" name="tiendaf"
                    value="<?php echo $tienda ?>">
                <input type="hidden" class="form-control input-sm" id="idusu" name="idusu" value="<?php echo $idusu ?>">
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
                            <label>ESTADO</label>
                        </div>
                        <div class="col-xs-2">
                            <select name="ncestado" class="form-control" id='ncestado'>
                                <option value="PENDIENTE">PENDIENTE</option>
                                <option value="PROCESANDO">PROCESANDO</option>
                                <option value="INGRESADO">INGRESADO</option>
                                <option value="DELIVERY">DELIVERY</option>
                                <option value="ACTIVADO">ACTIVADO</option>
                                <option value="CAIDA">CAIDA</option>
                                <option value="BOLSA">BOLSA</option>
                                <option value="SALIDAANTICIPADA">SALIDA ANTICIPADA</option>
                                <option value="ADR">ADR</option>
                                <option value="TODO" selected>TODO</option>
                            </select>
                        </div>
                        <div class="col-xs-2">
                            <button type="button" class="btn btn-success" id="btnfiltrar">FILTRAR</button>
                        </div>
                        <div class="col-xs-2">
                            <button type="submit" class="btn btn-success" id="btnexportar">EXPORTAR</button>
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
                            style="background-color: #00a65a;color: white; font-weight: bold;text-align: center ">NOTA
                            DE CARGO MOVIL</div>
                    </div>
                    <div class="tab-content">
                        <div class="tab-content tab-pane fade in active " id="llamada">
                            <div class="modal-body">
                                <form id="frmNCM" method="POST">
                                    <div class="row">
                                        <!--- div para elegir el tipo de respuesta-->
                                        <div class="col-xs-4">
                                            <input type="hidden" class="form-control input-sm" id="idusu" name="idusu"
                                                value="<?php echo $idusu ?>">
                                            <input type="hidden" class="form-control input-sm" id="idsupervisor"
                                                name="idsupervisor" value="<?php echo $idsupervisor ?>">
                                            <input type="hidden" class="form-control input-sm" id="tienda" name="tienda"
                                                value="<?php echo $tienda ?>">

                                            <input type="hidden" class="form-control input-sm" id="ncfruc"
                                                name="ncfruc">
                                            <input type="hidden" class="form-control input-sm" id="ncfrazonsocial"
                                                name="ncfrazonsocial">
                                            <label>Fecha:</label>
                                            <input type="date" disabled="" class="form-control input-sm" id="fechahoy"
                                                name="fechahoy"
                                                value="<?php

                                                date_default_timezone_set('America/Lima');
                                                $hoy = date('Y-m-d');
                                                echo $hoy ?>"><br>
                                        </div>
                                        <div class="col-xs-4">
                                            <label>Q lineas</label>
                                            <input type="number" class="form-control input-sm" id="ncqlineas"
                                                name="ncqlineas" required>
                                        </div>
                                        <div class="col-xs-4">
                                            <label>Modalidad</label>
                                            <select name="ncmodalidad" class="form-control" id='ncmodalidad'>
                                                <option value="ALTA" selected>ALTA</option>
                                                <option value="PORTABILIDAD">PORTABILIDAD</option>
                                                <option value="BAM">BAM</option>
                                            </select>
                                        </div>
                                    </div>
                                    <!--- div para elegir el tipo de respuesta-->
                                    <div id="nuevocliente" style="display:show">
                                        <!--- div si la respuesta es titular interesado-->
                                        <div class="row">
                                            <div class="col-xs-6">
                                                <label>Cargo Fijo</label>
                                                <input type="text" class="form-control input-sm" id="nccargofijo"
                                                    name="nccargofijo">
                                            </div>
                                            <div class="col-xs-6">
                                                <label>Oportunidad</label>
                                                <input type="text" class="form-control input-sm" id="ncoportunidad"
                                                    name="ncoportunidad" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-4">
                                                <label>RUC</label>
                                                <input type="text" disabled="" class="form-control input-sm" id="ncruc"
                                                    name="ncruc" required>
                                            </div>
                                            <div class="col-xs-8">
                                                <label>Razon Social</label>
                                                <input type="text" disabled="" class="form-control input-sm"
                                                    id="ncrazonsocial" name="ncrazonsocial">
                                            </div>
                                        </div>
                                        </br>
                                    </div>
                                    <!--- div si la respuesta es titular interesado-->
                                    <div id="titular" style="display:show">
                                        <!--- div si la respuesta es titular interesado-->
                                        <div class="row">
                                            <div class="col-xs-6">
                                                <label>Nombre contacto</label>
                                                <input type="text" class="form-control input-sm" id="ncnomcontacto"
                                                    name="ncnomcontacto">
                                            </div>
                                            <div class="col-xs-3">
                                                <label>Telefono1</label>
                                                <input type="number" class="form-control input-sm" id="nctelefono1"
                                                    name="nctelefono1" required>
                                            </div>
                                            <div class="col-xs-3">
                                                <label>Telefono2</label>
                                                <input type="number" class="form-control input-sm" id="nctelefono2"
                                                    name="nctelefono2" required>
                                            </div>
                                        </div>
                                        </br>
                                    </div>
                                    <!--- div si la respuesta es titular interesado-->
                                    <div id="comun" style="display:show">
                                        <!--- div de campos en comun-->
                                        <div class="row">
                                            <div class="col-xs-4">
                                                <label>Probabilidad de cierre</label>
                                                <select name="ncnegociacion" class="form-control" id='ncnegociacion'>
                                                    <option value="25" selected>25 %</option>
                                                    <option value="50">50 %</option>
                                                    <option value="75">75 %</option>
                                                    <option value="100">100 %</option>
                                                    <option value="CAIDA">CAIDA</option>
                                                </select>
                                            </div>
                                            <div class="col-xs-4">
                                                <label>Fecha Cierre</label>
                                                <input type="date" class="form-control input-sm" id="ncestimada"
                                                    name="ncestimada">
                                            </div>
                                            <!-- <div class="col-xs-4">
                                                    <label>Region</label>
                                                    <select name="nczonaltdp" class="form-control" id='nczonaltdp'>
                                                    </select>
                                                </div> -->
                                            <div class="col-xs-4">
                                                <label>Región</label>
                                                <select name="nczonaltdp" class="form-control" id='nczonaltdp'>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-4">
                                                <label>Ejecutivo TDP</label>
                                                <select name="ncejecutivotdp" class="form-control" id='ncejecutivotdp'>
                                                </select>
                                            </div>
                                            <div class="col-xs-4">
                                                <label>Tipo de producto</label>
                                                <select name="nctipoproducto" class="form-control" id='nctipoproducto'>
                                                    <option value="M4" selected>M4</option>
                                                    <option value="COMBO">COMBO</option>
                                                    <option value="M4/COMBO">M4 / COMBO</option>
                                                </select>
                                            </div>
                                            <div class="col-xs-4">
                                                <label>Fase</label>
                                                <select name="ncetapa" class="form-control" id="ncetapa">
                                                    <option value="F1-PERDIDA" selected>F1 PERDIDA</option>
                                                    <option value="F1-GANADA">F1 GANADA</option>
                                                    <option value="F2-GESTION">F2 GESTION</option>
                                                    <option value="F3-NEGOCIACION">F3 NEGOCIACION</option>
                                                    <option value="F4-DISENO">F4 DISEÑO</option>
                                                    <option value="F5-DEFINICION">F5 DEFINICION</option>
                                                </select>
                                            </div>
                                        </div>
                                        <!-- <div class="row">
                                                <div class="col-xs-4">
                                                    <label>Tipo de etapa</label>
                                                    <select name="nctipoetapa" class="form-control" id="nctipoetapa">
                                                    </select>
                                                </div>
                                            </div> -->
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <label for="comment">Comentario:</label>
                                                <div class="form-group">
                                                    <textarea class="form-control" rows="5" id="ncobservaciones"
                                                        name="ncobservaciones"></textarea>
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
                            style="background-color: #00a65a;color: white; font-weight: bold;text-align: center ">
                            SEGUIMIENTO NOTA DE CARGO MOVIL</div>
                    </div>
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#producto" data-toggle="tab">DATO PRODUCTO</a></li>
                        <li><a href="#validacion" data-toggle="tab">DATOS VALIDACION</a></li>
                        <li><a href="#gestionback" data-toggle="tab">GESTION BACK</a></li>
                        <li><a href="#liquidacion" data-toggle="tab">LIQUIDACION</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-content tab-pane fade in active " id="producto">
                            <div class="modal-body">
                                <form id="frmNCMS" method="post">
                                    <input type="hidden" class="form-control input-sm" id="idcargo" name="idcargo">
                                    <div class="row">
                                        <!--- div para elegir el tipo de respuesta-->
                                        <div class="col-xs-4">
                                            <label>Fecha Ingreso:</label>
                                            <input type="text" disabled="" class="form-control input-sm"
                                                id="fechaingresos" name="fechaingresos">
                                        </div>
                                        <div class="col-xs-4">
                                            <label>Q lineas</label>
                                            <input type="number" class="form-control input-sm" id="ncqlineass"
                                                name="ncqlineass">
                                        </div>
                                        <div class="col-xs-4">
                                            <label>Modalidad</label>
                                            <input type="text" disabled="" class="form-control input-sm"
                                                id="ncmodalidads" name="ncmodalidads">
                                        </div>
                                    </div>
                                    <!--- div para elegir el tipo de respuesta-->
                                    <div id="nuevocliente" style="display:show">
                                        <!--- div si la respuesta es titular interesado-->
                                        <div class="row">
                                            <div class="col-xs-4">
                                                <label>Cargo Fijo</label>
                                                <input type="text" class="form-control input-sm" id="nccargofijos"
                                                    name="nccargofijos">
                                            </div>
                                            <div class="col-xs-4">
                                                <label>Telefono1</label>
                                                <input type="text" class="form-control input-sm" id="nctelefono1m"
                                                    name="nctelefono1m" required>
                                            </div>
                                            <div class="col-xs-4">
                                                <label>Telefono2</label>
                                                <input type="text" class="form-control input-sm" id="nctelefono2m"
                                                    name="nctelefono2m" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-4">
                                                <label>RUC</label>
                                                <input type="text" disabled="" class="form-control input-sm" id="ncrucs"
                                                    name="ncrucs" required>
                                            </div>
                                            <div class="col-xs-8">
                                                <label>Razon Social</label>
                                                <input type="text" disabled="" class="form-control input-sm"
                                                    id="ncrazonsocials" name="ncrazonsocials">
                                            </div>
                                        </div>
                                    </div>
                                    <!--- div si la respuesta es titular interesado-->
                                    <div id="comun" style="display:show">
                                        <!--- div de campos en comun-->
                                        <div class="row">
                                            <div class="col-xs-6">
                                                <label>Region</label>
                                                <input type="text" disabled="" class="form-control input-sm"
                                                    id="nczonaltdps" name="nczonaltdps">
                                            </div>
                                            <div class="col-xs-6">
                                                <label>EJECUTIVO</label>
                                                <input type="text" disabled="" class="form-control input-sm"
                                                    id="ncpersonals" name="ncpersonals">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-4">
                                                <label>Nivel Negociación</label>
                                                <select name="ncnegociacions" class="form-control" id='ncnegociacions'>
                                                    <option value="25" selected>25 %</option>
                                                    <option value="50">50 %</option>
                                                    <option value="75">75 %</option>
                                                    <option value="100">100 %</option>
                                                    <option value="CAIDA">CAIDA</option>
                                                </select>
                                            </div>
                                            <div class="col-xs-4">
                                                <label>Fecha estimada</label>
                                                <input type="date" class="form-control input-sm" id="ncestimadas"
                                                    name="ncestimadas">
                                            </div>
                                            <div class="col-xs-4">
                                                <label>SUPERVISOR</label>
                                                <input type="text" disabled="" class="form-control input-sm"
                                                    id="ncsupervisors" name="ncsupervisors">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-4">
                                                <label>Ejecutivo TDP</label>
                                                <input type="text" disabled="" class="form-control input-sm"
                                                    id="ncejecutivotdps" name="ncejecutivotdps">
                                            </div>
                                            <div class="col-xs-4">
                                                <label>Tipo de producto</label>
                                                <input type="text" disabled="" class="form-control input-sm"
                                                    id="nctipoproductos" name="nctipoproductos">
                                            </div>
                                            <div class="col-xs-4">
                                                <label>Fase</label>
                                                <select name="ncetapab" class="form-control" id="ncetapab">
                                                    <option value="F1-PERDIDA" selected>F1 PERDIDA</option>
                                                    <option value="F1-GANADA">F1 GANADA</option>
                                                    <option value="F2-GESTION">F2 GESTION</option>
                                                    <option value="F3-NEGOCIACION">F3 NEGOCIACION</option>
                                                    <option value="F4-DISENO">F4 DISEÑO</option>
                                                    <option value="F5-DEFINICION">F5 DEFINICION</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <!-- <div class="col-xs-4">
                                                    <label>Tipo Etapa</label>
                                                    <input type="text" disabled="" class="form-control input-sm" id="nctipoetapas" name="nctipoetapas">
                                                </div> -->

                                            <div class="col-xs-4">
                                                <label>Caso FWA</label>
                                                <input type="text" disabled="" class="form-control input-sm"
                                                    id="nccasofwas" name="nccasofwas">
                                            </div>
                                            <div class="col-xs-4">
                                                <label>Oportunidad</label>
                                                <input type="text" class="form-control input-sm" id="ncoportunidade"
                                                    name="ncoportunidade">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <label for="comment">Comentario Ejecutivo:</label>
                                                <div class="form-group">
                                                    <textarea class="form-control" rows="5" id="ncobservacionesejes"
                                                        name="ncobservacionesejes"></textarea>
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
                        <div class="tab-content tab-pane  " id="validacion">
                            <div class="modal-body">
                                <form id="frmNCMV" method="post">
                                    <div class="row">
                                        <div class="col-xs-6">
                                            <label>Nombre contacto</label>
                                            <input type="text" disabled="" class="form-control input-sm"
                                                id="ncnomcontactos" name="ncnomcontactos">
                                        </div>
                                        <div class="col-xs-3">
                                            <label>Telefono1</label>
                                            <input type="text" disabled="" class="form-control input-sm"
                                                id="nctelefono1s" name="nctelefono1s" required>
                                        </div>
                                        <div class="col-xs-3">
                                            <label>Telefono2</label>
                                            <input type="text" disabled="" class="form-control input-sm"
                                                id="nctelefono2s" name="nctelefono2s" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <!--- div para elegir el tipo de respuesta-->
                                        <div class="col-xs-4">
                                            <label>Fecha Validacion:</label>
                                            <input type="text" disabled="" class="form-control input-sm"
                                                id="fechavalidacions" name="fechavalidacions"><br>
                                        </div>
                                        <div class="col-xs-4">
                                            <label>VALIDADOR</label>
                                            <input type="text" class="form-control input-sm" id="ncvalidadors"
                                                name="ncvalidadors" disabled="">
                                        </div>
                                        <div class="col-xs-4">
                                            <label>VALIDACION</label>
                                            <input type="text" disabled="" class="form-control input-sm"
                                                id="ncvalidacions" name="ncvalidacions">
                                        </div>
                                    </div>
                                    <!--- div para elegir el tipo de respuesta-->
                                    <div id="comun" style="display:show">
                                        <!--- div de campos en comun-->
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
                                    <!--- div de campos en comun-->
                                </form>
                            </div>
                        </div>
                        <div class="tab-content tab-pane  " id="gestionback">
                            <div class="modal-body">
                                <form id="frmNCMB" method="post">
                                    <div class="row">
                                        <!--- div para elegir el tipo de respuesta-->
                                        <div class="col-xs-4">
                                            <label>Estado Actual</label>
                                            <input type="text" disabled="" class="form-control input-sm"
                                                id="ncestadoacts" name="ncestadoacts">
                                        </div>
                                        <div class="col-xs-4">
                                            <label>Fecha Actualización:</label>
                                            <input type="text" disabled="" class="form-control input-sm"
                                                id="ncfechaacts" name="ncfechaacts">
                                        </div>
                                        <div class="col-xs-4">
                                            <label>Oportunidad</label>
                                            <input type="text" class="form-control input-sm" id="ncoportunidads"
                                                name="ncoportunidads" disabled="" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!--- div para elegir el tipo de respuesta-->
                                        <div class="col-xs-4">
                                            <label>Ciclo</label>
                                            <input type="text" disabled="" class="form-control input-sm" id="ncciclo"
                                                name="ncciclo">
                                        </div>
                                        <div class="col-xs-4">
                                            <label>Cuenta Financiera</label>
                                            <input type="text" disabled="" class="form-control input-sm" id="nccuentaf"
                                                name="nccuentaf">
                                        </div>
                                    </div>

                                    <!--- div para elegir el tipo de respuesta-->
                                    <div id="comun" style="display:show">
                                        <!--- div de campos en comun-->
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <label for="comment">Comentario Back Office:</label>
                                                <div class="form-group">
                                                    <textarea class="form-control" rows="20" id="nccomentariobacks"
                                                        name="nccomentariobacks" disabled=""></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--- div de campos en comun-->
                                </form>
                            </div>
                        </div>
                        <div class="tab-content tab-pane  " id="liquidacion">
                            <div class="modal-body">
                                <form id="frmNCML" method="post">
                                    <div class="row">
                                        <div class="col-xs-6">
                                            <label>Comisión</label>
                                            <input type="text" disabled="" class="form-control input-sm"
                                                id="nccomisionl" name="nccomisionl">
                                        </div>
                                        <div class="col-xs-3">
                                            <label>Observación</label>
                                            <input type="text" disabled="" class="form-control input-sm"
                                                id="ncobservacionl" name="ncobservacionl">
                                        </div>
                                        <div class="col-xs-3">
                                            <label>Telefono2</label>
                                            <input type="text" disabled="" class="form-control input-sm"
                                                id="ncdescuentol" name="ncdescuentol">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <!--- div para elegir el tipo de respuesta-->
                                        <div class="col-xs-4">
                                            <label>Fecha Liquidación:</label>
                                            <input type="text" disabled="" class="form-control input-sm"
                                                id="fechaliquidacionl" name="fechaliquidacionl"><br>
                                        </div>
                                    </div>
                                    <!--- div para elegir el tipo de respuesta-->
                                    <div id="comun" style="display:show">
                                        <!--- div de campos en comun-->
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <label for="comment">Comentario Liquidador:</label>
                                                <div class="form-group">
                                                    <textarea class="form-control" rows="5" id="ncobservacionesl"
                                                        name="ncobservacionesl" disabled=""></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--- div de campos en comun-->
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
    <script type="text/javascript" src="js/notacargo_movil.js"></script>
</body>

</html>
<?php
} else {
    header('Location: /SAFEAPP/vista/login.php');
}
