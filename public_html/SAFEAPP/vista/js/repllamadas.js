
   

 $('#mostrar').click(function () {


if($("#desde").val()===""){
 alertify.error("seleccione fecha");

}else{

    if($("#hasta").val()===""){

         alertify.error("seleccione fecha");
    }else{
          var desde =$("#desde").val();

          var hasta =$("#hasta").val();

          $('#tablaDatatable').load('../capa_presentacion/tablaasig.php?desde='+desde+'&hasta='+hasta);
          }
}
       
        
    });


                    

  $("#excel").click(function () {

    if($("#desde").val()===""){
 alertify.error("seleccione fecha");

}else{

    if($("#hasta").val()===""){

         alertify.error("seleccione fecha");
    }else{
              $("#frmrepllamada").submit();
       
          }
}
    
       

       
    });




