<?php
use yii\widgets\ActiveField;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use app\models\TipoDocumento;
use app\models\TipoSexo;
use kartik\widgets\DatePicker;
?>

<div class="persona-form">

    
    <div class="row form-group">
        <div class="col-sm-3">        
            <div class="input-group">
                <span class="input-group-addon"> 
                <?= Html::activeLabel($model, 'id_tipodocumento', ['class' => 'control-label', 'aria-required'=>"true"]) ?> </span>
                <?= Html::activeDropDownList($model, 'id_tipodocumento', TipoDocumento::getTipoDocumentos(), ['prompt'=>'Select..','class'=>'form-control']); ?>
            </div>
            <?= Html::error($model, 'id_tipodocumento', ['class'=>'text-error text-red']); ?>
        </div>
        <div class="col-sm-5">        
            <div class="input-group">
                <span class="input-group-addon"> <?= Html::activeLabel($model, 'nro_documento', ['class' => 'control-label', 'aria-required'=>"true"]) ?> </span>
                <?=  Html::activeTextInput($model, 'nro_documento',['class'=>'form-control', 'placeholder'=>'Nº Doc']); ?>
            </div>
            <?= Html::error($model, 'nro_documento',['class'=>'text-error text-red']); ?>
        </div>
    </div>
    
    <div class="row form-group required">
        <div class="col-sm-6">        
            <div class="input-group">
                <span class="input-group-addon"> <?= Html::activeLabel($model, 'apellido', ['class' => 'control-label', 'aria-required'=>"true"]) ?> </span>
                <?=  Html::activeInput('text', $model, 'apellido',['class'=>'form-control','aria-required'=>"true", 'placeholder'=>'Apellido']); ?>
                
            </div>
            <?= Html::error($model, 'apellido', ['class'=>'text-error text-red']); ?>
        </div>
    </div>
    
    <div class="row form-group required">
        <div class="col-sm-6">        
            <div class="input-group">
                <span class="input-group-addon"> <?= Html::activeLabel($model, 'nombre', ['class' => 'control-label']) ?> </span>
                <?php echo Html::activeInput('text', $model, 'nombre',['class'=>'form-control', 'placeholder'=>'Nombre']); ?>
            </div>
            <?= Html::error($model, 'nombre',['class'=>'text-error text-red']); ?>
        </div>
    </div>
    
    <div class="row form-group required">        
        <div class="col-sm-5">        
            <div class="input-group">
                <span class="input-group-addon"> <?= Html::activeLabel($model, 'sexo', ['class' => 'control-label']) ?> </span>
                <?php echo Html::activeDropDownList($model, 'id_sexo', TipoSexo::getTipoSexos(), ['class'=>'form-control', 'placeholder'=>'Sexo']); ?>
            </div>
            <?= Html::error($model, 'sexo',['class'=>'text-error text-red']); ?>
        </div>
        <div class="col-sm-5">        
            <div class="input-group">
                <span class="input-group-addon"> <?= Html::label('Nacimiento', ['class' => 'control-label']); ?> </span>
                <?php
                    echo DatePicker::widget([
                        'model' => $model,
                        'type'=> DatePicker::TYPE_INPUT,
                        'attribute' => 'xfecha_nacimiento',
                        'pluginOptions' => [
                            'autoclose'=>true,
                            'format' => 'dd-mm-yyyy'
                        ],
                        'language' => 'es',

                    ]);
                ?>
            </div>
            <?= Html::error($model, 'xfecha_nacimiento',['class'=>'text-error text-red']); ?>
        </div>      
    </div>
    
    <div class="row form-group">
        <div class="col-sm-4">        
            <div class="input-group">
                <span class="input-group-addon"> <?= Html::activeLabel($model, 'calle', ['class' => 'control-label']) ?> </span>
                <?php echo Html::activeInput('text', $model, 'calle',['class'=>'form-control', 'placeholder'=>'Calle']); ?>
            </div>
            <?= Html::error($model, 'calle',['class'=>'text-error text-red']); ?>
        </div>
        <div class="col-sm-2">        
            <div class="input-group">
                <span class="input-group-addon"> Nº </span>
                <?php echo Html::activeInput('text', $model, 'nro_calle',['class'=>'form-control', 'placeholder'=>'Nro']); ?>
            </div>
            <?= Html::error($model, 'nro_calle',['class'=>'text-error text-red']); ?>
        </div>
        <div class="col-sm-2">        
            <div class="input-group">
                <span class="input-group-addon"> <?= Html::activeLabel($model, 'piso', ['class' => 'control-label']) ?> </span>
                <?php echo Html::activeInput('text', $model, 'piso',['class'=>'form-control']); ?>
            </div>
            <?= Html::error($model, 'piso',['class'=>'text-error text-red']); ?>
        </div>
        <div class="col-sm-2">        
            <div class="input-group">
                <span class="input-group-addon"> <?= Html::activeLabel($model, 'dpto', ['class' => 'control-label']) ?> </span>
                <?php echo Html::activeInput('text', $model, 'dpto',['class'=>'form-control']); ?>
            </div>
            <?= Html::error($model, 'dpto',['class'=>'text-error text-red']); ?>
        </div>
    </div>
    <div class="row form-group">
        <div class="col-sm-4">        
            <div class="input-group">
                <span class="input-group-addon"> <?= Html::activeLabel($model, 'localidad', ['class' => 'control-label']) ?> </span>
                <?php echo Html::activeInput('text', $model, 'localidad',['class'=>'form-control', 'placeholder'=>'Localidad']); ?>
            </div>
            <?= Html::error($model, 'localidad',['class'=>'text-error text-red']); ?>
        </div>
    </div>
    <div class="row form-group">    
        <div class="col-sm-4">        
            <div class="input-group">
                <span class="input-group-addon"> <?= Html::activeLabel($model, 'telefono', ['class' => 'control-label']) ?> </span>
                <?php echo Html::activeInput('text', $model, 'telefono',['class'=>'form-control', 'placeholder'=>'Telefono']); ?>
            </div>
            <?= Html::error($model, 'telefono',['class'=>'text-error text-red']); ?>
        </div>
    
        <div class="col-sm-4">        
            <div class="input-group">
                <span class="input-group-addon"> <?= Html::activeLabel($model, 'celular', ['class' => 'control-label']) ?> </span>
                <?php echo Html::activeInput('text', $model, 'celular',['class'=>'form-control', 'placeholder'=>'Celular']); ?>
                
            </div>
            <?= Html::error($model, 'celular',['class'=>'text-error text-red']); ?>
        </div>
    </div>
    
    <div class="row form-group">
    
        <div class="col-sm-4">        
            <div class="input-group">
                <span class="input-group-addon"> <?= Html::activeLabel($model, 'mail', ['class' => 'control-label']) ?> </span>
                <?php echo Html::activeInput('text', $model, 'mail', ['class'=>'form-control', 'placeholder'=>'Email','type'=>'email']); ?>
            </div>
            <?= Html::error($model, 'mail',['class'=>'text-error text-red']); ?>
        </div>
    </div>
</div>

<style type="text/css">
 .input-group .input-group-addon{background-color: #f3f3f3;}
</style>