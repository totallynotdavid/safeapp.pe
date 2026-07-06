$("#mostrar").click(function () {
  if ($("#desde").val() === "") {
    alertify.error("SELECCIONE FECHA");
  } else {
    if ($("#hasta").val() === "") {
      alertify.error("SELECCIONE FECHA");
    } else {
      var desde = $("#desde").val();

      var hasta = $("#hasta").val();

      $("#tablaDatatable").load(
        "../capa_presentacion/tablahistoricousu.php?desde=" +
          desde +
          "&hasta=" +
          hasta
      );
    }
  }
});


$("#excel").click(function () {
  if ($("#desde").val() === "") {
    alertify.error("SELECCIONE FECHA");
  } else {
    if ($("#hasta").val() === "") {
      alertify.error("SELECCIONE FECHA");
    } else {
      $("#frmrephistoricousu").submit();
    }
  }
});

$("#cerrar1").click(function () {
  $("#telefono").val("");
  $("#qlineas").val("");
  $("#nomcontacto").val("");
  $("#correorl").val("");
  $("#comentario").val("");
  $("#qclaro").val("");
  $("#qentel").val("");
  $("#qbitel").val("");
  $("#respuesta").empty().append("whatever");
  $("#btnRegistrar").hide();
});

$("#cerrar2").click(function () {
  $("#telefono").val("");
  $("#qlineas").val("");
  $("#nomcontacto").val("");
  $("#correorl").val("");
  $("#comentario").val("");
  $("#qclaro").val("");
  $("#qentel").val("");
  $("#qbitel").val("");
  $("#respuesta").empty().append("whatever");
  $("#btnRegistrar").hide();
});

$("#respuesta").click(function () {
  $("#btnRegistrar").show();
  if (
    $("#respuesta").val() == "1" ||
    $("#respuesta").val() == "2" ||
    $("#respuesta").val() == "3" ||
    $("#respuesta").val() == "4" ||
    $("#respuesta").val() == "15" ||
    $("#respuesta").val() == "16" ||
    $("#respuesta").val() == "17" ||
    $("#respuesta").val() == "18"
  ) {
    $("#titular").toggle();
    $("#comun").toggle();

    $(".fr").hide();
  } else {
    if (
      $("#respuesta").val() == "5" ||
      $("#respuesta").val() == "6" ||
      $("#respuesta").val() == "7" ||
      $("#respuesta").val() == "8" ||
      $("#respuesta").val() == "9" ||
      $("#respuesta").val() == "10" ||
      $("#respuesta").val() == "11" ||
      $("#respuesta").val() == "12" ||
      $("#respuesta").val() == "13" ||
      $("#respuesta").val() == "14" ||
      $("#respuesta").val() == "19" ||
      $("#respuesta").val() == "20" ||
      $("#respuesta").val() == "21" ||
      $("#respuesta").val() == "22" ||
      $("#respuesta").val() == "23" ||
      $("#respuesta").val() == "24" ||
      $("#respuesta").val() == "25" ||
      $("#respuesta").val() == "26" ||
      $("#respuesta").val() == "27" ||
      $("#respuesta").val() == "28"
    ) {
      $("#comun").toggle();
    } else {
      $("#titular").hide();
      $("#comun").hide();

      $(".fr").hide();
    }
  }
});

$("#btnRegistrar").click(function () {
  var telefono = $("#telefono").val();
  if (
    $("#respuesta").val() === "0" ||
    $("#telefono").val() === "" ||
    telefono.length < 8
  ) {
    alertify.error("REVISE LOS DATOS");
  } else {
    if (
      ($("#respuesta").val() == "1" ||
        $("#respuesta").val() == "2" ||
        $("#respuesta").val() == "3" ||
        $("#respuesta").val() == "4" ||
        $("#respuesta").val() == "15" ||
        $("#respuesta").val() == "16" ||
        $("#respuesta").val() == "17" ||
        $("#respuesta").val() == "18") &&
      ($("#qlineas").val() === "0" ||
        $("#qlineas").val() === "" ||
        $("#nomcontacto").val() === "")
    ) {
      alertify.error("REVISE LOS DATOS");
    } else {
      datos = $("#frmLlamada").serialize();
      $.ajax({
        type: "POST",
        data: datos,
        url: "../procesos/agregarLlamadaH.php",
        success: function (r) {
          if (r != 1) {
            AgregarTelefono();

            alertify.success("REGISTRADO CON EXITO");
            $("#modalEditar").modal("hide");
            $("#telefono").val("");
            $("#qlineas").val("");
            $("#nomcontacto").val("");
            $("#correorl").val("");
            $("#comentario").val("");
            $("#qclaro").val("");
            $("#qentel").val("");
            $("#qbitel").val("");
            $("#respuesta").empty().append("whatever");
            $("#btnRegistrar").hide();
          } else {
            alertify.error("FALLO AL AGREGAR");
          }
        },
      });
    }
  }
});

