$(document).ready(function () {
       TraerDatosSupervisor();
});

                
  $("#excel").click(function () {

    if($("#desde").val()==="" || $("#hasta").val()==="" ){
 alertify.error("SELECCIONE FECHA");

}else{

    if($("#supervisor").val()==='0'){

         alertify.error("SELECCIONE SUPERVISOR");
    }else{
              $("#frmrephistoricousu").submit();
       
          }
}
    
       
    });


      function TraerDatosSupervisor(){
        $('#supervisor').empty().append('whatever');
    $.ajax({
       
        url: "../procesos/obtenDatosSupervisor.php",
         success: function (data) {
            datos = jQuery.parseJSON(data);

          if (datos.length > 0) {
                $("#supervisor").append("<option value = '0'> --Elegir-- </option>");
                var i;
                for (i = 0; i < datos.length; i++) {
                    $("#supervisor").append("<option value = '" + datos[i][0] + "'>" + datos[i][1] + "</option>");
                }
            }
        }
    });
}




     
  
  



   
   

