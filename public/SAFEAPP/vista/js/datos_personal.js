$(document).ready(function () {
  $("#tablaDatatable").load("../capa_presentacion/tablaPersonal.php");
});

$("#cerrar2").click(function () {
  $("#personal").val("");
  $("#nombreusu").val("");
  $("#clave").val("");

  $("#fecha_nacimientoA").val("");
  $("#telefonoA").val("");
  $("#correoA").val("");

  $("#rolcombo").empty().append("whatever");
  $("#regcombo").empty().append("whatever");
  $("#supcombo").empty().append("whatever");
});

$("#cerrar3").click(function () {
  $("#personalA").val("");
  $("#nombreusuA").val("");
  $("#claveA").val("");

  $("#fecha_nacimientoA").val("");
  $("#telefonoA").val("");
  $("#correoA").val("");

  $("#supcomboA").empty().append("whatever");
});

$("#cerrarA").click(function () {
  $("#personalA").val("");
  $("#nombreusuA").val("");
  $("#claveA").val("");

  $("#fecha_nacimientoA").val("");
  $("#telefonoA").val("");
  $("#correoA").val("");

  $("#supcomboA").empty().append("whatever");
});

$("#btnActualizarP").click(function () {
  datos = $("#frmActualizar").serialize();
  $.ajax({
    type: "POST",
    data: datos,
    url: "../procesos/actualizarPersonal.php",
    success: function (r) {
      try {
        datos = jQuery.parseJSON(r);
      } catch (error) {
        console.log(error, datos);
      }
      if (r == 1) {
        alertify.error("Fallo al actualizar");
      } else {
        $("#modalEditar").modal("hide");
        alertify.success("Actualizado con exito");
        $("#personalA").val("");
        $("#fecha_nacimientoA").val("");
        $("#telefonoA").val("");
        $("#correoA").val("");
        $("#tablaDatatable").load("../capa_presentacion/tablaPersonal.php");
      }
    },
  });
});

function FrmActualizar(id) {
  MostrarSupervisorA();
  $.ajax({
    type: "POST",
    data: "id_usuario=" + id,
    url: "../procesos/obtenDatosPersonal.php",
    success: function (r) {
      datos = jQuery.parseJSON(r);
      $("#personalA").val(datos["personal"]);
      $("#nombreusuA").val(datos["usuario"]);
      $("#id_usu").val(datos["id_usuario"]);

      $("#fecha_nacimientoA").val(datos["fecha_nacimiento"]);
      $("#telefonoA").val(datos["telefono"]);
      $("#correoA").val(datos["correo"]);
    },
  });
}

function MostrarRol() {
  $.ajax({
    url: "../procesos/obtenDatosUsuarioRol.php",
    success: function (r) {
      datos = jQuery.parseJSON(r);
      if (datos.length > 0) {
        $("#rolcombo").append("<option value = '0'> --Elegir-- </option>");
        var i;
        for (i = 0; i < datos.length; i++) {
          $("#rolcombo").append(
            "<option value = '" + datos[i][0] + "'>" + datos[i][1] + "</option>"
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
        $("#regcombo").append("<option value = '0'> --Elegir-- </option>");
        var i;
        for (i = 0; i < datos.length; i++) {
          $("#regcombo").append(
            "<option value = '" + datos[i][0] + "'>" + datos[i][1] + "</option>"
          );
        }
      }
    },
  });
}

function MostrarSupervisor() {
  $.ajax({
    url: "../procesos/obtenDatosUsuarioSupervisor.php",
    success: function (r) {
      datos = jQuery.parseJSON(r);
      if (datos.length > 0) {
        $("#supcombo").append("<option value = '0'> --Elegir-- </option>");
        var i;
        for (i = 0; i < datos.length; i++) {
          $("#supcombo").append(
            "<option value = '" + datos[i][0] + "'>" + datos[i][1] + "</option>"
          );
        }
      }
    },
  });
}

function MostrarSupervisorA() {
  $.ajax({
    url: "../procesos/obtenDatosUsuarioSupervisor.php",
    success: function (r) {
      datos = jQuery.parseJSON(r);
      if (datos.length > 0) {
        $("#supcomboA").append("<option value = '0'> --Elegir-- </option>");
        var i;
        for (i = 0; i < datos.length; i++) {
          $("#supcomboA").append(
            "<option value = '" + datos[i][0] + "'>" + datos[i][1] + "</option>"
          );
        }
      }
    },
  });
}

function AgregarRegion() {
  datos = $("#frmnuevo").serialize();
  $.ajax({
    type: "POST",
    data: datos,
    url: "../procesos/AgregarRegion.php",
    success: function (data) {
      datos = jQuery.parseJSON(data);
      console.log(datos);
    },
  });
}

function AgregarAccesos() {
  datos = $("#frmnuevo").serialize();
  $.ajax({
    type: "POST",
    data: datos,
    url: "../procesos/AgregarAcceso.php",
    success: function (data) {
      datos = jQuery.parseJSON(data);
      console.log(datos);
    },
  });
}
