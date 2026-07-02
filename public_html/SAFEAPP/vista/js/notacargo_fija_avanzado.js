$(document).ready(function () {
  TraerDatosAnoMes();
});

$("#suptipocom").click(function () {
  TraerDatosDescripcion();
});

$("#descripcioncom").click(function () {
  TraerDatoscf();
});

$("#cerrar1").click(function () {
  $("#suptipocom").empty().append("whatever");
  $("#descripcioncom").empty().append("whatever");
  $("#ncregion").empty().append("whatever");
  $("#ncback").empty().append("whatever");
  $("#nccargofijo").val("");
  $("#ncruc").val("");
  $("#ncrazonsocial").val("");
  $("#ncnomcontacto").val("");
  $("#nctelefono1").val("");
  $("#nctelefono2").val("");
  $("#ncdireccion").val("");
  $("#nccoordenadas").val("");
  $("#ncobservaciones").val("");
});

$("#cerrar2").click(function () {
  $("#suptipocom").empty().append("whatever");
  $("#descripcioncom").empty().append("whatever");
  $("#ncregion").empty().append("whatever");
  $("#ncback").empty().append("whatever");
  $("#nccargofijo").val("");
  $("#ncruc").val("");
  $("#ncrazonsocial").val("");
  $("#ncnomcontacto").val("");
  $("#nctelefono1").val("");
  $("#nctelefono2").val("");
  $("#ncdireccion").val("");
  $("#nccoordenadas").val("");
  $("#ncobservaciones").val("");
});

$("#btnRegistrar").click(function () {
  if (
    $("#suptipocom").val() == "0" ||
    $("#descripcioncom").val() == "0" ||
    $("#ncregion").val() == "0" ||
    $("#ncback").val() == "0" ||
    $("#nccargofijo").val() == "0" ||
    $("#nccargofijo").val() == "" ||
    $("#ncnomcontacto").val() == "" ||
    $("#nctelefono1").val() == "" ||
    $("#ncdireccion").val() == ""
  ) {
    alertify.error("REVISE LOS DATOS");
  } else {
    datos = $("#frmNCF").serialize();
    $.ajax({
      type: "POST",
      data: datos,
      url: "../procesos/agregarncfnvoAv.php",
      success: function (r) {
        if (r != 1) {
          alertify.success("Registrado con exito");

          $("#modalEditar").modal("hide");

          $("#suptipocom").empty().append("whatever");
          $("#descripcioncom").empty().append("whatever");
          $("#ncregion").empty().append("whatever");
          $("#ncback").empty().append("whatever");
          $("#nccargofijo").val("");
          $("#ncruc").val("");
          $("#ncrazonsocial").val("");
          $("#ncnomcontacto").val("");
          $("#nctelefono1").val("");
          $("#nctelefono2").val("");
          $("#ncdireccion").val("");
          $("#nccoordenadas").val("");
          $("#ncobservaciones").val("");
          $("#nctecnlogia").val("");
          $("#nvoruc").val("");
          var estado = $("#ncestado").val();
          var idusu = $("#idusu").val();
          var ano = $("#ncano").val();
          var periodo = $("#ncperiodo").val();
          $("#tablaDatatable").load(
            "../capa_presentacion/tabla_nc_fija_avanzado.php?ano=" +
              ano +
              "&periodo=" +
              periodo +
              "&estado=" +
              estado +
              "&idusu=" +
              idusu
          );
        } else {
          alertify.error("Fallo al agregar");
        }
      },
    });
  }
});

