<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\RegistroLoteSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Registro Lotes';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="registro-lote-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Registro Lote', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'id_lote',
            'nombre_cliente',
            'tipo_dni',
            'dni',
            //'email:email',
            //'concepto',
            //'monto',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
