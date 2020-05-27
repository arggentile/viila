/*  listado  */
$(document).ready(function () {
    $(document).on('pjax:beforeSend', '#pjax-familias', function() { 
        $("body").loading({message: 'Aguarde procesando....'});
    });
    $(document).on('pjax:complete', '#pjax-familias', function() { 
        $("body").loading('stop');
    });
});       

/************************************************************/
/*
 * Formulario carga de grupo familiar
 */
$('#grupofamiliar-id_pago_asociado').on('change',function() { 
   val = $(this).val();

   $('#grupofamiliar-cbu_cuenta').attr('readonly','readonly');
   $('#grupofamiliar-nro_tarjetacredito').attr('readonly','readonly');
   $('#grupofamiliar-tarjeta_banco').attr('readonly','readonly');  
   $('#grupofamiliar-prestador_tarjeta').attr('readonly','readonly'); 
   
    if (val == '4'){ 
        $('#grupofamiliar-cbu_cuenta').removeAttr('readonly'); 
        $('#grupofamiliar-tarjeta_banco').removeAttr('readonly'); 
    }
    if (val== '5'){        
        $('#grupofamiliar-nro_tarjetacredito').removeAttr('readonly');
        $('#grupofamiliar-tarjeta_banco').removeAttr('readonly'); 
        $('#grupofamiliar-prestador_tarjeta').removeAttr('readonly'); 
    }       
});

$(document).ready(function(){    
    $('#form-grupofamiliar').on('beforeValidate',function(e){        
        $('#btn-envio').attr('disabled','disabled');
        $('#btn-envio').html('<i class=\'fa fa-spinner fa-spin\'></i> Procesando...');        
    });
    $('#form-grupofamiliar').on('afterValidate',function(e, messages){
        if ($('#form-grupofamiliar').find('.has-error').length > 0){
            $('#btn-envio').removeAttr('disabled');
            $('#btn-envio').html('<i class=\'fa fa-save\'></i> Guardar...');
        }
    });    
});     


/************************************************************/
/*
 * Funcionalidad para la asignaciòn de responsables al grupo familiar-
 * 
 * Lo primeromeabre el modal con el grid para busqueda y asigancion;
 * la segunda es la funcion encarga de asignaciòn
 */
$("#btn-asignar-responsable").click(function(){     
    xhref = $(this).attr("value");    
    $.ajax({
        url    : xhref, 
        dataType: "json",
        success: function (response){                
            $('#modalAsignacionResponsable').modal('show').find('#modalContent').html(response.vista); 
        },
        error: function(xhr){
                reportarNotificacionGral(xhr.responseText, 'error', true);   
        },
        
    });
    
});

function asignarResponsable(btn){
    
    xhref = $(btn).attr("value");
    
    $('.bt-asign-responsablefamilia').attr('disabled','disabled');
    $('.bt-asign-responsablefamilia').html('<i class=\'fa fa-spinner fa-spin\'></i> Procesando...');
        
    responsbale = $('input:radio[name=radioButtonSelection]:checked').val(); 
  
    $.ajax({
        url    : xhref,
        type   : "get",
        data   : {"tipores": $('#tipores').val(), "familia": $('#familia').val()},
        success: function (response) {
                $("#modalAsignacionResponsable").modal("toggle");                 
                $.pjax.reload({container:'#pjax-responsables', timeout:false}); 
        },
        error  : function (xhr) {
            reportarNotificacionGral(xhr.responseText, 'error', true);   
            $('.bt-asign-responsablefamilia').removeAttr('disabled','disabled');
            $('.bt-asign-responsablefamilia').html('Asignar');
                
        }
    }).done();
}



function cargarResponsable(btn){  
    xhref = jQuery(btn).attr("value");
    
    $.ajax({
        url    : xhref,                 
        dataType: "json",
        success: function (response){              
            if(response.error==='0'){
                $('#modalAsignacionResponsable').modal('show').find('#modalContent').html(response.vista);   
            }else{
                new PNotify({
                    title: 'Error',
                    text: response.message,
                    icon: 'glyphicon glyphicon-envelope',
                    type: 'error'
                });
            }
        },
        error: function(xhr){
            reportarNotificacionGral(xhr.responseText, 'error', true);   
        }
    });   
}