$("#telefono").blur(function () {
  var tel = $("#telefono").val();
  if (tel === "") {
    $("#btnRegistrar").hide();
  } else {
    $("#btnRegistrar").show();
  }
});

$("#gestion").click(function () {
  TraerDatosRespuesta($("#gestion").val());
});

function AgregarListanegra() {
  datos = $("#frmLlamada").serialize();
  $.ajax({
    type: "POST",
    data: datos,
    url: "../procesos/AgregarListaNegra.php",
    success: function (data) {
      datos = jQuery.parseJSON(data);
      console.log(datos);
    },
  });
}

function CambiarEstadoTel() {
  datos = $("#frmLlamada").serialize();
  $.ajax({
    type: "POST",
    data: datos,
    url: "../procesos/CambiaEstadoTel.php",
    success: function (data) {
      datos = jQuery.parseJSON(data);
      console.log(datos);
    },
  });
}

function TraerDatosRespuesta(gestion) {
  $("#respuesta").empty().append("whatever");
  $.ajax({
    type: "POST",
    data: "gestion=" + gestion,
    url: "../procesos/obtenDatosRespuesta.php",
    success: function (data) {
      datos = jQuery.parseJSON(data);

      if (datos.length > 0) {
        $("#respuesta").append("<option value = '0'> --Elegir-- </option>");
        var i;
        for (i = 0; i < datos.length; i++) {
          $("#respuesta").append(
            "<option value = '" + datos[i][0] + "'>" + datos[i][1] + "</option>"
          );
        }
      }
    },
  });
}

function AgregarTelefono() {
  datos = $("#frmLlamada").serialize();
  $.ajax({
    type: "POST",
    data: datos,
    url: "../procesos/AgregarTelefono.php",
    success: function (data) {
      datos = jQuery.parseJSON(data);
      console.log(datos);
    },
  });
}

function TraerDatosTabla(idllamada) {
  TraerDatosRespuesta($("#gestion").val());
  $("#titular").hide();
  $("#comun").hide();

  $.ajax({
    type: "POST",
    data: "idllamada=" + idllamada,
    url: "../procesos/obtenDatosLlamada.php",
    success: function (data) {
      datos = jQuery.parseJSON(data);
      $("#datos").html(datos["razon_social"] + "   RUC:" + datos["ruc"]);
      $("#ruc").val(datos["ruc"]);
      $("#razon_social").val(datos["razon_social"]);
      $("#telefono").val(datos["telefono"]);
      $("#nomcontacto").val(datos["nombre_contacto"]);
      $("#correorl").val(datos["correo"]);
      $("#comentario").val(datos["comentario"]);
      $("#tipo_data").val(datos["tipo_data"]);
      $("#qclaro").val(datos["q_claro"]);
      $("#qentel").val(datos["q_entel"]);
      $("#qbitel").val(datos["q_bitel"]);
      $("#tablaDatatablehistorial").load(
        "../capa_presentacion/tablaHistorial.php?ruc=" + datos["ruc"]
      );
      $("#tablaDatatabletelefono").load(
        "../capa_presentacion/tablaTelefono.php?ruc=" + datos["ruc"]
      );
      $("#tablaDatatableplanta").load(
        "../capa_presentacion/tablaplanta.php?ruc=" + datos["ruc"]
      );
      $("#tablaDatatablewinback").load(
        "../capa_presentacion/tablawinback.php?ruc=" + datos["ruc"]
      );
    },
  });
}

function TraerDatosModificar(idllamada) {
  TraerDatosRespuesta();
  $("#titular").show();
  $("#comun").show();

  $.ajax({
    type: "POST",
    data: "idllamada=" + idllamada,
    url: "../procesos/obtenDatosLlamada.php",
    success: function (data) {
      datos = jQuery.parseJSON(data);
      $("#datos").html(datos["razon_social"] + "   RUC:" + datos["ruc"]);
      $("#ruc").val(datos["ruc"]);
      $("#razon_social").val(datos["razon_social"]);
      $("#telefono").val(datos["telefono"]);
      $("#qlineas").val(datos["dnirl"]);
      $("#nrl").val(datos["nombrerl"]);
      $("#nomcontacto").val(datos["correo"]);
      $("#comentario").val(datos["comentario"]);
      $("#tablaDatatablehistorial").load(
        "../capa_presentacion/tablaHistorial.php?ruc=" + datos["ruc"]
      );
      $("#tablaDatatabletelefono").load(
        "../capa_presentacion/tablaTelefono.php?ruc=" + datos["ruc"]
      );
    },
  });
}
