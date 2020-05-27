<?php
use yii\widgets\ActiveField;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use app\models\TipoDocumento;
use app\models\TipoSexo;
use kartik\widgets\DatePicker;
?>

<div class="persona-form">
<?php
$modelTutor = "[tutor]";
?>
    
    <div class="row form-group">
        <div class="col-sm-3">        
            <div class="input-group">
                <span class="input-group-addon"> 
                <?= Html::activeLabel($model, $modelTutor. 'id_tipodocumento', ['class' => 'control-label', 'aria-required'=>"true"]) ?> </span>
                <?= Html::activeDropDownList($model, $modelTutor. 'id_tipodocumento', TipoDocumento::getTipoDocumentos(), ['prompt'=>'Select..','class'=>'form-control']); ?>
            </div>
            <?= Html::error($model, $modelTutor. $modelTutor.'id_tipodocumento', ['class'=>'text-error text-red']); ?>
        </div>
        <div class="col-sm-5">        
            <div class="input-group">
                <span class="input-group-addon"> <?= Html::activeLabel($model, $modelTutor. 'nro_documento', ['class' => 'control-label', 'aria-required'=>"true"]) ?> </span>
                <?=  Html::activeTextInput($model, $modelTutor. 'nro_documento',['class'=>'form-control', 'placeholder'=>'Nº Doc']); ?>
            </div>
            <?= Html::error($model, $modelTutor. 'nro_documento',['class'=>'text-error text-red']); ?>
        </div>
    </div>
    
    <div class="row form-group required">
        <div class="col-sm-6">        
            <div class="input-group">
                <span class="input-group-addon"> <?= Html::activeLabel($model,$modelTutor. 'apellido', ['class' => 'control-label', 'aria-required'=>"true"]) ?> </span>
                <?=  Html::activeInput('text', $model, $modelTutor.'apellido',['class'=>'form-control','aria-required'=>"true", 'placeholder'=>'Apellido']); ?>
                
            </div>
            <?= Html::error($model,$modelTutor. 'apellido', ['class'=>'text-error text-red']); ?>
        </div>
    </div>
    
    <div class="row form-group required">
        <div class="col-sm-6">        
            <div class="input-group">
                <span class="input-group-addon"> <?= Html::activeLabel($model,$modelTutor. 'nombre', ['class' => 'control-label']) ?> </span>
                <?php echo Html::activeInput('text', $model,$modelTutor. 'nombre',['class'=>'form-control', 'placeholder'=>'Nombre']); ?>
            </div>
            <?= Html::error($model,$modelTutor. 'nombre',['class'=>'text-error text-red']); ?>
        </div>
    </div>
    
    <div class="row form-group required">        
        <div class="col-sm-5">        
            <div class="input-group">
                <span class="input-group-addon"> <?= Html::activeLabel($model,$modelTutor. 'sexo', ['class' => 'control-label']) ?> </span>
                <?php echo Html::activeDropDownList($model, $modelTutor.'id_sexo', TipoSexo::getTipoSexos(), ['class'=>'form-control', 'placeholder'=>'Sexo']); ?>
            </div>
            <?= Html::error($model, $modelTutor.'sexo',['class'=>'text-error text-red']); ?>
        </div>
        <div class="col-sm-5">        
            <div class="input-group">
                <span class="input-group-addon"> <?= Html::label('Nacimiento', ['class' => 'control-label']); ?> </span>
                <?php
                    echo DatePicker::widget([
                        'model' => $model,
                        'type'=> DatePicker::TYPE_INPUT,
                        'attribute' =>'[tutor]xfecha_nacimiento',
                        'pluginOptions' => [
                            'autoclose'=>true,
                            'format' => 'dd-mm-yyyy'
                        ],
                        'language' => 'es',

                    ]);
                ?>
            </div>
            <?= Html::error($model,$modelTutor. 'xfecha_nacimiento',['class'=>'text-error text-red']); ?>
        </div>      
    </div>
    
    <div class="row form-group">
        <div class="col-sm-4">        
            <div class="input-group">
                <span class="input-group-addon"> <?= Html::activeLabel($model, $modelTutor.'calle', ['class' => 'control-label']) ?> </span>
                <?php echo Html::activeInput('text', $model, $modelTutor.'calle',['class'=>'form-control', 'placeholder'=>'Calle']); ?>
            </div>
            <?= Html::error($model,$modelTutor. 'calle',['class'=>'text-error text-red']); ?>
        </div>
        <div class="col-sm-2">        
            <div class="input-group">
                <span class="input-group-addon"> Nº </span>
                <?php echo Html::activeInput('text', $model, $modelTutor.'nro_calle',['class'=>'form-control', 'placeholder'=>'Nro']); ?>
            </div>
            <?= Html::error($model, $modelTutor.'nro_calle',['class'=>'text-error text-red']); ?>
        </div>
        <div class="col-sm-2">        
            <div class="input-group">
                <span class="input-group-addon"> <?= Html::activeLabel($model, 'piso', ['class' => 'control-label']) ?> </span>
                <?php echo Html::activeInput('text', $model, $modelTutor.'piso',['class'=>'form-control']); ?>
            </div>
            <?= Html::error($model, $modelTutor.'piso',['class'=>'text-error text-red']); ?>
        </div>
        <div class="col-sm-2">        
            <div class="input-group">
                <span class="input-group-addon"> <?= Html::activeLabel($model, $modelTutor.'dpto', ['class' => 'control-label']) ?> </span>
                <?php echo Html::activeInput('text', $model, $modelTutor.'dpto',['class'=>'form-control']); ?>
            </div>
            <?= Html::error($model, $modelTutor.'dpto',['class'=>'text-error text-red']); ?>
        </div>
    </div>
    <div class="row form-group">
        <div class="col-sm-4">        
            <div class="input-group">
                <span class="input-group-addon"> <?= Html::activeLabel($model, $modelTutor.'localidad', ['class' => 'control-label']) ?> </span>
                <?php echo Html::activeInput('text', $model, $modelTutor.'localidad',['class'=>'form-control', 'placeholder'=>'Localidad']); ?>
            </div>
            <?= Html::error($model, $modelTutor.'localidad',['class'=>'text-error text-red']); ?>
        </div>
    </div>
    <div class="row form-group">    
        <div class="col-sm-4">        
            <div class="input-group">
                <span class="input-group-addon"> <?= Html::activeLabel($model,$modelTutor. 'telefono', ['class' => 'control-label']) ?> </span>
                <?php echo Html::activeInput('text', $model, $modelTutor.'telefono',['class'=>'form-control', 'placeholder'=>'Telefono']); ?>
            </div>
            <?= Html::error($model,$modelTutor. 'telefono',['class'=>'text-error text-red']); ?>
        </div>
    
        <div class="col-sm-4">        
            <div class="input-group">
                <span class="input-group-addon"> <?= Html::activeLabel($model, $modelTutor.'celular', ['class' => 'control-label']) ?> </span>
                <?php echo Html::activeInput('text', $model, $modelTutor.'celular',['class'=>'form-control', 'placeholder'=>'Celular']); ?>
                
            </div>
            <?= Html::error($model, $modelTutor. 'celular',['class'=>'text-error text-red']); ?>
        </div>
    </div>
    
    <div class="row form-group">
    
        <div class="col-sm-4">        
            <div class="input-group">
                <span class="input-group-addon"> <?= Html::activeLabel($model, 'mail', ['class' => 'control-label']) ?> </span>
                <?php echo Html::activeInput('text', $model, 'mail',['class'=>'form-control', 'placeholder'=>'Email']); ?>
            </div>
            <?= Html::error($model, 'mail',['class'=>'text-error text-red']); ?>
        </div>
    </div>
    
  
</div>

<style type="text/css">
 .input-group .input-group-addon{background-color: #f3f3f3;}
</style>