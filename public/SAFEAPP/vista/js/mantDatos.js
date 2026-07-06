 

  $("#btncargar").click(function () {

  
    $.ajax({
       
        url: "../procesos/subirDatos.php",
       
         success: function (data) {
            datos = jQuery.parseJSON(data);
        alertify.success("Cargado con Exito");
          
        }
    });



    });


