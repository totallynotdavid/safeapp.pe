$(document).ready(function () {
  TraerDatosAnoMes();
});

$("#cerrar1").click(function () {
  $("#nczonaltdp").empty().append("whatever");
});

$("#cerrar2").click(function () {
  $("#nczonaltdp").empty().append("whatever");
});

$("#btnActualizar").click(function () {
  datos = $("#frmNCMB").serialize();
  $.ajax({
    type: "POST",
    data: datos,
    url: "../procesos/agregarncmback.php",
    success: function (r) {
      if (r != 1) {
        alertify.success("Registrado con exito");

        $("#modalEditar").modal("hide");
        $("#nczonaltdp").empty().append("whatever");

        var ncestadof = $("#ncestadof").val();
        var tienda = $("#tienda").val();
        var ncvalidacionf = $("#ncvalidacionf").val();
        var ano = $("#ncano").val();
        var periodo = $("#ncperiodo").val();
        $("#tablaDatatable").load(
          "../capa_presentacion/tabla_repbackmovil.php?ano=" +
            ano +
            "&periodo=" +
            periodo +
            "&ncestadof=" +
            ncestadof +
            "&tienda=" +
            tienda +
            "&ncvalidacionf=" +
            ncvalidacionf
        );
      } else {
        alertify.error("Fallo al agregar");
      }
    },
  });
});

$("#btnfiltrar").click(function () {
  var ncestadof = $("#ncestadof").val();
  var tienda = $("#tienda").val();
  var ncvalidacionf = $("#ncvalidacionf").val();
  var ano = $("#ncano").val();
  var periodo = $("#ncperiodo").val();
  $("#tablaDatatable").load(
    "../capa_presentacion/tabla_repbackmovil.php?ano=" +
      ano +
      "&periodo=" +
      periodo +
      "&ncestadof=" +
      ncestadof +
      "&tienda=" +
      tienda +
      "&ncvalidacionf=" +
      ncvalidacionf
  );
});

function MostrarRegion(idregion) {
  $.ajax({
    url: "../procesos/obtenDatosUsuarioRegion.php",
    success: function (r) {
      datos = jQuery.parseJSON(r);
      if (datos.length > 0) {
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

function TraerDatosAnoMes() {
  var ncestadof = $("#ncestadof").val();
  var tienda = $("#tienda").val();
  var ncvalidacionf = $("#ncvalidacionf").val();
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
    "../capa_presentacion/tabla_repbackmovil.php?ano=" +
      ano +
      "&periodo=" +
      periodo +
      "&ncestadof=" +
      ncestadof +
      "&tienda=" +
      tienda +
      "&ncvalidacionf=" +
      ncvalidacionf
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

      $("#fechaact").val(datos["fecha_actualizacion"]);
      $("#ncoportunidad").val(datos["oportunidad"]);
      $("#ncejecutivotdp").val(datos["ejecutivo_telefonica"]);
      $("#nccomentarioback").val(datos["comentario_back"]);

      MostrarRegion(datos["zonal_telefonica"]);
      MostrarEjecutivo(datos["ejecutivo_telefonica"]);
    },
  });
}

$("#btnexportar").click(function () {
  $("#frmrepexportarback").submit();
});
