


<?php



/* @var $this \yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;

use app\assets\AppAsset;

use dektrium\user\widgets\Connect;
use yii\widgets\ActiveForm;

AppAsset::register($this);
?>
<?php $this->beginPage();
      $this->title = 'Asosiación Hermanos de Don Bosco';?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<div class="wrap">
    <div class="container">
        <div class="row">
            <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">
                
                <div class="box box-login">
                   
                    <div class="panel-body">
                    <?php $form = ActiveForm::begin(
                        [
                            'id' => $model->formName(),
                            'enableAjaxValidation' => true,
                            'enableClientValidation' => false,
                        ]
                    ); ?>

                    <?= $form->field($model, 'email')->textInput(['autofocus' => true]) ?>

                    <?= Html::submitButton(Yii::t('usuario', 'Continue'), ['class' => 'btn btn-primary btn-block']) ?><br>

                    <?php ActiveForm::end(); ?>    
                    </div>
                    
                </div>
           
      
            
            </div>
        </div>
    </div>
</div>

<?php $this->endBody() ?>
    <style type="text/css">
     .btn-login{background-color: #8e101b !important;
     color: #fff !important;
     border: none !important;}
     body{background-color: #d2d6de;}
     .box-login{background-color: #fff !important;}
     .text-center{text-align: center;}
    </style>
</body>
</html>
<?php $this->endPage() ?>