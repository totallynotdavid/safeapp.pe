$(document).ready(function () {
  TraerDatosAnoMes();
});

$("#btnexportar").click(function () {
  $("#frmMovil").submit();
});

$("#cerrar1").click(function () {
  // $("#nczonaltdp").empty().append("whatever");
  $("#nccargofijo").val("");
  $("#ncruc").val("");
  $("#ncrazonsocial").val("");
  $("#ncnomcontacto").val("");
  $("#nctelefono1").val("");
  $("#nctelefono2").val("");
  $("#ncobservaciones").val("");
  $("#ncoportunidad").val("");
  $("#ncqlineas").val("");
  $("#nczonaltdp").empty().append("whatever");
  $("#ncetapa").empty().append("whatever");
  $("#nctipoetapa").empty().append("whatever");
  $("#ncejecutivotdp").empty().append("whatever");
});

$("#cerrar2").click(function () {
  // $("#nczonaltdp").empty().append("whatever");
  $("#nccargofijo").val("");
  $("#ncruc").val("");
  $("#ncrazonsocial").val("");
  $("#ncnomcontacto").val("");
  $("#nctelefono1").val("");
  $("#nctelefono2").val("");
  $("#ncobservaciones").val("");
  $("#ncoportunidad").val("");
  $("#ncqlineas").val("");
  $("#nczonaltdp").empty().append("whatever");
  $("#ncetapa").empty().append("whatever");
  $("#nctipoetapa").empty().append("whatever");
  $("#ncejecutivotdp").empty().append("whatever");
});

