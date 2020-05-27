$(document).ready(function(){
    $('#formSearchEgresoAlumnos').on('beforeValidate',function(e){
        $(".btn-submit-envio").attr('disabled','disabled');
        $(".btn-submit-envio").html("<i class=\'fa fa-spinner fa-spin\'></i> Procesando...");        
    });
    
    $("#formSearchEgresoAlumnos").on("afterValidate",function(e, messages){
        if ( $(".form-prev-submit").find(".has-error").length > 0){
            $(".btn-submit-envio").removeAttr("disabled");
            $(".btn-submit-envio").html("<i class=\'fa fa-save\'></i> Guardar...");
        }
    });
});   


$('#egresoalumnoform-es_egreso').on('change',function() { 
    val = $(this).val();
   
    if (val == '1'){ 
        $('#establegreso').css('display','none'); 
        $('#divisionegreso').css('display','none'); 
    }
    if (val== '0'){        
        $('#establegreso').css('display','block'); 
        $('#divisionegreso').css('display','block'); 
       
    }       
});


$('#formEgresoAlumnos').on('beforeSubmit', function (e) {     
    $(".btn-submit-envio").attr('disabled','disabled');
    $(".btn-submit-envio").html("<i class=\'fa fa-spinner fa-spin\'></i> Procesando...");   
        
    e.preventDefault();
    var alumnosSeleccionados = $('#grid-egreso-alumnos').yiiGridView('getSelectedRows').toString();

    if(alumnosSeleccionados.length==0 || alumnosSeleccionados==''){
        $(".btn-submit-envio").removeAttr("disabled");
        $(".btn-submit-envio").html("<i class=\'fa fa-save\'></i> Egresar");
        alert('Seleccione al menos algun alumno');
        return false;
    }
    else{
        var egresaralumnos =  $('#egresaralumnos').val();
        if(egresaralumnos=='1' || egresaralumnos==1){
            return true;
        }else{
            urlForm = $(this).attr('action');

            var estabInicial = $('#establecimiento-egreso-inicial').val();
            var divisionInicial = $('#division-egreso-inicial').val();
            var estabEgreso = $('#egresoalumnoform-id_establecimiento option:selected').html();
            var divisionEgreso = $('#egresoalumnoform-id_divisionescolar option:selected').html();
            var tipoegreso = $('#egresoalumnoform-es_egreso').val();
            
            if(tipoegreso=='1'){
                var mensajeAdvertir = '<p style="font-size:16px"> Está seguro que desea "Egresar del Establecimiento" el/los alumnos. <br />';
                mensajeAdvertir += ' Este proceso Inactivara al el/los Alumnos</p>';
            }
            else{    
                var mensajeAdvertir = '<p style="font-size:16px"> Está seguro que desea migrar los alumnos de:' + divisionInicial + '('+ estabInicial +')'
                 + ' a la división ' + divisionEgreso + ' (' + estabEgreso + ')?</p>';
            }

            bootbox.confirm({
                message: mensajeAdvertir,
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
                        $('#egresaralumnos').val(1);
                        $('#formEgresoAlumnos').submit();
                    }else{
                        $(".btn-submit-envio").removeAttr("disabled");
                        $(".btn-submit-envio").html("<i class=\'fa fa-save\'></i> Egresar");    
                    }
                }
            });     
        return false;
        }
    }
});
    