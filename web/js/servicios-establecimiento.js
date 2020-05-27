function asignarServicioDivision(xhref){     
    $('body').loading({message: 'AGUARDE... procesando.'}); 
    urlreload = $('#urlreloadservicios').val();
    $.ajax({
        url    : xhref,                   
        dataType: 'json',
        success: function (response){                         
            if(response.error==0){
                $.pjax({container: '#pjax-divisionesservicios', timeout: false, push: false,  replace:false, url: urlreload}).done(function(){
                    $('body').loading('stop')                   
                });                            
            }else{
                $('body').loading('stop');
                new PNotify({
                    title: 'Error',
                    text: 'Se produjo un error al asignar el servicio',
                    icon: 'glyphicon glyphicon-envelope',
                    type: 'error'
                });
            }
        }
    });
    
    return false;       
} //fin deleteAjax 

function quitarServicioDivision(xhref){
    
    $('body').loading({message: 'AGUARDE... procesando.'}); 
    urlreload = $('#urlreloadservicios').val();
    $.ajax({
        url    : xhref,
        type   : 'post',            
        dataType: 'json',
        success: function (response){                         
            if(response.error==0){
                $.pjax({container: '#pjax-divisionesservicios', timeout: false, push: false, replace:false, url: urlreload}).done(function(){
                    $('body').loading('stop')                   
                });                            
            }else{
                $('body').loading('stop');
                new PNotify({
                    title: 'Error',
                    text: 'Se produjo un error al asignar el servicio',
                    icon: 'glyphicon glyphicon-envelope',
                    type: 'error'
                });
            }
        }
    });
    
    return false;       
} //fin deleteAjax


function eliminarServicioDivision(xhref){
    
    $('body').loading({message: 'AGUARDE... procesando.'}); 
    urlreload = $('#urlreloadservicios').val();
    $.ajax({
        url    : xhref,
        type   : 'post',            
        dataType: 'json',
        success: function (response){                         
            if(response.error==0){
                $.pjax({container: '#pjax-servicios', timeout: false, push: false, replace:false, url: urlreload}).done(function(){
                    $('body').loading('stop')                   
                });                            
            }else{
                $('body').loading('stop');
                new PNotify({
                    title: 'Error',
                    text: 'Se produjo un error al asignar el servicio',
                    icon: 'glyphicon glyphicon-envelope',
                    type: 'error'
                });
            }
        }
    });
    
    return false;       
} //fin deleteAjax 