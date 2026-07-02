
function cambiartie() {
    $('#modalTienda').modal({backdrop: false, keyboard: false});
           TraerTienda();
}


   $('#cambiar').click(function () {

        if( $("#nvatienda").val()==="00" ){

                alertify.error("Seleccione Tienda");

        }else{

            datos=$("#frmTienda").serialize();
                 $.ajax({
                    type: "POST",
                    data: datos,
                    url: "../procesos/CambiarEstadoTienda.php",
            success: function (r) {
             
                if (r!=1) {

                            
                    location.href ="principal.php";
                } else {

                }
            }
        });
          } 
        
    });



         function TraerTienda(){
        $('#nvatienda').empty().append('whatever');
         datos=$("#frmTienda").serialize();
    $.ajax({
        type: "POST",
        data: datos,
        url: "../procesos/obtenTienda.php",
         success: function (data) {
            datos = jQuery.parseJSON(data);
            console.log(datos);
          if (datos.length > 0) {
                $("#nvatienda").append("<option value = '00'> --Elegir-- </option>");
                var i;
                for (i = 0; i < datos.length; i++) {
                    $("#nvatienda").append("<option value = '" + datos[i][0] + "'>" + datos[i][1] + "</option>");
                }
            }
        }
    });
        }


   



