$(document).ready(function () {
  TraerDatosAnoMes();
});

$("#cerrar1").click(function () {
  $("#nczonaltdp").empty().append("whatever");
});

$("#cerrar2").click(function () {
  $("#nczonaltdp").empty().append("whatever");
});

$("#ncestado").click(function () {
  if ($("#ncestado").val() == "LIQUIDADO") {
    document.getElementById("ncfechaliq").disabled = false;
  } else {
    document.getElementById("ncfechaliq").disabled = true;
  }
});

$("#btnActualizar").click(function () {
  if ($("#ncestado").val() == "LIQUIDADO") {
    if ($("#ncfechaliq").val() == "") {
      alertify.error("Verifica la FECHA DE LIQUIDACION");
    } else {
      datos = $("#frmNCFB").serialize();
      $.ajax({
        type: "POST",
        data: datos,
        url: "../procesos/agregarncfback.php",
        success: function (r) {
          if (r != 1) {
            alertify.success("Registrado con exito");

            $("#modalEditar").modal("hide");
            $("#nczonaltdp").empty().append("whatever");
            var ncestadof = $("#ncestadof").val();
            var tienda = $("#tienda").val();
            var ano = $("#ncano").val();
            var periodo = $("#ncperiodo").val();
            $("#tablaDatatable").load(
              "../capa_presentacion/tabla_repbackfija.php?ano=" +
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
  } else {
    datos = $("#frmNCFB").serialize();
    $.ajax({
      type: "POST",
      data: datos,
      url: "../procesos/agregarncfback.php",
      success: function (r) {
        if (r != 1) {
          alertify.success("Registrado con exito");
          $("#modalEditar").modal("hide");
          $("#nczonaltdp").empty().append("whatever");
          var ncestadof = $("#ncestadof").val();
          var tienda = $("#tienda").val();
          var ano = $("#ncano").val();
          var periodo = $("#ncperiodo").val();
          $("#tablaDatatable").load(
            "../capa_presentacion/tabla_repbackfija.php?ano=" +
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

$("#btnfiltrar").click(function () {
  var ncestadof = $("#ncestadof").val();
  var tienda = $("#tienda").val();
  var ano = $("#ncano").val();
  var periodo = $("#ncperiodo").val();
  $("#tablaDatatable").load(
    "../capa_presentacion/tabla_repbackfija.php?ano=" +
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
    "../capa_presentacion/tabla_repbackfija.php?ano=" +
      ano +
      "&periodo=" +
      periodo +
      "&ncestadof=" +
      ncestadof +
      "&tienda=" +
      tienda
  );
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

function TraerDatosTabla(idcargo) {
  $.ajax({
    type: "POST",
    data: "idcargo=" + idcargo,
    url: "../procesos/obtenNotacargo_back.php",
    success: function (data) {
      datos = jQuery.parseJSON(data);
      $("#idcargo").val(idcargo);
      $("#ncqlineas").val(datos["q_lineas"]);
      $("#ncmodalidad").val(datos["modalidad"]);
      $("#suptipocom").val(datos["subtipo"]);
      $("#descripcioncom").val(datos["descripcion"]);
      $("#nccargofijo").val(datos["cargo_fijo"]);
      $("#ncruc").val(datos["ruc"]);
      $("#ncrazonsocial").val(datos["razon_social"]);
      $("#ncnomcontacto").val(datos["contacto"]);
      $("#nctelefono1").val(datos["telefono1"]);
      $("#nctelefono2").val(datos["telefono2"]);
      $("#ncdireccion").val(datos["direccion"]);
      $("#nccoordenadas").val(datos["coordenadas"]);
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
      $("#ncpeticion").val(datos["peticion"]);
      $("#nccontrata").val(datos["contrata"]);
      $("#ncfechaliq").val(datos["fecha_liquidacion"]);
      $("#ncnunasignado").val(datos["nasignado"]);
      $("#ncejecutivotdp").val(datos["ejecutivo_telefonica"]);
      $("#nccomentarioback").val(datos["comentario_back"]);
      $("#nctecnologia").val(datos["tecnologia"]);

      MostrarRegion(datos["zonal_telefonica"]);
      MostrarEjecutivo(datos["ejecutivo_telefonica"]);
    },
  });
}

$("#btnexportar").click(function () {
  $("#frmrepexportarback").submit();
});
