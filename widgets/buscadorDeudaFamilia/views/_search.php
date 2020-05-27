<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model app\models\search\ServicioAlumnoSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="grupo-familiar-search">
    
    <?php $form = ActiveForm::begin([        
        'method' => 'get',
        'id'=>'form-buscar-deudafamiliar',        
    ]); ?>

    
    <div class="row">
        <div class="col-sm-3">
            <?= $form->field($model, 'tipo_servicio')->dropDownList([''])?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($model, 'folio_familia') ?>    
        </div>
        <div class="col-sm-2 form-group">
            <?= Html::submitButton('Buscar', ['class' => 'btn btn-search']) ?>
        </div>
    </div>
    
  
    <?php ActiveForm::end(); ?>

</div>