$("#btnagregar").click(function () {
  $("#ncback").empty().append("whatever");
  MostrarBack();
  $("#ncregion").empty().append("whatever");
  TraerDatosSuptipo();
  MostrarRegion();

  var nvoruc = $("#nvoruc").val();
  var ncfqlineas = $("#ncqlineas").val();

  if (nvoruc.length === 11) {
    datos = $("#frmagregar").serialize();
    $.ajax({
      type: "POST",
      data: datos,
      url: "../procesos/BuscarRuc.php",
      success: function (data) {
        datos = jQuery.parseJSON(data);
        console.log(datos);
        if (datos["estado"] === "1" || datos["estado"] === "0") {
          $("#modalEditar").modal("show");

          $("#ncruc").val(nvoruc);
          $("#ncrazonsocial").val(datos["razon_social"]);
          $("#ncfruc").val(nvoruc);
          $("#ncfrazonsocial").val(datos["razon_social"]);
          $("#ncfqlineas").val("1");
        } else {
          alertify.error("CLIENTE NO REGISTRADO: IR A FUNNEL +AGREGAR CLIENTE");
        }
      },
    });
  } else {
    alertify.error("RUC DEBE TENER 11 DIGITOS");
  }
});

$("#btnfiltrar").click(function () {
  var estado = $("#ncestado").val();
  var idusu = $("#idusu").val();
  var ano = $("#ncano").val();
  var periodo = $("#ncperiodo").val();
  $("#tablaDatatable").load(
    "../capa_presentacion/tabla_nc_fija_avanzado.php?ano=" +
      ano +
      "&periodo=" +
      periodo +
      "&estado=" +
      estado +
      "&idusu=" +
      idusu
  );
});

function TraerDatosSuptipo() {
  $("#suptipocom").empty().append("whatever");
  $.ajax({
    url: "../procesos/obtenDatosSuptipoAv.php",
    success: function (data) {
      datos = jQuery.parseJSON(data);

      if (datos.length > 0) {
        $("#suptipocom").append("<option value = '0'> --Elegir-- </option>");
        var i;
        for (i = 0; i < datos.length; i++) {
          $("#suptipocom").append(
            "<option value = '" + datos[i][0] + "'>" + datos[i][0] + "</option>"
          );
        }
      }
    },
  });
}

function TraerDatosDescripcion() {
  $("#descripcioncom").empty().append("whatever");
  datos = $("#frmNCF").serialize();
  $.ajax({
    type: "POST",
    data: datos,
    url: "../procesos/obtenDatosDescripcionAv.php",
    success: function (data) {
      datos = jQuery.parseJSON(data);
      if (datos.length > 0) {
        $("#descripcioncom").append(
          "<option value = '0'> --Elegir-- </option>"
        );
        var i;
        for (i = 0; i < datos.length; i++) {
          $("#descripcioncom").append(
            "<option value = '" + datos[i][0] + "'>" + datos[i][0] + "</option>"
          );
        }
      }
    },
  });
}

function TraerDatoscf() {
  datos = $("#frmNCF").serialize();
  $.ajax({
    type: "POST",
    data: datos,
    url: "../procesos/obtenDatoscfAv.php",
    success: function (data) {
      datos = jQuery.parseJSON(data);
      $("#nccargofijo").val(datos["cf"]);
    },
  });
}

function MostrarBack() {
  $.ajax({
    url: "../procesos/obtenDatosback.php",
    success: function (r) {
      datos = jQuery.parseJSON(r);
      if (datos.length > 0) {
        $("#ncback").append("<option value = '0'> --Elegir-- </option>");
        var i;
        for (i = 0; i < datos.length; i++) {
          $("#ncback").append(
            "<option value = '" + datos[i][1] + "'>" + datos[i][1] + "</option>"
          );
        }
      }
    },
  });
}

function MostrarRegion() {
  $.ajax({
    url: "../procesos/obtenDatosUsuarioRegion.php",
    success: function (r) {
      datos = jQuery.parseJSON(r);
      if (datos.length > 0) {
        $("#ncregion").append("<option value = '0'> --Elegir-- </option>");
        var i;
        for (i = 0; i < datos.length; i++) {
          $("#ncregion").append(
            "<option value = '" + datos[i][0] + "'>" + datos[i][1] + "</option>"
          );
        }
      }
    },
  });
}

