$(document).ready(function () {
  TraerDatosAnoMes();
});

$("#cerrar1").click(function () {
  $("#nczonaltdp").empty().append("whatever");
  $("#ncejecutivotdp").empty().append("whatever");
});

$("#cerrar2").click(function () {
  $("#nczonaltdp").empty().append("whatever");
  $("#ncejecutivotdp").empty().append("whatever");
});

$("#nczonaltdp").click(function () {
  $("#ncejecutivotdp").empty().append("whatever");
  MostrarEjecutivoTDP();
});

$("#ncejecutivotdp").click(function () {
  TraerDatosCartera();
});

$("#btnliquidacion").click(function () {
  if ($("#fechaact").val() == "") {
    alertify.error("Verifica la FECHA ACTUALIZACION");
  } else {
    datos = $("#frmNCML").serialize();
    $.ajax({
      type: "POST",
      data: datos,
      url: "../procesos/agregarliquidacion.php",
      success: function (r) {
        if (r != 1) {
          alertify.success("Actualizado con éxito");

          $("#modalEditar").modal("hide");
          $("#nczonaltdp").empty().append("whatever");
          $("#ncejecutivotdp").empty().append("whatever");

          var ncestadof = $("#ncestadof").val();
          var tienda = $("#tienda").val();

          var ano = $("#ncano").val();
          var periodo = $("#ncperiodo").val();
          $("#tablaDatatable").load(
            "../capa_presentacion/tabla_liquidacion.php?ano=" +
              ano +
              "&periodo=" +
              periodo +
              "&ncestadof=" +
              ncestadof +
              "&tienda=" +
              tienda
          );
        } else {
          alertify.error("Fallo al agregar");
        }
      },
    });
  }
});

// $("#btnActualizarpro").click(function () {
//   datos = $("#frmNCM").serialize();
//   $.ajax({
//     type: "POST",
//     data: datos,
//     url: "../procesos/agregarncmmodificacionback.php",
//     success: function (r) {
//       if (r != 1) {
//         alertify.success("Registrado con exito");

//         $("#modalEditar").modal("hide");
//         $("#nczonaltdp").empty().append("whatever");

//         var ncestadof = $("#ncestadof").val();
//         var tienda = $("#tienda").val();

//         var ano = $("#ncano").val();
//         var periodo = $("#ncperiodo").val();
//         $("#tablaDatatable").load(
//           "../capa_presentacion/tabla_liquidacion.php?ano=" +
//             ano +
//             "&periodo=" +
//             periodo +
//             "&ncestadof=" +
//             ncestadof +
//             "&tienda=" +
//             tienda
//         );
//       } else {
//         alertify.error("Fallo al agregar");
//       }
//     },
//   });
// });

$("#btnfiltrar").click(function () {
  var ncestadof = $("#ncestadof").val();
  var tienda = $("#tienda").val();

  var ano = $("#ncano").val();
  var periodo = $("#ncperiodo").val();
  $("#tablaDatatable").load(
    "../capa_presentacion/tabla_liquidacion.php?ano=" +
      ano +
      "&periodo=" +
      periodo +
      "&ncestadof=" +
      ncestadof +
      "&tienda=" +
      tienda
  );
});

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

function MostrarEjecutivo(idcartera) {
  $.ajax({
    url: "../procesos/obtenDatosEjecutivoCartera.php",
    success: function (r) {
      datos = jQuery.parseJSON(r);
      if (datos.length > 0) {
        $("#ncejecutivotdp").append(
          "<option value = '0'> --Elegir-- </option>"
        );
        var i;
        for (i = 0; i < datos.length; i++) {
          if (datos[i][0] == idcartera) {
            $("#ncejecutivotdp").append(
              "<option value = '" +
                datos[i][0] +
                "' selected>" +
                datos[i][2] +
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
  datos = $("#frmNCMB").serialize();
  $.ajax({
    type: "POST",
    data: datos,
    url: "../procesos/obtenDatosEjecutivo.php",
    success: function (data) {
      try {
        datos = jQuery.parseJSON(data);
      } catch (error) {
        console.log("error: ", error, data);
      }

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

function TraerDatosCartera() {
  datos = $("#frmNCMB").serialize();
  $.ajax({
    type: "POST",
    data: datos,
    url: "../procesos/obtenDatosCartera.php",
    success: function (data) {
      datos = jQuery.parseJSON(data);
      $("#idcartera").val(datos["id_cartera"]);
    },
  });
}

function TraerDatosAnoMes() {
  var ncestadof = $("#ncestadof").val();
  var tienda = $("#tienda").val();
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
    "../capa_presentacion/tabla_liquidacion.php?ano=" +
      ano +
      "&periodo=" +
      periodo +
      "&ncestadof=" +
      ncestadof +
      "&tienda=" +
      tienda
  );
}

function TraerDatosTabla(idcargo) {
  $.ajax({
    type: "POST",
    data: "idcargo=" + idcargo,
    url: "../procesos/obten_liquidacion.php",
    success: function (data) {
      datos = jQuery.parseJSON(data);
      $("#idcargo").val(idcargo);
      $("#idcargos").val(idcargo);
      $("#ncqlineas").val(datos["q_lineas"]);
      $("#ncmodalidad").val(datos["modalidad"]);

      $("#nccargofijo").val(datos["cargo_fijo"]);
      $("#ncruc").val(datos["ruc"]);
      $("#ncrazonsocial").val(datos["razon_social"]);
      $("#ncnomcontacto").val(datos["contacto"]);
      $("#nctelefono1").val(datos["telefono1"]);
      $("#nctelefono2").val(datos["telefono2"]);

      $("#ncregion").val(datos["zonal"]);
      $("#ncpersonal").val(datos["personal"]);
      $("#ncobservacioneseje").val(datos["comentario"]);
      $("#ncestado").val(datos["estado"]);
      $("#ncsupervisor").val(datos["supervisor"]);
      $("#fechaingreso").val(datos["fecha_ingreso"]);
      $("#fechavalidacion").val(datos["fecha_validacion"]);
      $("#ncvalidacion").val(datos["validacion"]);
      $("#ncvalidador").val(datos["validador"]);
      $("#ncobservacionesval").val(datos["comentario_validador"]);
      $("#ncestadoact").val(datos["estado_actual"]);

      $("#ncejecutivotdp").val(datos["ejecutivo_telefonica"]);
      $("#fechaact").val(datos["fecha_actualizacion"]);
      $("#nccomentarioback").val(datos["comentario_back"]);
      $("#ncoportunidad").val(datos["oportunidad"]);
      $("#ncnodo").val(datos["nodo"]);

      $("#ncfwa").val(datos["casofwa"]);

      $("#ncejecutivotdps").val(datos["ejecutivo_tdp"]);
      $("#nctipoproductos").val(datos["tipo_producto"]);
      $("#ncetapas").val(datos["etapa"]);
      $("#nctipoetapas").val(datos["tipo_etapa"]);

      $("#nccomisions").val(datos["comision"]);
      $("#ncobservacionl").val(datos["observacion"]);
      $("#ncdescuentos").val(datos["descuento"]);
      $("#fechavalidacionl").val(datos["fecha_liquidacion"]);
      $("#nccomentariol").val(datos["comentario_liquidador"]);

      MostrarRegion(datos["zonal_telefonica"]);
      MostrarEjecutivo(datos["ejecutivo_telefonica"]);
    },
  });
}

$("#btnexportar").click(function () {
  $("#frmrepexportarback").submit();
});
