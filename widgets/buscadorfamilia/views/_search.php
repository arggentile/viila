<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\search\GrupoFamiliarSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="grupo-familiar-search">
    
    <?php $form = ActiveForm::begin([        
        'method' => 'get',
        'id'=>'form-buscar-grupofamiliar',        
    ]); ?>

    
    <div class="row">
        <div class="col-sm-3">
            <?= $form->field($model, 'apellidos') ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($model, 'folio') ?>    
        </div>
        <div class="col-sm-4">
             <?= $form->field($model, 'responsable') ?>  
        </div>
        <div class="col-sm-2 form-group">
            <?= Html::submitButton('Buscar', ['class' => 'btn btn-search']) ?>
        </div>
    </div>
    
  
    <?php ActiveForm::end(); ?>

</div>