function TraerDatosAnoMes() {
  var estado = $("#ncestado").val();
  var idusu = $("#idusu").val();
  var fecha = new Date();
  var ano = fecha.getFullYear();

  console.log(fecha);
  $("#ncano").val(ano);

  var periodo = fecha.getMonth() + 1;

  if (periodo > 9) {
    $("#ncperiodo").val(periodo);
  } else {
    $("#ncperiodo").val("0" + periodo);
  }

  $("#tablaDatatable").load(
    "../capa_presentacion/tabla_nc_fija_avanzado.php?ano=" +
      ano +
      "&periodo=" +
      periodo +
      "&estado=" +
      estado +
      "&idusu=" +
      idusu
  );
}

function TraerDatosTabla(idcargo) {
  $.ajax({
    type: "POST",
    data: "idcargo=" + idcargo,
    url: "../procesos/obtenNotacargo_Avanzadoback.php",
    success: function (data) {
      datos = jQuery.parseJSON(data);
      $("#idcargo").val(idcargo);
      $("#ncqlineass").val(datos["q_lineas"]);
      $("#ncmodalidads").val(datos["modalidad"]);
      $("#suptipocoms").val(datos["subtipo"]);
      $("#descripcioncoms").val(datos["descripcion"]);
      $("#nccargofijos").val(datos["cargo_fijo"]);
      $("#ncrucs").val(datos["ruc"]);
      $("#ncrazonsocials").val(datos["razon_social"]);
      $("#ncnomcontactos").val(datos["contacto"]);
      $("#nctelefono1s").val(datos["telefono1"]);
      $("#nctelefono2s").val(datos["telefono2"]);
      $("#nctelefono1m").val(datos["telefono1"]);
      $("#nctelefono2m").val(datos["telefono2"]);
      $("#ncdireccions").val(datos["direccion"]);
      $("#nccoordenadass").val(datos["coordenadas"]);
      $("#ncregions").val(datos["zonal"]);
      $("#ncpersonals").val(datos["personal"]);
      $("#ncobservacionesejes").val(datos["comentario"]);
      $("#ncestados").val(datos["estado"]);
      $("#ncsupervisors").val(datos["supervisor"]);
      $("#fechaingresos").val(datos["fecha_ingreso"]);
      $("#fechavalidacions").val(datos["fecha_validacion"]);
      $("#ncvalidacions").val(datos["validacion"]);
      $("#ncvalidadors").val(datos["validador"]);
      $("#ncobservacionesvals").val(datos["comentario_validador"]);
      $("#ncestadoacts").val(datos["estado_actual"]);
      $("#ncpeticions").val(datos["peticion"]);
      $("#nccontratas").val(datos["contrata"]);
      $("#ncfechaliqs").val(datos["fecha_liquidacion"]);
      $("#ncfechaacts").val(datos["fecha_actualizacion"]);
      $("#ncnunasignados").val(datos["nasignado"]);
      $("#nccomentariobacks").val(datos["comentario_back"]);
      $("#nctecnlogias").val(datos["tecnologia"]);
      $("#back").val(datos["back"]);

      $("#ncoportunidads").val(datos["caso_sf"]);
    },
  });
}

$("#btnActualizar").click(function () {
  datos = $("#frmNCFS").serialize();
  $.ajax({
    type: "POST",
    data: datos,
    url: "../procesos/agregarncfavanzadocomentario.php",
    success: function (r) {
      if (r != 1) {
        alertify.success("Actualizado con exito");

        $("#modalSeguimiento").modal("hide");

        var estado = $("#ncestado").val();
        var idusu = $("#idusu").val();
        var ano = $("#ncano").val();
        var periodo = $("#ncperiodo").val();
        $("#tablaDatatable").load(
          "../capa_presentacion/tabla_nc_fija_avanzado.php?ano=" +
            ano +
            "&periodo=" +
            periodo +
            "&estado=" +
            estado +
            "&idusu=" +
            idusu
        );
      } else {
        alertify.error("Fallo al actualizar");
      }
    },
  });
});
