
$(document).ready(function () {
    MostrarRegionfiltro();
        TraerDatosAnoMes();
       
        
});

 
    $('#cerrar1').click(function () {
        $('#suptipocom').val("");
        $('#descripcioncom').val("");
        $('#ncregion').val("");
        $('#nccargofijo').val("");
         $("#ncruc").val("");
         $("#ncrazonsocial").val("");
         $("#ncnomcontacto").val("");
         $("#nctelefono1").val("");
         $("#nctelefono2").val("");
         $("#ncdireccion").val("");
         $("#nccoordenadas").val("");
         $("#ncobservacioneseje").val("");
          $("#ncpersonal").val("");
           $("#ncqlineas").val("");
            $("#ncmodalidad").val("");
            $("#ncobservacionesval").val("");
         

        
          });


      $('#cerrar2').click(function () {
       $('#suptipocom').val("");
        $('#descripcioncom').val("");
        $('#ncregion').val("");
        $('#nccargofijo').val("");
         $("#ncruc").val("");
         $("#ncrazonsocial").val("");
         $("#ncnomcontacto").val("");
         $("#nctelefono1").val("");
         $("#nctelefono2").val("");
         $("#ncdireccion").val("");
         $("#nccoordenadas").val("");
         $("#ncobservacioneseje").val("");
          $("#ncpersonal").val("");
           $("#ncqlineas").val("");
            $("#ncmodalidad").val("");
         
            $("#ncobservacionesval").val("");
          

       

          });

    

    $('#btnRegistrar').click(function () {

        if ($("#ncestado").val()== 'PENDIENTE' ) {
             datos=$("#frmNCF").serialize();
                 $.ajax({
                    type: "POST",
                    data: datos,
                    url: "../procesos/agregarncfavanzadovalidacion.php",
            success: function (r) {
             
                if (r!=1) {

                              alertify.success("Registrado con exito");

                              $('#modalEditar').modal('hide');
                                $('#suptipocom').val("");
                                $('#descripcioncom').val("");
                                $('#ncregion').val("");
                                $('#nccargofijo').val("");
                                 $("#ncruc").val("");
                                 $("#ncrazonsocial").val("");
                                 $("#ncnomcontacto").val("");
                                 $("#nctelefono1").val("");
                                 $("#nctelefono2").val("");
                                 $("#ncdireccion").val("");
                                 $("#nccoordenadas").val("");
                                 $("#ncobservacioneseje").val("");
                                 $("#ncpersonal").val("");
                                 $("#ncqlineas").val("");
                                 $("#ncmodalidad").val("");
                                 $("#ncobservacionesval").val("");
                                var validacionf =$("#ncvalidacionf").val();
        var tienda =$("#ncregionf").val();
        var ano =$("#ncano").val();
        var periodo =$("#ncperiodo").val();
$('#tablaDatatable').load('../capa_presentacion/tabla_nc_fija_avanzado_validacion.php?ano='+ano+'&periodo='+periodo+'&validacionf='+validacionf+'&tienda='+tienda);

     
                  
                } else {

                       alertify.error("Fallo al agregar");

                }

            }
        });
                 
             }else{

             alertify.error("EL PEDIDO ESTA:"+$("#ncestado").val());
        }
        
    });






    $('#btnfiltrar').click(function () {
        var validacionf =$("#ncvalidacionf").val();
        var tienda =$("#ncregionf").val();
        var ano =$("#ncano").val();
        var periodo =$("#ncperiodo").val();
$('#tablaDatatable').load('../capa_presentacion/tabla_nc_fija_avanzado_validacion.php?ano='+ano+'&periodo='+periodo+'&validacionf='+validacionf+'&tienda='+tienda);

     
    });








    function TraerDatosAnoMes(){
        var validacionf =$("#ncvalidacionf").val();
        var tienda =$("#ncregionf").val();
        var fecha = new Date();
        var ano = fecha.getFullYear();

        console.log(fecha);
        $("#ncano").val(ano);

          var periodo = fecha.getMonth() +1 ;

          if (periodo>9) {
             $("#ncperiodo").val(periodo);
          }else {
             $("#ncperiodo").val('0'+(periodo));
          }
       
$('#tablaDatatable').load('../capa_presentacion/tabla_nc_fija_avanzado_validacion.php?ano='+ano+'&periodo='+periodo+'&validacionf='+validacionf+'&tienda='+tienda);

        
    
}

function MostrarRegionfiltro(){
    
    $.ajax({
       
        url: "../procesos/obtenDatosUsuarioRegion.php",
         success: function (r) {
            datos = jQuery.parseJSON(r);
          if (datos.length > 0) {
                
                var i;
                for (i = 0; i < datos.length; i++) {
                    $("#ncregionf").append("<option value = '" + datos[i][0] + "'>" + datos[i][1] + "</option>");
                }
            }
        }
    });
}



    function TraerDatosTabla(idcargo) {
  
     
        $.ajax({
            type: "POST",
            data: "idcargo=" + idcargo,
            url: "../procesos/obtenNotacargo_avanzado_validacion.php",
            success: function (data) {
            datos = jQuery.parseJSON(data);
            $('#idcargo').val(idcargo);
            $('#ncqlineas').val(datos['q_lineas']);
            $('#ncmodalidad').val(datos['modalidad']);
            $('#suptipocom').val(datos['subtipo']);
            $('#descripcioncom').val(datos['descripcion']);
            $('#nccargofijo').val(datos['cargo_fijo']);
            $('#ncruc').val(datos['ruc']);
            $('#ncrazonsocial').val(datos['razon_social']);
            $('#ncnomcontacto').val(datos['contacto']);
            $('#nctelefono1').val(datos['telefono1']);
            $('#nctelefono2').val(datos['telefono2']);
            $('#ncdireccion').val(datos['direccion']);
            $('#nccoordenadas').val(datos['coordenadas']);
            $('#ncregion').val(datos['zonal']);
            $('#ncpersonal').val(datos['personal']);
            $('#ncobservacioneseje').val(datos['comentario']);
            $('#ncestado').val(datos['estado']);
            $('#ncobservacionesval').val(datos['comentario_validador']);
            $('#nctecnologia').val(datos['tecnologia']);
            $('#ncejecutivotdp').val(datos['ejecutivo_telefonica']);
            $('#ncsupervisor').val(datos['supervisor']);

       
            }
            
        });
    }

 $("#btnexportar").click(function () {
  
              $("#frmrepexportarvalidacion").submit();
       
       
    });



