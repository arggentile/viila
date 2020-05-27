<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Lote */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Lotes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="lote-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'nombre',
            'fecha',
        ],
    ]) ?>
    
    <?= yii\grid\GridView::widget([
        'dataProvider' => $dataProviderregistros,
    
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
