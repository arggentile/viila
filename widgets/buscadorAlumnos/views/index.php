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

?>

<?php
  echo $this->render('_search', ['model' => $searchModel]);
?>  
    
    <div class="clearfix crud-navigation">
       
    <div class="table-responsive"> 
        <?php 
            \yii\widgets\Pjax::begin(
            [
                'id'=>'pjax-wgt-buscadorFamilia',                
                'timeout'=>false, 
            ]); 
                
        echo GridView::widget([
                'id'=>'wgt-buscadorFamilia',
		'dataProvider' => $dataProvider,
		'pager' => [
			'class' => yii\widgets\LinkPager::className(),
			'firstPageLabel' => 'Primera',
			'lastPageLabel' => 'Ultima',
		],		
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
						return Html::a('Seleccionar', null, [ 'class' => 'btn btn-select btn-xs', 'onclick' => 'js:asignarFamilia('.$expr .');' ]);
					}
				],
				'contentOptions' => ['nowrap'=>'nowrap']
			],
			'apellidos',
			'folio',
                        [
                            'label' => 'Responsable',
                            'attribute'=>'responsable',
                            'format'=>'raw',
                            'value' => function($model) {
                                return $model->getMisResponsablesCabecera();
                            },
                        ],                
                                   
		],
	]); ?>
        
        <?php \yii\widgets\Pjax::end() ?>
    </div>

    </div>
   
<?php
$this->registerJs("
    function asignarFamilia(familia){
        $('.btn-select').attr('disabled','disabled');
        $('.btn-select').html('<i class=\'fa fa-spinner fa-spin\'></i> Procesando...');
        jQuery('body').trigger('familia:seleccionada', [familia]);
    }
    $('#form-buscar-grupofamiliar').on('beforeSubmit', function (e) {     
        e.preventDefault();        
        dataOptionPjax = 
        {
            url: '" . yii\helpers\Url::current()."',
            container: '#pjax-wgt-buscadorFamilia',    
            push: false, 
            replace: false, 
            timeout: false,
            data: $('#form-buscar-grupofamiliar').serialize()
        };
            
        $.pjax.reload(dataOptionPjax);  
        return false;
    });  
", \yii\web\View::POS_READY);
?>