$("#btnRegistrar").click(function () {
  if (
    $("#ncqlineas").val() == "0" ||
    $("#nczonaltdp").val() == "0" ||
    $("#nccargofijo").val() == "0" ||
    $("#nccargofijo").val() == "" ||
    $("#ncnomcontacto").val() == "" ||
    $("#nctelefono1").val() == "" ||
    $("#ncdireccion").val() == "" ||
    $("#ncestimada").val() == "" ||
    $("#ncoportunidad").val() == "" ||
    $("#ncejecutivotdp").val() == "" ||
    $("#ncejecutivotdp").val() == "0" ||
    $("#nctipoproducto").val() == "0" ||
    $("#nctipoproducto").val() == "" ||
    $("#ncetapa").val() == "0" ||
    $("#ncetapa").val() == ""
  ) {
    alertify.error("REVISE LOS DATOS");
  } else {
    datos = $("#frmNCM").serialize();
    $.ajax({
      type: "POST",
      data: datos,
      url: "../procesos/agregarncmnvo.php",
      success: function (r) {
        console.log(datos);
        if (r != 1) {
          alertify.success("Registrado con exito");

          $("#modalEditar").modal("hide");
          $("#nczonaltdp").empty().append("whatever");
          $("#nccargofijo").val("");
          $("#ncruc").val("");
          $("#ncrazonsocial").val("");
          $("#ncnomcontacto").val("");
          $("#nctelefono1").val("");
          $("#nctelefono2").val("");
          $("#ncobservaciones").val("");
          $("#ncoportunidad").val("");
          $("#ncqlineas").val("");
          $("#nvoruc").val("");
          $("#ncestimada").val("");
          $("#nczonaltdp").val("");
          $("#ncejecutivotdp").val("");
          $("#nctipoproducto").val("");
          $("#ncetapa").val("");
          $("#nctipoetapa").val("");
          var estado = $("#ncestado").val();
          var idusu = $("#idusu").val();
          var ano = $("#ncano").val();
          var periodo = $("#ncperiodo").val();
          $("#tablaDatatable").load(
            "../capa_presentacion/tabla_nc_movil.php?ano=" +
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
  $("#nczonaltdp").empty().append("whatever");
  MostrarRegion();
  var nvoruc = $("#nvoruc").val();
  // var ncfqlineas = $("#ncqlineas").val();
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
    "../capa_presentacion/tabla_nc_movil.php?ano=" +
      ano +
      "&periodo=" +
      periodo +
      "&estado=" +
      estado +
      "&idusu=" +
      idusu
  );
});

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
    "../capa_presentacion/tabla_nc_movil.php?ano=" +
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
    url: "../procesos/obtenNotacargoMovil_back.php",
    success: function (data) {
      datos = jQuery.parseJSON(data);
      $("#idcargo").val(idcargo);
      $("#ncqlineass").val(datos["q_lineas"]);
      $("#ncmodalidads").val(datos["modalidad"]);

      $("#nccargofijos").val(datos["cargo_fijo"]);
      $("#ncrucs").val(datos["ruc"]);
      $("#ncrazonsocials").val(datos["razon_social"]);
      $("#ncnomcontactos").val(datos["contacto"]);
      $("#nctelefono1s").val(datos["telefono1"]);
      $("#nctelefono2s").val(datos["telefono2"]);
      $("#nctelefono1m").val(datos["telefono1"]);
      $("#nctelefono2m").val(datos["telefono2"]);
      $("#nczonaltdps").val(datos["zonal"]);
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
      $("#ncfechaacts").val(datos["fecha_actualizacion"]);
      $("#ncoportunidads").val(datos["oportunidad"]);
      $("#nccomentariobacks").val(datos["comentario_back"]);
      $("#ncnegociacions").val(datos["nivel_negociacion"]);
      $("#ncestimadas").val(datos["fecha_estimada"]);
      $("#ncejecutivotdps").val(datos["ejecutivo_tdp"]);
      $("#nctipoproductos").val(datos["tipo_producto"]);
      $("#ncetapab").val(datos["etapa"]);
      $("#nccasofwas").val(datos["casofwa"]);
      $("#nccomisionl").val(datos["comision"]);
      $("#ncobservacionl").val(datos["observacion"]);
      $("#ncdescuentol").val(datos["descuento"]);
      $("#fechaliquidacionl").val(datos["fecha_liquidacion"]);
      $("#ncobservacionesl").val(datos["comentario_liquidador"]);
      $("#ncoportunidade").val(datos["oportunidad"]);
      $("#ncciclo").val(datos["ciclo"]);
      $("#nccuentaf").val(datos["cuenta_financiera"]);
    },
  });
}

$("#btnActualizar").click(function () {
  if ($("#ncvalidacions").val() === "PENDIENTE") {
    if (
      $("#ncqlineass").val() == "0" ||
      $("#nccargofijos").val() == "0" ||
      $("#nccargofijos").val() == "" ||
      $("#nctelefono1m").val() == "" ||
      $("#ncnegociacions").val() == "" ||
      $("#ncestimadas").val() == ""
    ) {
      alertify.error("REVISE LOS DATOS");
    } else {
      datos = $("#frmNCMS").serialize();
      $.ajax({
        type: "POST",
        data: datos,
        url: "../procesos/agregarncmcomentario.php",
        success: function (r) {
          if (r != 1) {
            alertify.success("Actualizado con exito");
            $("#modalSeguimiento").modal("hide");
            var estado = $("#ncestado").val();
            var idusu = $("#idusu").val();
            var ano = $("#ncano").val();
            var periodo = $("#ncperiodo").val();
            $("#tablaDatatable").load(
              "../capa_presentacion/tabla_nc_movil.php?ano=" +
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
    }
  } else {
    alertify.error("VALIDADO COMO:" + $("#ncvalidacions").val());
  }
});

// cambios diciembre 2022

$("#nczonaltdp").click(function () {
  $("#ncejecutivotdp").empty().append("whatever");
  MostrarEjecutivoTDP();
});

//   TraerDatosCartera();
// });
function MostrarRegion(idregion) {
  $.ajax({
    url: "../procesos/obtenDatosUsuarioRegion.php",
    success: function (r) {
      datos = jQuery.parseJSON(r);
      if (datos.length > 0) {
        $("#nczonaltdp").append("<option value = '0'> --Elegir-- </option>");
        var i;
        for (i = 0; i < datos.length; i++) {
          if (datos[i][0] == idregion) {
            $("#nczonaltdp").append(
              "<option value = '" +
                datos[i][0] +
                "' selected>" +
                datos[i][1] +
                "</option>"
            );
          } else {
            $("#nczonaltdp").append(
              "<option value = '" +
                datos[i][0] +
                "'>" +
                datos[i][1] +
                "</option>"
            );
          }
        }
      }
    },
  });
}

function MostrarEjecutivoTDP() {
  $("#ncejecutivotdp").empty().append("whatever");
  datos = $("#frmNCM").serialize();
  $.ajax({
    type: "POST",
    data: datos,
    url: "../procesos/obtenDatosEjecutivo.php",
    success: function (data) {
      datos = jQuery.parseJSON(data);
      console.log(datos);
      if (datos.length > 0) {
        $("#ncejecutivotdp").append(
          "<option value = '0'> --Elegir-- </option>"
        );
        var i;
        for (i = 0; i < datos.length; i++) {
          $("#ncejecutivotdp").append(
            "<option value = '" + datos[i][0] + "'>" + datos[i][2] + "</option>"
          );
        }
      }
    },
  });
}
