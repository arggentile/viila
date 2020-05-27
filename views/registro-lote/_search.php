<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\search\RegistroLoteSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="registro-lote-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'id_lote') ?>

    <?= $form->field($model, 'nombre_cliente') ?>

    <?= $form->field($model, 'tipo_dni') ?>

    <?= $form->field($model, 'dni') ?>

    <?php // echo $form->field($model, 'email') ?>

    <?php // echo $form->field($model, 'concepto') ?>

    <?php // echo $form->field($model, 'monto') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
