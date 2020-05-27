<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

/**
 *
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var frontend\models\LegajoSearch $searchModel
 */

 \yii\widgets\Pjax::begin(
         [
             'id'=>'pjax-persona-busqueda',
             'enablePushState' => false,
             'enableReplaceState' => false,
         ]) 
?>

<h1>
    <?php echo 'PERSONAS'; ?>
    <small>
        List
    </small>
</h1>
<div class="clearfix crud-navigation">

<div class="table-responsive">
    <?php echo GridView::widget([
            'id'=>'buscadorPersonaWidget',
            'dataProvider' => $dataProvider,
            'pager' => [
                    'class' => yii\widgets\LinkPager::className(),
                    'firstPageLabel' => 'First',
                    'lastPageLabel' => 'Last',
            ],
            'filterModel' => $searchModel,
            'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
            'headerRowOptions' => ['class'=>'x'],
            'columns' => [
                    [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{view}',
                            'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                            $obarr = \yii\helpers\Json::encode(yii\helpers\ArrayHelper::toArray($model));
                                            $expr = new yii\web\JsExpression($obarr);
                                            return Html::a('Selec', null, [ 'class' => 'btn btn-primary', 'onclick' => '(function ( $event ) { jQuery("body").trigger("persona:seleccionada", ['.$expr.']); })();' ]);
                                    }
                            ],
                            'contentOptions' => ['nowrap'=>'nowrap']
                    ],
                    'miTipodocumento.siglas',
                    'nro_documento',                
                    'apellido',
                    'nombre',
            ],
    ]); ?>
</div>

</div>


<?php \yii\widgets\Pjax::end() ?>