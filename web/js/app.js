function reportarNotificacionGral(mensaje, tipomsg, hide) {
    new PNotify({
        title: 'Atención',
        text: mensaje,
        icon: 'glyphicon glyphicon-envelope',
        type: tipomsg,
        addclass: "stack-topleft notify-vinculador",
        hide: hide,
    });
}

$(document).ready(function(){
    $(".form-prev-submit").on("beforeValidate",function(e){
        $(".btn-submit-envio").attr("disabled","disabled");
        $(".btn-submit-envio").html("<i class=\'fa fa-spinner fa-spin\'></i> Procesando...");        
    });
    
    $(".form-prev-submit").on("afterValidate",function(e, messages){
        if ( $(".form-prev-submit").find(".has-error").length > 0){
            $(".btn-submit-envio").removeAttr("disabled");
            $(".btn-submit-envio").html("<i class=\'fa fa-save\'></i> Guardar...");
        }
    });
});   

function downListado(xhref){    
    $("body").loading({message: 'ESPERE... procesando'});
    $.ajax({
         url    : xhref,
         type   : "post",            
         dataType: "json",
         success: function (response){ 
            $("body").loading('stop');  
            if(response.result_error==='0'){
                window.location.href = response.result_texto; 
            }else{
                new PNotify({
                    title: 'Error',
                    text: response.message,
                    icon: 'glyphicon glyphicon-envelope',
                    type: 'error'
                });
            }
        },         
    }).done(function(o) {
        $("body").loading('stop');       
    });
}


function downFactura(xhref){
    $("body").loading({message: 'ESPERE... procesando'});
    $.ajax({
        url    : xhref,
        type   : "post",            
        dataType: "json",
        success: function (response){             
             $("body").loading('stop');  
             if(response.result_error==='0'){
                window.location.href = response.result_texto; 
             }else{
                new PNotify({
                    title: 'Error',
                    text: response.message,
                    icon: 'glyphicon glyphicon-envelope',
                    type: 'error'
                });
             }
        },
                error: function(XHR) {
                   $("body").loading('stop');                    
                   if (XHR.statusText == 'Unauthorized')
                    {
                        new PNotify({
                            title: 'ERROR!!!',
                            text: 'USTED NO TIENE LOS PERMISOS SUFICIENTES PARA LLEVAR A CABO LA TAREA SOLICITADA',
                            icon: 'ui-icon ui-icon-mail-closed',
                            opacity: .8,
                            type: 'success'
                           
                        });
                    }else
                    if( ((XHR.status == '403') || ((XHR.status == 403))) && ((XHR.statusText == 'Forbidden'))){
                            new PNotify({
                                title: 'ERROR!!!',
                                text: 'USTED NO TIENE LOS PERMISOS SUFICIENTES PARA LLEVAR A CABO LA TAREA SOLICITADA',
                                icon: 'ui-icon ui-icon-mail-closed',
                                opacity: .8,
                                type: 'success'                           
                            });  
                     }
                }
    }); 
}
