$(document).ready(function () {
    //bloquemos la pantalla cuando se realiza apetion ajaz del gri de alumnos
    $(document).on('pjax:send', '#pjax-alumnos', function() {
        
        $('body').loading({message: 'Aguarde procesando...'});
    });       
    $(document).on('pjax:end', '#pjax-alumnos', function() {    
        $('body').loading('stop');         
        $('#form-search-alumnos .btn-search').button('reset');
    });     
});



$(document).on('pjax:error', '#pjax-servicios-convenio', function() {
    reportarNotificacionGral('Se produjo un error al actualizar la lista de servicios a adherir al convenio','error',true);
}); 

/* Funcionalidad para elalta del conveio */
/*
 * Incorpora un determinado servicio a un Convenio de Pago Establecido
 * 
 * @param {type} idServicio
 * @returns {undefined}
 */
function adherirServicio(idServicio){    
    let serviciosConvenio = $('#serviciosconvenio').val();
    let familiaConvenio = $('#familiaconvenio').val();
    
    if(serviciosConvenio=='')
        serviciosConvenio=idServicio;
    else        
        serviciosConvenio+=','+idServicio;
   
        dataOptionPjax = 
        {          
            url: $('#urlreload').val(), 
            container: '#pjax-servicios-convenio', 
            timeout: false,
            data: 'familia='+ familiaConvenio +'&servicios=' +serviciosConvenio
        };
            
        $.pjax.reload(dataOptionPjax);  
}

//
///*
// * Llamada ajax para remover un servio puesto en el conveniode pago 
// * a generar.
// * 
// * @param {type} idServicio
// * @returns {undefined}
// */
function quitarServicio(idServicio){   
    idServicio = idServicio.toString();
    let serviciosConvenio = $('#serviciosconvenio').val(); 
    let familiaConvenio = $('#familiaconvenio').val();   
    
    var arrayServicios = serviciosConvenio.split(",");
    var index = arrayServicios.indexOf(idServicio);
    if (index > -1) {
        arrayServicios.splice(index, 1);
    }
    
    var nuevosServiciosConvenio = arrayServicios.join(',');
        dataOptionPjax = 
        {          
            url: $('#urlreload').val(), 
            container: '#pjax-servicios-convenio', 
            timeout: false,
            data: 'familia='+ familiaConvenio +'&servicios=' +nuevosServiciosConvenio
        };
            
        $.pjax.reload(dataOptionPjax);
}

function enviarAConvenio(){
    let serviciosConvenio = $('#serviciosconvenio').val();
    let familiaConvenio = $('#familiaconvenio').val();
    
    var UrlGeneracion = $('#url-generar-planpago').val();
    if (UrlGeneracion.indexOf('?') !== -1)
        UrlGeneracion =  UrlGeneracion +  '&familia='+ familiaConvenio;
    else
        UrlGeneracion = UrlGeneracion +  '?familia='+ familiaConvenio;        
    
    
    if(serviciosConvenio=='' ||  serviciosConvenio==null || serviciosConvenio.length==0){
        bootbox.confirm({
            message: "Está a punto de iniciar un Convenio de Pago sin servicios adheridos; desea proseguir???",
            buttons: {
                confirm: {
                    label: 'Si',
                    className: 'btn-success'
                },
                cancel: {
                    label: 'No',
                    className: 'btn-danger'
                }
            },
            callback: function (result) {
                if(result===true){     
                    window.location = UrlGeneracion; 
                }
            }
        });   
    }else{
        UrlGeneracion+= '&servicios=' +serviciosConvenio;
        window.location = UrlGeneracion;
    }
         
}

///******************************/
////esto estaba cuando usabamos sessiones
//function getUncheckeds(){
//    var unch = [];
//    /*corrected typo: $('[name^=someChec]') => $('[name^=misservicios]') */
//    $('[name^=selection]').not(':checked,[name$=all]').each(function(){unch.push($(this).val());});
//    return unch.toString();
//}
//       
//$('#pjax-servicios-convenio').on('pjax:beforeSend', function (event, data, status, xhr, options) {
//    seleccionados = $('#gridServiviosCP').yiiGridView('getSelectedRows').toString();     
//    no_seleccionados = getUncheckeds();
//    status.data = status.data+'&selects='+seleccionados;
//    status.data = status.data+'&noselects='+no_seleccionados;       
//});
//
//
//$(document).ready(function(){ 
//    $('#form-servicios').on('beforeSubmit',function(e, messages){
//        $('#btn-generar-convenio').attr('disabled','disabled');
//        $('#btn-generar-convenio').html('<i class=\'fa fa-spinner fa-spin\'></i> Procesando...');
//        
//        alert("asda");
//        seleccionados = $('#gridServiviosCP').yiiGridView('getSelectedRows').toString();     
//        no_seleccionados = getUncheckeds();
//        $('#selects').val(seleccionados);
//        $('#noselects').val(no_seleccionados);
//        $('#envios').val(1);        
//       
//    });
//});
//

///**************************************/
///*** manejo de las cuotas **/
function addCuota(url){     
    var ordn = parseInt($('#ordn').val()) + 1;    
   
    $.ajax({
        url: url,
        dataType: 'json',
        type: 'POST',
        data: 'nro='+ordn,
        beforeSend: function(xhr){

        },
        success:function(data){            
            dataCuota = data.vista;
            $('#misCuotas').prepend(dataCuota);
            $('#ordn').val(ordn);       
        },
        error: function(xhr) {
            reportarNotificacionGral(xhr.responseText, 'error', true);
        }
    });   
}

function eliminarcuota(nrocuota){
    if($('.groupcuota').length>1){       
        $('#divcuota-'+nrocuota).remove();    
    }else{
        reportarNotificacionGral('No se puede eliminar la cuota, al menos debe existir una de ellas', 'error', true);
    }
}

///****************************************/

$('#form-convenio').on('beforeValidate',function(e){
    $('.btn-submit-envio').button('loading');
});
$('#form-convenio').on('afterValidate',function(e, messages){
    if ($('#form-convenio').find('.has-error').length > 0){
        $('.btn-submit-envio').button('reset');
    }
});    

/*************************************************/
/*
 * Procesa una invocacion ajax; para devolver al usuario una descaga de pdf
 * 
 * {type} xhref url invocar procesar elpedido de envio de mail
 * @returns {undefined}
 */
function downPdfConvenio(xhref){
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
    }); 
}   
/*
 * 
 * @param {type} xhref url invocar procesar elpedido de envio de mail
 * @returns {undefined}
 */
function enviarEmailPdfConvenio(xhref){    
    console.log(xhref);
    $("body").loading({message: 'ESPERE... procesando'});
    $.ajax({
        url    : xhref,
        type   : "post",            
        dataType: "json",
        success: function (response){             
            $("body").loading('stop');  
            if(response.result_error==='0'){
                new PNotify({
                    title: 'Correcto',
                    text: 'Se envio de forma correcta el correo.',
                    icon: 'glyphicon glyphicon-envelope',
                    type: 'success'
                }); 
            }else{
                new PNotify({
                    title: 'Error',
                    text: response.message,
                    icon: 'glyphicon glyphicon-envelope',
                    type: 'error'
                });
            }
        },
        error: function(){
            $("body").loading('stop');
            new PNotify({
                title: 'Error',
                text: 'Se produjo un error severo al enviar el correo',
                icon: 'glyphicon glyphicon-envelope',
                type: 'error'
            });
        }
    }); 
}