$('#tiket-id_formapago').on('change',function() {  
    if ($(this).val() == '1'){           
        $('#tiket-cuentapagadora').val('1');
    }else{           
        $('#tiket-cuentapagadora').prop('selectedIndex','1')
        $('#tiket-cuentapagadora').val('2');
    }
});

$('#tiket-cuentapagadora').on('change',function() {
    if ( ($(this).val() == '1') || ($(this).val() == '2') ){
        $('#tiket-tipo_pago').val('1');
    }
});


$(document).ready(function(){    
    /*cada vez que seleccionamos un servicio; si se selcciona se suma al valor total que se
    debe abonar caso contrario si se deselcciona se resta*/
    $('.checkservicios').change(function(event){    
        montoservicio = parseFloat($(this).parent().parent().children().last().text()); 
        montototal = parseFloat($('#tiket-importeservicios').val());

        if (this.checked){
            montototal = montototal + montoservicio;
            $('#tiket-importeservicios').val(montototal);
        } else {
            montototal = montototal - montoservicio;
            $('#tiket-importeservicios').val(montototal);
        }
    });
    
    $('#form-servicios').on('beforeSubmit',function(e, messages){        
        $('#btn-envio').attr('disabled','disabled');
        $('#btn-envio').html('<i class=\'fa fa-spinner fa-spin\'></i> Procesando...');

        var importe_servicios = parseFloat($('#tiket-importeservicios').val());
        var importe_abonado = parseFloat($('#tiket-montoabonado').val());

        if ( ($('#tiket-pagototal').val()=='0') && (importe_servicios != importe_abonado) && ($('#servicios').yiiGridView('getSelectedRows').length > 1) ){
            $('#btn-envio').removeAttr('disabled');
            $('#btn-envio').html('<i class=\'fa fa-save\'></i> Aceptar Cobro');
            e.preventDefault();
            new PNotify({
                                title: 'Error',
                                text: 'Solo se permite el pago parcial de un unico servicio',
                                icon: 'glyphicon glyphicon-envelope',
                                type: 'error'
                            });
            return false;      
        }
        
    });    
});        
