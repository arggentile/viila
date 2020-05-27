$(document).ready(function(){    
    $('.form-ajax-crud').on('beforeValidate',function(e){        
        $('.btn-submit-form').attr('disabled','disabled');
        $('.btn-submit-form').html('<i class=\'fa fa-spinner fa-spin\'></i> Procesando...');        
    });
    $('.form-ajax-crud').on('afterValidate',function(e, messages){
        if ($('.form-ajax-crud').find('.has-error').length > 0){
            $('.btn-submit-form').removeAttr('disabled');
            $('.btn-submit-form').html('<i class=\'fa fa-save\'></i> Guardar...');
        }
    });    
});   

function cargaAjax(xhref){    
    $.ajax({
        url    : xhref,                 
        dataType: "json",
        success: function (response){              
            if (response.form==='1' && response.error == '0'){
                $('#ModalCrudAjax').modal('show').find('#modalContent').html("");
                $('#ModalCrudAjax').modal('show').find('#modalContent').html(response.vista);  
                $('.btn-submit-form').removeAttr('disabled');
                $('.btn-submit-form').html('<i class=\'fa fa-save\'></i> Guardar...');                
            }else{
                reportarNotificacionGral(response.mensaje, 'error', true);  
            }
        },
        error: function(xhr){
            if(xhr.status == 403 && xhr.statusText=='Forbidden') 
                mensaje = 'Usted no dispone de los permisos suficientes para realizar esta tarea';
            else
                mensaje = xhr.responseText; 
                
            reportarNotificacionGral(mensaje, 'error', true);                          
        }
    });
    
    return false;   
}



function editAjax(xhref){
    $.ajax({
        url    : xhref,                 
        dataType: "json",
        success: function (response){
            if(response.form==='1' && response.error == '0'){
                $('#ModalCrudAjax').modal('show').find('#modalContent').html("");
                $('#ModalCrudAjax').modal('show').find('#modalContent').html(response.vista);   
            }else{
                reportarNotificacionGral(response.mensaje, 'error', true); 
            }
        },
        error: function(xhr){
            if(xhr.status == 403 && xhr.statusText=='Forbidden') 
                mensaje = 'Usted no dispone de los permisos suficientes para realizar esta tarea';
            else
                mensaje = xhr.responseText; 
                
            reportarNotificacionGral(mensaje, 'error', true); 
        }
    });
    
    return false;  
}

function deleteAjax(xhref){
    bootbox.confirm({
        message: "Está seguro que desea realizar la eliminación?",
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
                     type   : "post",            
                     dataType: "json",
                     success: function (response){
                         if(response.error==0){
                            reportarNotificacionGral(response.mensaje, 'success', true);     
                            $.pjax.reload({container:"#pjax-grid",timeout:false});
                        }else{           
                            reportarNotificacionGral(response.mensaje, 'error', true);                                
                        }
                     },
                     error  : function (xhr) {     
                            console.log(xhr);
                            if(xhr.status == 403 && xhr.statusText=='Forbidden') 
                                mensaje = 'Usted no dispone de los permisos suficientes para realizar esta tarea';
                            else
                                mensaje = xhr.responseText;
                            reportarNotificacionGral(mensaje, 'error', true);   
                            $("body").loading('stop');  
                    }
                }).done(function() {
                    $("body").loading('stop');       
                });
            }else{
                $("body").loading('stop');    
            }
            
        }
    });
    
    return false;       
} //fin deleteAjax    



$(document).ready(function () {
    $('.btn-submit-form').click(function(){       
        $('.btn-enviar').click();
    });    
    
    $("body").on("beforeSubmit", "form.form-ajax-crud", function () {
        var form = $(this);
        // return false if form still have some validation errors        
        /*
        if (form.find(".has-error").length) {
            alert("CON ERRORES");
            return false;
        }
        */
        $('.btn-submit-form').attr('disabled','disabled');
        $('.btn-submit-form').html('<i class=\'fa fa-spinner fa-spin\'></i> Procesando...'); 
       
        $("body").loading({message: 'ESPERE... procesando'});
        // submit form
        $.ajax({
            url    : form.attr("action"),
            type   : "post",
            data   : form.serialize(),
            dataType: "json",
            success: function (response) {
                if(response.error == '0'){
                    if (response.form=='1'){
                        $("body").loading('stop'); 
                        $("#ModalCrudAjax").modal("show").find("#modalContent").html("");
                        $("#ModalCrudAjax").modal("show").find("#modalContent").html(response.vista);
                        $('.btn-submit-form').removeAttr('disabled');
                        $('.btn-submit-form').html('<i class=\'fa fa-save\'></i> Guardar...');
                    }
                    if(response.carga == '1' && response.form == '0') {                    
                        new PNotify({
                                title: 'Correcto',
                                text: response.mensaje,
                                icon: 'glyphicon glyphicon-envelope',
                                type: 'success'
                            });
                        $('.btn-submit-form').removeAttr('disabled');
                        $('.btn-submit-form').html('<i class=\'fa fa-save\'></i> Guardar...');    
                        $("#ModalCrudAjax").modal("toggle");
                        $.pjax.reload({container: "#pjax-grid", timeout:false});                        
                    }
                }
                else{                    
                    new PNotify({
                                title: 'ERROR',
                                text: response.mensaje,
                                icon: 'glyphicon glyphicon-envelope',
                                type: 'error'
                            });
                            $('.btn-submit-form').removeAttr('disabled');
                        $('.btn-submit-form').html('<i class=\'fa fa-save\'></i> Guardar...');  
                    $("#ModalCrudAjax").modal("toggle");
                    grillaajax = $('#grilla-ajax').val();
                }
            },
            error  : function (error) {
                if(error.status == 403 && error.statusText=='Forbidden') 
                    mensaje = 'Usted no dispone de los permisos suficientes para realizar esta tarea';
                else
                    mensaje = 'Se produjo un error en la ejecucion de la tarea. Intente nuevamente y si persiste el error comuniquese con su administrador';

                new PNotify({
                    title: 'Error',
                    text: mensaje,
                    icon: 'glyphicon glyphicon-envelope',
                    type: 'error'
                }); 
            }
        }).done(function(o) {
            $("body").loading('stop');       
        });
        
        return false;
    }); 
        
});