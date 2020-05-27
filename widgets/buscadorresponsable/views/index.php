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

<div class="clearfix crud-navigation">
    <div class="">
        <?= \dmstr\helpers\Html::dropDownList('tipores','',common\models\TipoResponsable::getTipoResponsables(),['class'=>'form-control','id'=>'tipores']); ?>
    </div>
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
                            'template' => '{select}',
                            'buttons' => [                                    
                                    'select' => function ($url, $model, $key) {                                            
                                            return Html::a('Selec', '#', ['class' => 'btn btn-xs btn-select','id'=>'asign']);
                                    }
                            ],                            
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