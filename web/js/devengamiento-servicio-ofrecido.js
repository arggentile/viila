/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$('#btn-devenarServicioOfrecido').click(function () {
//    var cant_divisiones = $('#cantdivisionesasociadas').val();
//    alert(cant_divisiones);
//    if(cant_divisiones=='0'){      
//        new PNotify({
//            title: 'Atención',
//            text: 'Debe asociar el servicio a las divisiones escolares',
//            icon: 'glyphicon glyphicon-envelope',
//            type: 'warning'
//        });   
//        return false;
//    }else{
//       
        var href = $(this).attr('value');

        // submit form
        $('body').loading({message: 'AGUARDE... procesando.'});
        $.ajax({
            url: href,
            type: 'GET',
            dataType: 'json',
            success: function (response) {            
                if (response.error == 0) {
                    $.pjax.reload({container: '#pjax-serviciosalumnos', timeout: false});
                    $('body').loading('stop');
                    reportarNotificacionGral(response.resultado, 'success', true); 
                } else {
                    $('body').loading('stop');
                    reportarNotificacionGral(response.resultado, 'error', true); 
                }
            },
            error: function (xhr) {
                $('body').loading('stop');
                reportarNotificacionGral(xhr.resultText, 'error', true);                           
            }
        });
    
});


$('#btn-eliminarDevengamientoServicioOfrecido').click(function () {
    var href = $(this).attr('value');
    var mensaje = 'Está seguro que desea realizar la eliminación del Devengamiento?' +
            '<br /> Esto eliminará los servicios a los alumnos que no estén abonados';
    bootbox.confirm({
        message: mensaje,
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
            if (result === true) {

                // submit form
                $('body').loading({message: 'AGUARDE... procesando.'});
                $.ajax({
                    url: href,
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        
                        if (response.error == '0') {
                            $.pjax.reload({container: '#pjax-serviciosalumnos', timeout: false});
                            $('body').loading('stop');
                            reportarNotificacionGral(response.resultado, 'success', true); 
                        } else {
                            $('body').loading('stop');
                            reportarNotificacionGral(response.resultado, 'error', true); 

                        }
                    },
                    error: function (xhr) {
                        $('body').loading('stop');
                        reportarNotificacionGral(xhr.resultText, 'error', true);                           
                    }
                });
            }
        }
    });
});