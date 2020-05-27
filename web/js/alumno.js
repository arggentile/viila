function inactivarAlumno(xhref){    
    grillaajax = '#pjax-alumnos';
    
    bootbox.confirm({
        message: "Está seguro que desea INACTIVAR al Alumno?",
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
                $("body").loading({message: 'Aguarde procesando...'});
                $.ajax({
                     url    : xhref,
                     type   : "post",            
                     dataType: "json",
                     success: function (response){  
                        $("body").loading('stop');
                        if(response.error==0){
                            $('#grid-alumnos').yiiGridView('applyFilter');
                        }else{                            
                            reportarNotificacionGral(response.mensaje, 'error', true);              
                        }                        
                    },
                    error  : function (xhr) {    
                        $("body").loading('stop');
                        reportarNotificacionGral(xhr.responseText, 'error', true);               
//                        if(error.status == 403 && error.statusText=='Forbidden') {                            
//                            new PNotify({
//                                title: 'Error',
//                                text: 'Usted no dispone de los permisos suficientes para realizar esta tarea',
//                                icon: 'glyphicon glyphicon-envelope',
//                                type: 'error'
//                            });
//                        }
                     }
                     
                 });
            }
        }
    });
    return false;       
}

function activarAlumno(xhref){    
    grillaajax = '#pjax-alumnos';
    
    bootbox.confirm({
        message: "Está seguro que desea ACTIVAR al Alumno?",
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
                $("body").loading({message: 'Aguarde procesando...'});
                $.ajax({
                     url    : xhref,
                     type   : "post",            
                     dataType: "json",
                     success: function (response){
                        $("body").loading('stop');
                        if(response.error==0){                             
                            $('#grid-alumnos').yiiGridView('applyFilter');         
                        }else{       
                            reportarNotificacionGral(response.mensaje, 'error', true);                             
                        }                        
                    },
                    error  : function (xhr) {
                        $("body").loading('stop');
                        reportarNotificacionGral(xhr.responseText, 'error', true);                         
                    }
                 });
            }
        }
    });
    return false;       
} 

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
                    
                    $("body").loading({message: 'Aguarde procesando...'});
                    window.location.href = href;
                  
                }
            }
        });
    
    });
});

/****************************************************************************/
/****************************************************************************/
$(document).ready(function () {
    //bloquemos la pantalla cuando se realiza apetion ajaz del gri de alumnos
    $(document).on('pjax:send', '#pjax-alumnos', function() {
        
        $('body').loading({message: 'Aguarde procesando...'});
    });       
    $(document).on('pjax:end', '#pjax-alumnos', function() {    
        $('body').loading('stop');         
        $('#form-search-alumnos .btn-search').button('reset');
    });     
    //al finalizar lallamada ajax del render de pjax del grid de alumnos
    //actulizamos el combio de divisiones segun el establecimiento
    /*
    $(document).on('pjax:complete', '#pjax-alumnos', function() {      
        establecimiento = $('#alumnosearch-establecimiento').val();        
        $.ajax({
            url    : 'index.php?r=establecimiento/drop-mis-divisionesescolares',
            type   : 'GET',  
            data: { 'idEst': establecimiento},
            success: function (data){ 
                  $('#alumnosearch-id_divisionescolar').html('Seleccione');
                  $('#alumnosearch-id_divisionescolar').html(data);       
            },
        }).done($('body').loading('stop'));           
    });     
    */
});

