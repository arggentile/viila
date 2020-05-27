/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* listao y export */
$(document).ready(function () {
    $('body').on('click', ".btn-eliminar-servicioofrecido", function (e) {
        e.preventDefault();
        var href = $(this).attr('data-url');
       
        bootbox.confirm({
            message: "Está seguro que desea realizar la Eliminacion?",
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
    
    
    
    $('#form-search-serviciosofrecidos').on('beforeSubmit', function (e) {     
        e.preventDefault();
        var urlReload = $('#url-reload-listado-serviciosofrecidos').val();       
        
        dataOptionPjax = 
        {
            url: urlReload,
            container: '#pjax-servicioofrecido', 
            timeout: false,
            data: $('#form-search-serviciosofrecidos').serialize()
        };
            
        $.pjax.reload(dataOptionPjax);  
        return false;
    });
    
    $('#form-search-serviciosofrecidos .btn-export-listado').click(function() {
        
        var curr_page = window.location.href;
        if (curr_page.indexOf('?') !== -1)
            var curr_page =  curr_page +  '&export=1';
        else
            var curr_page = curr_page +  '?export=1';
        window.location.href = curr_page;       
    });
});
    
/* alta, actualizacion */    
$(document).ready(function () {    
    $(document).on('pjax:beforeSend', '#pjax-servicioofrecido', function() { 
        $("body").loading({message: 'Aguarde procesando....'});
    });
    $(document).on('pjax:complete', '#pjax-servicioofrecido', function() { 
        $("body").loading('stop');
    });
    
    
    $('#form-servicioofrecido').on('beforeValidate',function(e){
        $('#btn-envio').attr('disabled','disabled');
        $('#btn-envio').html('<i class=\'fa fa-spinner fa-spin\'></i> Procesando...');        
    });
    
    $('#form-servicioofrecido').on('afterValidate',function(e, messages){
        if ($('#form-servicioofrecido').find('.has-error').length > 0){
            $('#btn-envio').removeAttr('disabled');
            $('#btn-envio').html('<i class=\'fa fa-save\'></i> Guardar...');
        }
    });
});     

/*Divisiones*/
$(document).ready(function () {
    $('.btn-asociardivision').on('click',function(){        
        xhref = $(this).attr('data-xhref');       
        
        $.ajax({
            url    : xhref,                 
            dataType: "json",
            success: function (response){ 
                if (response.form==='1' && response.error == '0'){
                    $('#modal-divisiones-servicio').modal('show').find('.micontent').html("");
                    $('#modal-divisiones-servicio').modal('show').find('.micontent').html(response.vista);   
                }else{
                    reportarError(response.mensaje);
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
    });
});

$('#modal-divisiones-servicio').on('hide.bs.modal', function(){
    dataOptionPjax = 
    {          
        container: '#pjax-divisionesdelservicios', 
        timeout: false,         
    };            
    $.pjax.reload(dataOptionPjax);  
});