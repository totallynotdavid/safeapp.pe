$(document).ready(function () {
  TraerDatosAnoMes();
});

$("#cerrar1").click(function () {
  $("#fechaActivacions").val("");
  $("#ncrucs").val("");
  $("#ncrazonsocials").val("");
  $("#quiebre_servicios").val("");
  $("#quiebre_tipo_averias").val("");
  $("#quiebre_problemas").val("");
  $("#quiebre_detalles").val("");
  $("#quiebre_backs").val("");
  $("#fechaInicios").val("");
  $("#fechaTickets").val("");
  $("#quiebre_numero_tickets").val("");
  $("#quiebre_contacto1").val("");
  $("#quiebre_celular1").val("");
  $("#quiebre_correo1").val("");

  $("#quiebre_contacto2").val("");
  $("#quiebre_celular2").val("");
  $("#quiebre_correo2").val("");

  $("#quiebre_ejecutivo").val("");
  $("#quiebre_numero_problemas").val("");
  $("#ncobservacioness").val("");

  $("#ncvalidacions").val("");
  $("#casosf").val("");
  $("#ncobservacionesval").val("");
});

$("#cerrar2").click(function () {
  $("#fechaActivacions").val("");
  $("#ncrucs").val("");
  $("#ncrazonsocials").val("");
  $("#quiebre_servicios").val("");
  $("#quiebre_tipo_averias").val("");
  $("#quiebre_problemas").val("");
  $("#quiebre_detalles").val("");
  $("#quiebre_backs").val("");
  $("#fechaInicios").val("");
  $("#fechaTickets").val("");
  $("#quiebre_numero_tickets").val("");
  $("#quiebre_contacto1").val("");
  $("#quiebre_celular1").val("");
  $("#quiebre_correo1").val("");

  $("#quiebre_contacto2").val("");
  $("#quiebre_celular2").val("");
  $("#quiebre_correo2").val("");

  $("#quiebre_ejecutivo").val("");
  $("#quiebre_numero_problemas").val("");
  $("#ncobservacioness").val("");

  $("#ncvalidacions").val("");
  $("#casosf").val("");
  $("#ncobservacionesval").val("");
});

$("#btnRegistrar").click(function () {
  if (
    $("#validacions").val() == null ||
    $("#casosf").val() == null ||
    $("#ncobservacionesval").val() == null
  ) {
    datos = $("#frmNCM").serialize();
    $.ajax({
      type: "POST",
      data: datos,
      url: "../procesos/agregarQuiebrevalidacion.php",
      success: function (r) {
        if (r != 1) {
          alertify.success("Registrado con exito");

          $("#modalEditar").modal("hide");

          $("#fecha_registro_quiebre").val("");
          $("#fechaActivacions").val("");
          $("#fechaInicios").val("");
          $("#ncrucs").val("");
          $("#ncrazonsocials").val("");
          $("#quiebre_servicios").val("");
          $("#quiebre_tipo_averias").val("");
          $("#quiebre_problemas").val("");
          $("#quiebre_detalles").val("");
          $("#quiebre_tickets").val("");
          $("#fechaTickets").val("");
          $("#quiebre_numero_tickets").val("");
          $("#quiebre_contacto1").val("");
          $("#quiebre_celular1").val("");
          $("#quiebre_correo1").val("");
          $("#quiebre_contacto2").val("");
          $("#quiebre_celular2").val("");
          $("#quiebre_correo2").val("");
          $("#quiebre_numero_problemas").val("");
          $("#ncregions").val("");
          $("#ncobservacioness").val("");

          $("#ncvalidacions").val("");
          $("#casosf").val("");
          $("#ncobservacionesval").val("");

          var validacionf = $("#ncvalidacionf").val();
          var tienda = $("#tienda").val();
          var ano = $("#ncano").val();
          var periodo = $("#ncperiodo").val();
          $("#tablaDatatable").load(
            "../capa_presentacion/tabla_quiebre_validacion.php?ano=" +
              ano +
              "&periodo=" +
              periodo +
              "&validacionf=" +
              validacionf +
              "&tienda=" +
              tienda
          );
        } else {
          alertify.error("Fallo al agregar");
        }
      },
    });
  } else {
    alertify.error("EL PEDIDO ESTA: " + $("#ncestado").val());
  }
});

$("#btnfiltrar").click(function () {
  var validacionf = $("#ncvalidacionf").val();
  var tienda = $("#tienda").val();
  var ano = $("#ncano").val();
  var periodo = $("#ncperiodo").val();
  $("#tablaDatatable").load(
    "../capa_presentacion/tabla_quiebre_validacion.php?ano=" +
      ano +
      "&periodo=" +
      periodo +
      "&validacionf=" +
      validacionf +
      "&tienda=" +
      tienda
  );
});

function TraerDatosAnoMes() {
  var validacionf = $("#ncvalidacionf").val();
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
    "../capa_presentacion/tabla_quiebre_validacion.php?ano=" +
      ano +
      "&periodo=" +
      periodo +
      "&validacionf=" +
      validacionf +
      "&tienda=" +
      tienda
  );
}

function TraerDatosTabla(idquiebre) {
  $.ajax({
    type: "POST",
    data: "idquiebre=" + idquiebre,
    url: "../procesos/obtenQuiebre_validacion.php",
    success: function (data) {
      try {
        datos = jQuery.parseJSON(data);
        $("#idquiebre").val(idquiebre);

        $("#fecha_registro_quiebre").val(datos["fecha_registro"]);
        $("#fechaActivacions").val(datos["fecha_activacion"]);
        $("#fechaInicios").val(datos["fecha_inicio_averia"]);
        $("#ncrucs").val(datos["ruc"]);
        $("#ncrazonsocials").val(datos["razon_social"]);
        $("#quiebre_servicios").val(datos["servicio"]);
        $("#quiebre_tipo_averias").val(datos["tipo_averia"]);
        $("#quiebre_problemas").val(datos["problema_equipo"]);
        $("#quiebre_detalles").val(datos["detalle_equipo"]);
        $("#quiebre_tickets").val(datos["ticket_atencion"]);
        $("#fechaTickets").val(datos["fecha_ticket_atencion"]);
        $("#quiebre_numero_tickets").val(datos["numero_ticket"]);
        $("#quiebre_contacto1").val(datos["contacto1"]);
        $("#quiebre_celular1").val(datos["celular1"]);
        $("#quiebre_correo1").val(datos["correo1"]);
        $("#quiebre_contacto2").val(datos["contacto2"]);
        $("#quiebre_celular2").val(datos["celular2"]);
        $("#quiebre_correo2").val(datos["correo2"]);

        $("#quiebre_ejecutivo").val(datos["id_usuario"]);
        $("#quiebre_numero_problemas").val(datos["numero_problema"]);
        $("#ncregions").val(datos["zonal_telefonica"]);
        $("#ncobservacioness").val(datos["comentario_ejecutivo"]);

        $("#quiebre_tipo_averiass").val(datos["tipo_averia"]);
        $("#casosf").val(datos["casosf"]);
        $("#fechavalidacions").val(datos["fecha_validacion"]);
        $("#ncvalidadors").val(datos["id_validador"]);
        $("#ncvalidacions").val(datos["estado"]);
        $("#ncobservacionesval").val(datos["comentario_validador"]);
        $("#ncestado").val(datos["estado"]);

        $("#quiebre_backs").val(datos["back"]);
      } catch (error) {
        console.log("Error parsing JSON:", error, data);
      }
    },
  });
}

$("#btnexportar").click(function () {
  $("#frmrepexportarquiebrevalidacion").submit();
});