/****************************************************************************/
/******************************* CARGA ALUMNO, BUSCAR FAMILIAS***************/
$(document).ready(function () {
    
    $("#buscarFamiliaBtn").click(function(){                
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
        $("#mifamilia").val(familia.id);
        $("#apellidoFamilia").val(familia.apellidos);
        $("#folioFamilia").val(familia.folio);   
        $("#responsableFamilia").val(familia.responsablePrincipal);
        $("#modalfamilia").modal("hide");
    });    
      
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

/****************************************************************************/
/****************************************************************************/
/*
 * Funciones para la asignacion y quitas de bonificaciones alumno
 */
$(document).ready(function () {
    $("#btn-asignar-bonificacion").click(function(){     
        xhref = $(this).attr("value")
        $.ajax({
            url    : xhref,                 
            dataType: "json",
            success: function (response){            
                if(response.error==='0'){                    
                    $('#modalBonificaciones').modal('show').find('#modalContent').html(response.vista);   
                }else{
                    reportarNotificacionGral(response.mensaje, 'error', true);                     
                }
            },
            error: function(xhr){
                reportarNotificacionGral(xhr.responseText, 'error', true);         
            }
        });

    });  

    $("body").on("beforeSubmit", "form#formAsignacionBonificacion", function () {
        var form = $(this);  

        $('#grilla-ajax').val('#pjax-bonificaciones');
        grillaajax = $('#grilla-ajax').val();

        // submit form
        $("body").loading({message: 'AGUARDE... procesando.'});
        $.ajax({
            url    : form.attr("action"),
            type   : "post",
            data   : form.serialize(),
            dataType: "json",
            success: function (response) {
                if(response.error == 0){                                   
                    if((response.carga == '1') && (response.error == '0')){ 

                        $.pjax.reload({container:grillaajax,timeout:false}); 
                        $(document).on('pjax:complete',grillaajax, function() {                               
                            
                            new PNotify({
                                    title: 'Correcto',
                                    text: response.mensaje,
                                    icon: 'glyphicon glyphicon-envelope',
                                    type: 'success'
                                });
                            $("#modalBonificaciones").modal("toggle");
                            $(document).off('pjax:complete',grillaajax);
                        }); 
                    }
                    else
                    if ((response.carga=='0') && (response.error=='0')){
                        
                        $("#modalBonificaciones").modal("show").find("#modalContent").html("");
                        $("#modalBonificaciones").modal("show").find("#modalContent").html(response.vista);
                    }
                }
                else{                    
                    reportarNotificacionGral(response.message, 'error', true); 
                    $("#modalBonificaciones").modal("toggle");                    
                }
            },
            error  : function (xhr) {
                $("body").loading('stop');
                $("#modalBonificaciones").modal("toggle");
                reportarNotificacionGral(xhr.responseText, 'error', true);  
                
                //console.log("internal server error");
            }
        }).done($("body").loading('stop'));        
        return false;
    });

 });
 
 function quitarBonificacion(xhref){
   
    $('#grilla-ajax').val('#pjax-bonificaciones');
    grillaajax = $('#grilla-ajax').val();
    
    bootbox.confirm({
        message: "Está seguro que deséa realizar la eliminación?",
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
                $("body").loading({message: 'AGUARDE... procesando.'});
                $.ajax({
                     url    : xhref,
                     type   : "post",            
                     dataType: "json",
                     success: function (response){                         
                         if(response.error==0){   
                            $.pjax.reload({container:grillaajax,timeout:false}); 
                            $(document).on('pjax:complete',grillaajax, function() {
                                new PNotify({
                                    title: 'Correcto',
                                    text: response.mensaje,
                                    icon: 'glyphicon glyphicon-envelope',
                                    type: 'success'
                                });
                                 $(document).off('pjax:complete',grillaajax);
                            });     
                         }else{                            
                            new PNotify({
                                title: 'Error',
                                text: response.mensaje,
                                icon: 'glyphicon glyphicon-envelope',
                                type: 'error'
                            });
                         }
                     },
                    error  : function (error) {
                        if(error.status == 403 && error.statusText=='Forbidden') {                            
                            new PNotify({
                                title: 'Error',
                                text: 'Usted no dispone de los permisos suficientes para realizar esta tarea',
                                icon: 'glyphicon glyphicon-envelope',
                                type: 'error'
                            });
                        }       
                    }
                 }).done($("body").loading('stop'));
            }
        }
    });
    return false;  
}