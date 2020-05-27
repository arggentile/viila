<?php

use app\models\Establecimiento;

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\widgets\ActiveField;
use kartik\widgets\DepDrop;
use yii\helpers\Url;
use yii\web\View;
use kartik\widgets\DatePicker;

use app\assets\AlumnoAssets;
AlumnoAssets::register($this);

/* @var $this yii\web\View */
/* @var $model common\models\Alumno */
/* @var $modelPersona common\models\Persona */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="callout callout-warning">
    <h4>
        <i class="icon fa fa-check"></i>
        Formulario ficha inscripcion 2019.        
    </h4>
    <p>
        Solicitamos que cargue los datos de los alumnos y su familia. Trabajaremos para mejorar la planilla de inscripción.
        Sepa disculpar las molestias.
    </p>
</div>
<div class="box box-greenlightsite box-solid box-colegio">
    <div class="box-header with-border">
        <i class="fa fa-user-plus"></i> <h3 class="box-title"> Formulario Inscripción </h3>  
    </div>
     <?php $form = ActiveForm::begin(
                            [
                            'id'=>'form-empadronamiento',
                            'enableClientValidation'=>true,
                            'options' => [
                                'class' => 'form-prev-submit'
                             ],
                            ]); ?>
    <div class="box-body">
        <div class="box box-default box-solid box-colegio">
            <div class="box-header with-border">
                <i class="fa fa-user-plus"></i> <h3 class="box-title"> Datos Basicos del Alumno </h3>  
            </div>
            <div class="box-body">
                <?= app\widgets\formulariopersona\FormularioPersona::widget(['model' => $modelPersonaAlumno]); ?>
            </div>
        </div>  
        
        <div class="box box-default box-solid box-colegio">
            <div class="box-header with-border">
                <i class="fa fa-user-plus"></i> <h3 class="box-title"> Datos Basicos del Tutor </h3>  
            </div>
            <div class="box-body">
                <?= app\widgets\formulariopersonatutor\FormularioPersona::widget(['model' => $modelTutor]); ?>
                <div class="row">
                    <div class="row">
                        <div class="col-sm-5">
                            <?= $form->field($familia, 'id_pago_asociado')->dropDownList( [4 => 'Debitos x CBU' , 5=>'Debitos x TC'],['prompt'=>'Select...']) ?>
                        </div>
                    </div>
                    <div class="row">
        <div class="col-sm-8">
                    <?php 
            if($familia->id_pago_asociado==4)
                echo $form->field($familia, 'cbu_cuenta')->textInput();
            else
                echo $form->field($familia, 'cbu_cuenta')->textInput(['readonly' => true]);?>

          
        </div>
    </div>

    <div class="row">
        <div class="col-sm-4">
              <?php 
            if($familia->id_pago_asociado==5)
                echo $form->field($familia, 'nro_tarjetacredito')->textInput()->label('Nº TC');
            else
                echo $form->field($familia, 'nro_tarjetacredito')->textInput(['readonly' => true])->label('Nº TC');?>
           
        </div>
        <div class="col-sm-4">
              <?php 
            if($familia->id_pago_asociado==5)
                echo $form->field($familia, 'prestador_tarjeta')->textInput()->label('Prestador');
            else
                echo $form->field($familia, 'prestador_tarjeta')->textInput(['readonly' => true])->label('Prestador');?>
           
        </div>
    </div>
    <div class="row">
        <div class="col-sm-8">
              <?php 
            if($familia->id_pago_asociado==5 || $familia->id_pago_asociado==4 )
                echo $form->field($familia, 'tarjeta_banco')->textInput()->label('Banco');
            else
                echo $form->field($familia, 'tarjeta_banco'
                                    )->textInput(['readonly' => true])->label('Banco'); 
                
                ?>
           
        </div>        
    </div>
                </div>
            </div>
        </div>  
    </div>
    
    <div class="row form-group">
        <div class="cols-sm-12">
        <?= Html::submitButton("<i class='fa fa-save'></i> Guardar...", ['class' => 'btn btn-block btn-primary','id'=>'btn-envio']) ?>
    </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>


<?php 
    $this->registerJsFile('@web/js/grupoFamiliares.js', ['depends'=>[app\assets\AppAsset::className()]]);
?>