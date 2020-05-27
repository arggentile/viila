<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\search\FacturaSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="factura-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'fecha_factura') ?>

    <?= $form->field($model, 'nroFactura') ?>

    <?= $form->field($model, 'informada') ?>

    <?= $form->field($model, 'fecha_informada') ?>

    <?php // echo $form->field($model, 'monto') ?>

    <?php // echo $form->field($model, 'cae') ?>

    <?php // echo $form->field($model, 'ptoVta') ?>

    <?php // echo $form->field($model, 'id_tiket') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
