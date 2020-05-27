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
                            'id'=>'form-search-serviciosalumno',
                            'method' => 'get',
                        ]); 
            ?>
        
        <div class="row form-group required <?= (in_array('familia', $notDisplaySearch))?'invisible':'';?> " style="margin-bottom: 7px;">
            <input type="hidden" name="ServicioAlumnoSearch[familia]" id="" value="<?= $model->familia; ?>">
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
      
        
       
        <div class="row form-group required <?= (in_array('alumno', $notDisplaySearch))?'invisible':'';?>" style="margin-bottom: 7px;">
            <input type="hidden" name="ServicioAlumnoSearch[id_alumno]" id="" value="<?= $model->id_alumno; ?>">
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
       
        <div class="row form-group required <?= (in_array('establecimiento', $notDisplaySearch))?'invisible':'';?>" style="margin-bottom: 7px;">
            <div class="col-sm-4">        
                <div class="input-group">
                    <span class="input-group-addon"> <?= Html::activeLabel($model, 'establecimiento', ['label'=>'Estb','class' => 'control-label']) ?> </span>
                    <?php echo Html::activeDropDownList($model, 'establecimiento', $filtros['establecimiento'], 
                            ['class'=>'form-control','prompt'=>'Seleccione',
                            'onchange'=>'
                                     $.get( "'. \yii\helpers\Url::toRoute('establecimiento/drop-mis-divisionesescolares').'", { idEst: $(this).val() } )
                                        .done(function( data )
                                        {
                                        $("#servicioalumnosearch-division_escolar").html(data);
                                        $(\'#servicioalumnosearch-division_escolar\').empty();                                        
                                        let option = "<option value=\'\'>TODOS</option>";
                                        $(\'#servicioalumnosearch-division_escolar\').append(option);                           
                                        for(let i = 0; i < data.length; i++) {
                    
                                            let option = "<option value=\'"+ data[i].id +"\'>"+ data[i].nombre +"</option>";
                                            $(\'#servicioalumnosearch-division_escolar\').append(option);
                                        }
                                        });']); ?>
                </div>
                
            </div>
               
            <div class="col-sm-4">        
                <div class="input-group">
                    <span class="input-group-addon"> <?= Html::activeLabel($model, 'division_escolar', ['label'=>'Division', 'class' => 'control-label']) ?> </span>
                    <?php echo Html::activeDropDownList($model, 'division_escolar', $filtros['divisiones'], 
                                ['class'=>'form-control',
                                'prompt'=>'Seleccione',
                                ]); ?>
                </div>
               
            </div>
        </div>
      
        
      
        <div class="row form-group required <?= (in_array('estado_servicios', $notDisplaySearch))?'invisible':'';?>" style="margin-bottom: 7px;">           
            <div class="col-sm-7">        
                <div class="input-group">
                    <span class="input-group-addon"> <?= Html::activeLabel($model, 'id_estado', ['label'=>'Estado','class' => 'control-label']) ?> </span>
                    <?php echo Html::activeDropDownList($model, 'id_estado', $filtros['estado_servicio'], 
                            [   'class'=>'form-control', 
                                'prompt'=>'Seleccione']); ?>
                </div>
               
            </div>
        </div>
        <div class="row form-group required invisible" style="margin-bottom: 7px;">           
            <div class="col-sm-7">        
                <div class="input-group">
                    <span class="input-group-addon"> <?= Html::activeLabel($model, 'id_servicio', ['label'=>'Servicio','class' => 'control-label']) ?> </span>
                    <?php echo Html::activeDropDownList($model, 'id_servicio', $filtros['servicios'], 
                            [   'class'=>'form-control', 
                                'prompt'=>'Seleccione']); ?>
                </div>
               
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-12">        
            <p class="pull-right">
                <?= Html::submitButton('<i class="fa fa-search"> </i> Buscar', ['class' => 'btn btn-search','id'=>'btn-buscar-serviciosalumno']) ?>
                <?= Html::button('<i class="glyphicon glyphicon-download-alt"></i> Exportar', ['class' => 'btn btn-export btn-export-listado']) ?>
            </p>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
        
    </div>