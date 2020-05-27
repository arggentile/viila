<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model app\models\search\ServicioAlumnoSearch */
/* @var $form yii\widgets\ActiveForm */
?>

    <div class="box-body" id="seachSA">
        
            <?php $form = ActiveForm::begin([ 
                            'id'=>'formservc',
                            'method' => 'POST',
                           
                        ]); 
            ?>
       
        <div class="row form-group required" style="margin-bottom: 7px;">
            <div class="col-sm-3">        
                <div class="input-group">
                    <span class="input-group-addon"> <?= Html::activeLabel($model, 'folioFamilia', ['label'=>'Folio', 'class' => 'control-label'])?> </span>
                    <?=  Html::activeInput('text', $model, 'folioFamilia',['class'=>'form-control','aria-required'=>"true",'placeholder'=>'Folio Familia']); ?>
                </div>                
            </div>
            <div class="col-sm-4">        
                <div class="input-group">
                    <span class="input-group-addon"> <?= Html::activeLabel($model, 'apellidoFamilia', ['label'=>'Apellido', 'class' => 'control-label'])?> </span>
                    <?=  Html::activeInput('text', $model, 'apellidoFamilia',['class'=>'form-control','aria-required'=>"true",'placeholder'=>'Apellido Familia']); ?>
                </div>                
            </div>            
        </div>
        
        <div class="row form-group required" style="margin-bottom: 7px;">
            <div class="col-sm-3">        
                <div class="input-group">
                    <span class="input-group-addon"> <?= Html::activeLabel($model, 'documentoAlumno', ['label'=>'Doc', 'class' => 'control-label'])?> </span>
                    <?=  Html::activeInput('text', $model, 'documentoAlumno',['class'=>'form-control','aria-required'=>"true",'placeholder'=>'Documento Alumno']); ?>
                </div>                
            </div>
            <div class="col-sm-4">        
                <div class="input-group">
                    <span class="input-group-addon"> <?= Html::activeLabel($model, 'apellidoAlumno', ['label'=>'Apellido', 'class' => 'control-label'])?> </span>
                    <?=  Html::activeInput('text', $model, 'apellidoAlumno',['class'=>'form-control','aria-required'=>"true",'placeholder'=>'Apellido Alumno']); ?>
                </div>                
            </div>
            <div class="col-sm-4">        
                <div class="input-group">
                    <span class="input-group-addon"> <?= Html::activeLabel($model, 'nombreAlumno', ['label'=>'Nombre', 'class' => 'control-label'])?> </span>
                    <?=  Html::activeInput('text', $model, 'nombreAlumno',['class'=>'form-control','aria-required'=>"true",'placeholder'=>'Nombre Alumno']); ?>
                </div>                
            </div>
        </div>
        
        <div class="row form-group required" style="margin-bottom: 7px;">
            <div class="col-sm-4">        
                <div class="input-group">
                    <span class="input-group-addon"> <?= Html::activeLabel($model, 'establecimiento', ['label'=>'Estb','class' => 'control-label']) ?> </span>
                    <?php echo Html::activeDropDownList($model, 'establecimiento', $filtro_establecimiento, 
                            ['class'=>'form-control','prompt'=>'Seleccione',
                            'onchange'=>'
                                     $.get( "'. \yii\helpers\Url::toRoute('establecimiento/drop-mis-divisionesescolares').'", { idEst: $(this).val() } )
                                        .done(function( data )
                                        {

                                            $("#servicioalumnosearch-division_escolar").html(data);
                                        });']); ?>
                </div>
                
            </div>
               
            <div class="col-sm-4">        
                <div class="input-group">
                    <span class="input-group-addon"> <?= Html::activeLabel($model, 'division_escolar', ['label'=>'Division', 'class' => 'control-label']) ?> </span>
                    <?php echo Html::activeDropDownList($model, 'division_escolar', $filtro_divisiones, 
                            ['class'=>'form-control','prompt'=>'Seleccione',
                            ]); ?>
                </div>
               
            </div>
        </div>
        
        <div class="row form-group required" style="margin-bottom: 7px;">           
            <div class="col-sm-7">        
                <div class="input-group">
                    <span class="input-group-addon"> <?= Html::activeLabel($model, 'estado', ['label'=>'Estado','class' => 'control-label']) ?> </span>
                    <?php echo Html::activeDropDownList($model, 'estado', $filtro_estados, 
                            ['class'=>'form-control', 'prompt'=>'Seleccione']); ?>
                </div>
               
            </div>
        </div>
        
        <div class="row form-group ">
            <div class="col-sm-12">        
            
            <?= Html::button('<i class="fa fa-search"> </i> Buscar', ['class' => 'btn btn-primary','id'=>'btn-buscar-serviciosalumno']) ?>
            <?= Html::resetButton('Borrar', ['class' => 'btn btn-secondary']) ?>
            
            <?= Html::button('<i class="fa fa-file-excel-o"> </i> EXCEL', ['class' => 'btn btn-success', 'id'=>'btn-excel',
                        'onclick'=>'js:{downListado("'.Url::to(['servicio-alumno/exportar-excel']) .'");}']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
        
    </div>

<?php
 
$this->registerJs(' 
    $("#pjax-serviciosalumnos").on("pjax:beforeSend", function (event, data, status, xhr, options) {
        
        status.data = status.data+$("#formservc").serialize();              
    });
            
    $("document").ready(function(){
        $("#btn-buscar-serviciosalumno").on("click",function(){                
            $.pjax.reload({container: "#pjax-serviciosalumnos",type:"POST" ,timeout:false, replace: false});   
        });
    });
    
');
?>

    
    