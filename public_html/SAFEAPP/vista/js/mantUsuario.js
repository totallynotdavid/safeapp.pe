$(document).ready(function () {
    $('#tablaDatatable').load('../capa_presentacion/tablaUsuario.php');
  
});


$('#nuevo').click(function () {
    MostrarRol();
    MostrarRegion();
    MostrarSupervisor();
});


$('#cerrar1').click(function () {
    $("#personal").val("");
    $("#nombreusu").val("");
    $("#clave").val("");
    $('#rolcombo').empty().append('whatever');
      $('#regcombo').empty().append('whatever');
      $('#supcombo').empty().append('whatever');
});

$('#cerrar2').click(function () {
   $("#personal").val("");
    $("#nombreusu").val("");
    $("#clave").val("");
    $('#rolcombo').empty().append('whatever');
    $('#regcombo').empty().append('whatever');
    $('#supcombo').empty().append('whatever');
});



$('#cerrar3').click(function () {
    $("#personalA").val("");
    $("#nombreusuA").val("");
    $("#claveA").val("");
      $('#supcomboA').empty().append('whatever');
});

$('#cerrarA').click(function () {
   $("#personalA").val("");
    $("#nombreusuA").val("");
    $("#claveA").val("");
    $('#supcomboA').empty().append('whatever');
});


$('#btnActualizar').click(function () {
    datos = $('#frmActualizar').serialize();
    $.ajax({
        type: "POST",
        data: datos,
        url: "../procesos/actualizarUsuario.php",
        success: function (r) {
            datos = jQuery.parseJSON(r);
            if (r == 1) {
                 alertify.error("Fallo al actualizar");
            } else {
               $('#modalEditar').modal('hide');
                alertify.success("Actualizado con exito");
                 $("#personalA").val("");
                $("#nombreusuA").val("");
                $("#claveA").val("");
                 $('#supcomboA').empty().append('whatever');
                $('#tablaDatatable').load('../capa_presentacion/tablaUsuario.php');
            }
        }
    });
});


function FrmActualizar(id) {
 MostrarSupervisorA();
    $.ajax({
        type: "POST",
        data: "id_usuario=" + id,
        url: "../procesos/obtenDatosUsuario.php",
        success: function (r) {
            datos = jQuery.parseJSON(r);
            $('#personalA').val(datos['personal']);
            $('#nombreusuA').val(datos['usuario']);
            $('#id_usu').val(datos['id_usuario']);
          
             
        }
    });
}





function MostrarRol(){
    $.ajax({
       
        url: "../procesos/obtenDatosUsuarioRol.php",
         success: function (r) {
            datos = jQuery.parseJSON(r);
          if (datos.length > 0) {
                $("#rolcombo").append("<option value = '0'> --Elegir-- </option>");
                var i;
                for (i = 0; i < datos.length; i++) {
                    $("#rolcombo").append("<option value = '" + datos[i][0] + "'>" + datos[i][1] + "</option>");
                }
            }
        }
    });
}


function MostrarRegion(){
    $.ajax({
       
        url: "../procesos/obtenDatosUsuarioRegion.php",
         success: function (r) {
            datos = jQuery.parseJSON(r);
          if (datos.length > 0) {
                $("#regcombo").append("<option value = '0'> --Elegir-- </option>");
                var i;
                for (i = 0; i < datos.length; i++) {
                    $("#regcombo").append("<option value = '" + datos[i][0] + "'>" + datos[i][1] + "</option>");
                }
            }
        }
    });
}




function MostrarSupervisor(){
    $.ajax({
       
        url: "../procesos/obtenDatosUsuarioSupervisor.php",
         success: function (r) {
            datos = jQuery.parseJSON(r);
          if (datos.length > 0) {
                $("#supcombo").append("<option value = '0'> --Elegir-- </option>");
                var i;
                for (i = 0; i < datos.length; i++) {
                    $("#supcombo").append("<option value = '" + datos[i][0] + "'>" + datos[i][1] + "</option>");
                }
            }
        }
    });
}



function MostrarSupervisorA(){
    $.ajax({
       
        url: "../procesos/obtenDatosUsuarioSupervisor.php",
         success: function (r) {
            datos = jQuery.parseJSON(r);
          if (datos.length > 0) {
                $("#supcomboA").append("<option value = '0'> --Elegir-- </option>");
                var i;
                for (i = 0; i < datos.length; i++) {
                    $("#supcomboA").append("<option value = '" + datos[i][0] + "'>" + datos[i][1] + "</option>");
                }
            }
        }
    });
}




$('#btnAgregarnuevo').click(function () {

     datos = $('#frmnuevo').serialize();

    if ($('#nombreusu').val() == "" || $('#clave').val() == "" || $('#personal').val() == ""
        || $('#rolcombo').val() == 0 || $('#regcombo').val() == 0 || $('#supcombo').val() == 0) {
              alertify.error("Revise los datos");
    } else {
        $.ajax({
            type: "POST",
            data: datos,
            url: "../procesos/agregarUsuario.php",
            success: function (r) {
                if (r != 1) {
                    AgregarRegion();
                    AgregarAccesos();
                    $("#personal").val("");
                    $("#nombreusu").val("");
                    $("#clave").val("");
                     $('#rolcombo').empty().append('whatever');
                      $('#regcombo').empty().append('whatever');
                      $('#supcombo').empty().append('whatever');
                    $('#agregarnuevosdatosmodal').modal('hide');
                    $('#tablaDatatable').load('../capa_presentacion/tablaUsuario.php');
                    alertify.success("Agregado con exito");
                } else {
                    alertify.error("Usuario ya existe");
                }
            }
        });
    }
});

function AgregarRegion() {
     datos=$("#frmnuevo").serialize();
        $.ajax({
            type: "POST",
            data: datos,
            url: "../procesos/AgregarRegion.php",
             success: function (data) {
            datos = jQuery.parseJSON(data);
            console.log(datos);
         
        }
            
        });
    }

function AgregarAccesos() {
     datos=$("#frmnuevo").serialize();
        $.ajax({
            type: "POST",
            data: datos,
            url: "../procesos/AgregarAcceso.php",
             success: function (data) {
            datos = jQuery.parseJSON(data);
            console.log(datos);
         
        }
            
        });
    }



     