function actualizarGrillaServicio(){
        var url = $('#urlreloadgrilla').val();
                
        dataOptionPjax = 
        {
            url: url,
            container: '#pjax-serviciosalumnos', 
            timeout: false,
            data: $('#form-search-serviciosalumno').serialize()
        };
            
        $.pjax.reload(dataOptionPjax);  
}


$(document).ready(function () {
    $('#form-search-serviciosalumno').on('beforeSubmit', function (e) {     
        e.preventDefault();
        var url = $('#urlreloadgrilla').val();
        dataOptionPjax = 
        {
            url: url,
            container: '#pjax-serviciosalumnos', 
            timeout: false,
            data: $('#form-search-serviciosalumno').serialize()
        };
            
        $.pjax.reload(dataOptionPjax);  
        return false;
    });
});

$(document).ready(function () {
    
    $("body").on('click', '.btn-remove-servicio', function(){
        xhref = $(this).attr('data-xhref'); 

        grillaajax = '#reporte-servicios-alumno';

        bootbox.confirm({
            message: "Está seguro que desea REMOVER el servicio al alumno?",
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
                    $("body").loading({message: 'Aguarde procesando...'});
                    $.ajax({
                         url    : xhref,
                         type   : "post",            
                         dataType: "json",
                         success: function (response){  
                            $("body").loading('stop');
                            reportarNotificacionGral('Eliminación Correcta', 'success', true);
                            if(response.error==0){
                                $.pjax.reload({container:'#pjax-serviciosalumnos', timeout:false});                            
                            }else
                                reportarNotificacionGral('No se puede realizar la eliminación', 'error', true);
                        },
                        error: function (xhr) {  
                            $("body").loading('stop');
                           reportarNotificacionGral(xhr.responseText, 'success', true);

                         }

                     });
                }
            }
        });
        return false;       
    });
    
    $('body').on('click',  '.btn-view-detalle-servicio', function(){                
        var  url = $(this).attr('url-edit');        
        $.ajax({
            url    : url,
            dataType: 'json',
            success: function (response){
                console.log(response);
                $('#modalEditServicio').modal('show').find('#modalContent').html(response.vista);    
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
    
    $('body').on('click',  '.btn-edit-servicio', function(){                
        var  url = $(this).attr('url-edit'); 
        $.ajax({
            url    : url,
            success: function (response){
                $('#modalEditServicio').modal('show').find('#modalContent').html(response);    
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
        

    $('body').on('beforeSubmit', 'form#form-edit-servicioalumno', function () {
        var form = $(this); 
        $.ajax({
            url    : form.attr('action'),
            type   : 'post',
            data   : form.serialize(),
            dataType: 'json',
            success: function (response) {                    
                if(response.success){
                    actualizarGrillaServicio();
                    $('#modalEditServicio').modal('hide');
                }
                if ((response.form == '1') || (response.form==1)){                        
                    $('#modalEditServicio').modal('show').find('#modalContent').html('');
                    $('#modalEditServicio').modal('show').find('#modalContent').html(response.vista);
                }
            },
            error  : function (xhr) {
                console.log(xhr);                    
            }
        });        
        return false;
    });
});
