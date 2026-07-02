$(document).ready(function () {
   TraerDatosAnoMes();

});

$('#btnfiltrar').click(function () {
      
        var ano =$("#ncano").val();
        var periodo =$("#ncperiodo").val();
 $('#tablaDatatableAvz').load('../capa_presentacion/tablaDashboardAvz.php?ano='+ano+'&periodo='+periodo);
    $('#tablaDatatableFija').load('../capa_presentacion/tablaDashboardFija.php?ano='+ano+'&periodo='+periodo);
        $('#tablaDatatableMovil').load('../capa_presentacion/tablaDashboardMovil.php?ano='+ano+'&periodo='+periodo);
          $('#tablaDatatableGlobal').load('../capa_presentacion/tablaDashboardGlobal.php?ano='+ano+'&periodo='+periodo);
     
    });


    function TraerDatosAnoMes(){
      
        
        var fecha = new Date();
        var ano = fecha.getFullYear();

       
        $("#ncano").val(ano);

          var periodo = fecha.getMonth() +1 ;

          if (periodo>9) {
             $("#ncperiodo").val(periodo);
          }else {
             $("#ncperiodo").val('0'+(periodo));
          }
       

       
     $('#tablaDatatableAvz').load('../capa_presentacion/tablaDashboardAvz.php?ano='+ano+'&periodo='+periodo);
    $('#tablaDatatableFija').load('../capa_presentacion/tablaDashboardFija.php?ano='+ano+'&periodo='+periodo);
        $('#tablaDatatableMovil').load('../capa_presentacion/tablaDashboardMovil.php?ano='+ano+'&periodo='+periodo);
          $('#tablaDatatableGlobal').load('../capa_presentacion/tablaDashboardGlobal.php?ano='+ano+'&periodo='+periodo);

}


  




     

// function sumarDias(fecha, dias){
//   fecha.setDate(fecha.getDate() + dias);
//   return fecha;
// }

// var d = new Date();
// console.log(sumarDias(, -5));



         





       






