<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\LoteSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Lotes';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="box  box-lotes lotes-index">
    <div class="box-header with-border">
        <i class="fa fa-users"></i> <h3 class="box-title"> Administración de Lotes </h3>    
    </div>
    <div class="box-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                'id',
                'nombre',
                'fecha:date',
                [
                    'label' => 'Cantidad registros',
                    'attribute'=>'cantregistros',
                    //'filter'=> dmstr\helpers\Html::activeInput('text', $modelPersona,'nro_documento',['class'=>'form-control']),
                    'value' => function($model) {
                        return $model->cantidadregistros;
                    },
                ],
                ['class' => 'yii\grid\ActionColumn'],
            ],
        ]); ?>
    </div>


</div>
