<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\search\GrupoFamiliarSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="alumnos-search">
    
    <?php $form = ActiveForm::begin([        
        'method' => 'get',
        'id'=>'form-buscar-alumnos',        
    ]); ?>

    
    <div class="row">
        <div class="col-sm-2">
             <?= $form->field($model, 'nro_doc') ?>  
        </div>
        <div class="col-sm-3">
            <?= $form->field($model, 'apellido') ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($model, 'nombre') ?>    
        </div>
    </div>
    <div class="row">
        <div class="col-sm-2">
             <?= $form->field($model, 'nro_doc') ?>  
        </div>
        <div class="col-sm-3">
            <?= $form->field($model, 'apellido') ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($model, 'nombre') ?>    
        </div>
    </div>    
    
    <div class="col-sm-2 form-group">
            <?= Html::submitButton('Buscar', ['class' => 'btn btn-search']) ?>
        </div>
    </div>
    
  
    <?php ActiveForm::end(); ?>

</div>
