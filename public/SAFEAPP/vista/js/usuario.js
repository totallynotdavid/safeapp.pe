$(document).ready(function () {
    $('#tablaDatatable').load('../capa_presentacion/tablaUsuario.php');
    MostrarRol();
});

$('#btnAgregarnuevo').click(function () {
    datos = $('#frmnuevo').serialize();
    if ($('#nombre').value === "") {
        alertify.error("Completar datos");
    } else {
        $.ajax({
            type: "POST",
            data: datos,
            url: "../procesos/agregarRol.php",
            success: function (r) {
                if (r != 1) {
                    $('#frmnuevo')[0].reset();
                    $('#tablaDatatable').load('rol.php');
                    alertify.success("agregado con exito :D");
                } else {
                    alertify.error("Fallo al agregar :(");
                }
            }
        });
    }
});

$('#cerrar1').click(function () {
    var combo = $("#selcombo").val();
    $('#selcombo').empty().append('whatever');
    MostrarRol
});

$('#cerrar2').click(function () {
    var combo = $("#selcombo").val();
    $('#selcombo').empty().append('whatever');
});


$('#btnActualizar').click(function () {
    datos = $('#frmnuevoU').serialize();
    $.ajax({
        type: "POST",
        data: datos,
        url: "../procesos/actualizarUsuario.php",
        success: function (r) {
            datos = jQuery.parseJSON(r);
            if (r == 1) {
                $('#tablaDatatable').load('tablarol.php');
                alertify.success("Actualizado con exito :D");
            } else {
                alertify.error("Fallo al actualizar :(");
            }
        }
    });
});


function FrmActualizar(id) {
    $.ajax({
        type: "POST",
        data: "id_usuario=" + id,
        url: "../procesos/obtenDatosUsuario.php",
        success: function (r) {
            datos = jQuery.parseJSON(r);
            $('#nombreU').val(datos['usuario']);
            $('#claveU').val(datos['clave']);
            $('#selcomboU').val(datos['id_rol']);
            $('#personalU').val(datos['personal']);
            $('#estado1U').val(datos['clave']);
        }
    });
}

function eliminarDatos(id) {
    alertify.confirm('Eliminar Rol', '¿Seguro de eliminar este rol?',
            function () {
                $.ajax({
                    type: "POST",
                    data: "id_rol=" + id,
                    url: "../procesos/eliminarRol.php",
                    success: function (r) {
                        $('#tablaDatatable').load('rol.php');
                        alertify.success("Eliminado con exito !");
                    }
                });
                alertify.success('Listo!')
            },
            function () {
                alertify.error('Cancelado')
            });
}

function MostrarRol(){
    $.ajax({
       
        url: "../procesos/obtenDatosUsuarioRol.php",
         success: function (r) {
            datos = jQuery.parseJSON(r);
          if (datos.length > 0) {
                $("#selcombo").append("<option value = '0'> --Elegir-- </option>");
                var i;
                for (i = 0; i < datos.length; i++) {
                    $("#selcombo").append("<option value = '" + datos[i][0] + "'>" + datos[i][1] + "</option>");
                }
            }
        }
    });
}