function actualizarResponsable(xhref){ 
    $.ajax({
        url    : xhref,                 
        dataType: "json",
        success: function (response){              
            if(response.error==='0'){
                $('#modalAsignacionResponsable').modal('show').find('#modalContent').html(response.vista);   
            }else{
                new PNotify({
                    title: 'Error',
                    text: response.message,
                    icon: 'glyphicon glyphicon-envelope',
                    type: 'error'
                });
            }
        },
        error: function(xhr){
            reportarNotificacionGral(xhr.responseText, 'error', true);  
        }
    });
}



function quitarResponsable(xhref){       
    bootbox.confirm({
        message: "Está seguro que deséa separar al Responsable del Grupo Familiar. <br /> \
                   Este proceso no eliminara los datos de la persona; solo remueve el responsbale del Grupo Familiar?",
        buttons: {
            confirm: {
                label: '<i class="glyphicon glyphicon-ok"></i> Si',
                className: 'btn-success'
            },
            cancel: {
                label: '<i class="glyphicon glyphicon-remove"></i> No',
                className: 'btn-danger'
            }
        },
        callback: function (result) {
            $("body").loading({message: 'AGUARDE... procesando.'});
            if(result===true){                
                $.ajax({
                     url    : xhref,
                     type   : "get",            
                     dataType: "json",
                     success: function (response){
                         $("body").loading('stop');
                         if(response.error==0){                             
                            new PNotify({
                                title: 'Correcto',
                                text: response.mensaje,
                                icon: 'glyphicon glyphicon-envelope',
                                type: 'success'
                            });                            
                            $.pjax.reload({container:"#pjax-responsables",timeout:false});                            
                         }
                     },
                     error  : function (xhr) { 
                        $("body").loading('stop'); 
                        reportarNotificacionGral(xhr.responseText, 'error', true);      
                     }
                });
            }else{
                $("body").loading('stop');    
            }            
        }
    });
    
    return false;       
} //fin deleteAjax 

$("body").on("beforeSubmit", "form.form-carga-responsable", function () {
    var grillaajax = '#pjax-responsable';
    var form = $(this);
    
    $('#btn-enviar').button('loading');
    
    // submit form        
    $.ajax({
        url    : form.attr("action"),
        type   : "post",
        data   : form.serialize(),
        dataType: "json",
        success: function (response) {
            if(response.error == 0){                                   
                if(response.carga == 1){
                    $("#modalAsignacionResponsable").modal("toggle"); 
                    grillaajax = '#pjax-responsables';
                    $.pjax.reload({container:grillaajax, timeout:false});                    
                }
                else
                if (response.carga==0){    
                    $('#btn-enviar').button('reset');
                    $("#modalAsignacionResponsable").find("#modalContent").html("");
                    $("#modalAsignacionResponsable").modal("show").find("#modalContent").html(response.vista);
                }
            }            
        },
        error  : function (xhr) {
            reportarNotificacionGral(xhr.responseText, 'error', true); 
            $('#btn-enviar').button('reset');
        }
    });

    return false;
});



$(document).ready(function () {
    $("body").on("click",".btn-delete-alumno", function(e){
        e.preventDefault();
        var href = $(this).attr('data-url');
       
        bootbox.confirm({
            message: "Está seguro que desea Eliminar al Alumno?",
            buttons: {
                confirm: {
                    label: '<i class="glyphicon glyphicon-ok"></i> Si',
                    className: 'btn-success'
                },
                cancel: {
                    label: '<i class="glyphicon glyphicon-remove"></i> No',
                    className: 'btn-danger'
                }
            },
            callback: function (result) {
                if(result===true){
                    // submit form        
                    $.ajax({
                        url    : href,
                        type   : "get",                       
                        dataType: "json",
                        success: function (response) {
                            if(response.error == 0){                                   
                                                                
                                    grillaajax = '#pjax-alumnos';
                                    $.pjax.reload({container:grillaajax, timeout:false});                    
                                
                                
                            }            
                        },
                        error  : function (xhr) {
                            reportarNotificacionGral(xhr.responseText, 'error', true); 
                            $('#btn-enviar').button('reset');
                        }
                    });
                }
            }
        });
    
    });
});
