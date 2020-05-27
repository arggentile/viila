<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\TiketSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Tikets';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tiket-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Tiket', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'nro_tiket',
            'fecha_tiket',
            'importe',
            'fecha_pago',
            //'detalles',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
