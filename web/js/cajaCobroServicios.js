/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function () {
    /*
    $('#tiket-id_tipopago').on('change',function() {  
        if ($(this).val() == '1'){           
            $('#tiket-id_cuentapagadora').val('1');
        }else{           
            $('#tiket-id_cuentapagadora').prop('selectedIndex','1')
            $('#tiket-id_cuentapagadora').val('2');
        }
    });    
    */
   
    $("#form-empadronamiento").on("beforeValidate",function(e){
        $("#btn-envio").attr("disabled","disabled");
        $("#btn-envio").html("<i class=\'fa fa-spinner fa-spin\'></i> Procesando...");        
    });
    
    $("#form-empadronamiento").on("afterValidate",function(e, messages){
        if ( $("#form-empadronamiento").find(".has-error").length > 0){
            $("#btn-envio").removeAttr("disabled");
            $("#btn-envio").html("<i class=\'fa fa-save\'></i> Guardar...");
        }
    });      
}); 

/* buscamos la familia/cliente  */
$(document).ready(function () {    
    $("#btn-buscarfamilia").click(function(){ 
        var  url = $(this).attr("value"); 
        $.ajax({
            url    : url,
            success: function (response){
                $("#modalfamilia").modal("show").find("#modalContent").html(response);    
            },
            error: function (xhr) {
                var mensajeError = '';
                if(xhr.status == 403 && xhr.statusText=='Forbidden')  
                    mensajeError= 'Usted no dispone de los permisos suficientes para realizar esta tarea';
                else
                    mensajeError=xhr.responseText;
                reportarNotificacionGral(mensajeError, 'error', true);   
            }
        });
    });   
    
    /*
     * Capturamos el evento pblicado de la familia seleccionada
     */
    $("body").on("familia:seleccionada", function(event, familia){ 
        console.log(familia);
        $("#tiket-id_cliente").val(familia.id);
        $("#apellidoFamilia").val(familia.apellidos);
        
        var dni_familia = familia.cuil_afip_pago;
        
        if(dni_familia==='' || dni_familia === null){
            reportarNotificacionGral("Atención, la folio/familia seleccionada no dispone de CUIL");
        }else
            $("#tiket-dni_cliente").val(familia.cuil_afip_pago);
        
        $("#modalfamilia").modal("hide");
    });    
});     


$(document).ready(function () {
    $("body").on('click',"#btn-agregar-servicio-impago", function () {
        alert("asd");
        var  url = $(this).attr("value"); 
        var familia = $("#tiket-id_cliente").val();
        url = url+"?familia="  +familia;      
        $.ajax({
            url    : url,
            success: function (response){
                $("#modalserviciosimpagos").modal("show").find("#modalContent").html(response);    
            },
            error: function (xhr) {
                var mensajeError = '';
                if(xhr.status == 403 && xhr.statusText=='Forbidden')  
                    mensajeError= 'Usted no dispone de los permisos suficientes para realizar esta tarea';
                else
                    mensajeError=xhr.responseText;
                reportarNotificacionGral(mensajeError, 'error', true);   
            }
        });
    });   
    
    /*
     * Capturamos el evento pblicado de la familia seleccionada
     */
    $("body").on("serviciodeuda:seleccionado", function(event, servicio){ 
        $("#modalserviciosimpagos").modal("toggle");
       
        let cuotasEnTiket = $('#cuotascp-tiket').val();
        let serviciosEnTiket = $('#servicios-tiket').val(); 
        
        let familia = $('#tiket-id_cliente').val();        
        let urlreload = $('#urlreload').val();        
        
        let idNuevoServicio = servicio.idservicio;
        let tipoNuevoServicio = servicio.tiposervicio;
        
      
        if(tipoNuevoServicio=='2' && serviciosEnTiket=='')
            serviciosEnTiket=idNuevoServicio;
        else
        if(tipoNuevoServicio=='2')
            serviciosEnTiket+=','+idNuevoServicio;
   
        if(tipoNuevoServicio=='1' && cuotasEnTiket=='')
            cuotasEnTiket=idNuevoServicio;
        else  
        if(tipoNuevoServicio=='1')
            cuotasEnTiket+=','+idNuevoServicio;
            
        dataOptionPjax = 
        {          
            url: urlreload, 
            container: '#pjax-servicios-tiket', 
            timeout: false,
            data: 'familia='+familia+'&selectServicios=' +serviciosEnTiket+'&selectCuotasCP=' +cuotasEnTiket
        };
            
        $.pjax.reload(dataOptionPjax); 
    });    
      
   
    
    
});     





$(document).ready(function(){
    


    $('#form-ingresos').on('beforeValidate',function(e){
        $('#btn-envio').attr('disabled','disabled');
        $('#btn-envio').html('<i class=\'fa fa-spinner fa-spin\'></i> Procesando...');        
    });
    
    $('#form-ingresos').on('afterValidate',function(e, messages){
        if ($('#form-ingresos').find('.has-error').length > 0){
            $('#btn-envio').removeAttr('disabled');
            $('#btn-envio').html('<i class=\'fa fa-save\'></i> Aceptar Cobro...');
        }
    });
    
    
    ///////////*********/
    $('#caja-cobroservicio-formapago').on('change',function() {  
        alert('dfgf');
        if ($(this).val() == '1'){           
            $('#caja-cobroservicio-cuentapagadora').val('1');
        }else{           
            $('#caja-cobroservicio-cuentapagadora').prop('selectedIndex','1')
            $('#caja-cobroservicio-cuentapagadora').val('2');
        }
    });


});