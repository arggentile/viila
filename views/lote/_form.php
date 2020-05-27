<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\widgets\ActiveField;
use kartik\widgets\DepDrop;
use yii\helpers\Url;
use yii\web\View;
use kartik\widgets\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Lote */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="lote-form">

    <?php $form = ActiveForm::begin([
        'id'=>'form-lote',
        'enableClientValidation'=>true,
        'options' => ['enctype' => 'multipart/form-data']
        ]); ?>

    <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>

      <div class="col-sm-3">
            <?= $form->field($model, 'xfecha')->widget(
                    DatePicker::className(),([
                                        'language'=>'es',
                                        'type' => DatePicker::TYPE_INPUT,
                                        'pluginOptions' => [
                                            'autoclose'=>true,
                                            'format' => 'dd-mm-yyyy'
                                        ]
                                    ])
                    );?>
        </div>
<div class="col-sm-3">
            <?= $form->field($model, 'archivoentrante')->fileInput() ?>
        </div>
    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
