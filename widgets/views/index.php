<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\ServicioAlumnoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>
<div class="box box-colegio">    
    <div class="box-body">
        
        <?php if($buscador) echo $this->render('_search', ['model' => $searchModel, 'filtro_estados'=>$filtro_estados, 'filtro_establecimiento'=>$filtro_establecimiento,
            'filtro_divisiones'=>$filtro_divisiones]); ?>
        
        
                        
        <div class="row table-responsive">
            <div class="col-sm-12">
            <?php   
                    Pjax::begin(
                            [
                            'id'=>'pjax-serviciosalumnos',
                            'enablePushState' => false,
                            'timeout'=>false,
                            'clientOptions' => ['method' => 'POST']
                            ]
                    ); ?>    
                    <?= GridView::widget([
                        'id'=>'reporte-servicios-alumno',
                        'dataProvider' => $dataProvider,                        
                        'columns' => [
                            [
                                'label' => 'Alumno',
                                 'value' => function($model) {
                                    return $model->datosMiAlumno;
                                },
                            ],
                            [
                                'label' => 'Servicio',
                                 'value' => function($model) {
                                    return $model->datosMiServicio;
                                },
                            ],
                            [
                                'label' => '$.Servicio',
                                 'value' => function($model) {
                                    return $model->importe_servicio;
                                },
                            ],
                            [
                                'label' => '$.Descuento',
                                 'value' => function($model) {
                                    return $model->importe_descuento;
                                },
                            ],            
                            [
                                'label' => '$.Abonado',
                                 'value' => function($model) {
                                    return $model->importe_servicio;
                                },
                            ],
                            [
                                'label' => '$.Abonar',
                                 'value' => function($model) {
                                    return $model->importe_servicio;
                                },
                            ], 
                            [
                                'label' => 'Estado',
                                'format'=>'raw',
                                'value' => function($model) {
                                    return $model->detalleEstado;
                                },
                            ],
                           [
                            'class' => 'yii\grid\ActionColumn',
                            'headerOptions' => ['width' => '50', 'class'=>'actionsgrid'],
                            'template'=>'{remover}',
                            'buttons' => 
                            [
                            'remover' => function ($url, $model) {     
                                    if($model->estado!='A')
                                        $class='disabled readonly';
                                    else
                                    $class='';
                                            return Html::button( '<i class="fa fa fa-remove"></i>',
                                                                   ['data-xhref'=> Url::to(['servicio-alumno/remover', 'id'=>$model['id']]),'class'=>'btn btn-primary btn-xs btn-remove-servicio'. $class, 'title'=>'Remueve el servicio al alumno','alt'=>'Remueve el servicio al alumno']
                                                           );
                                    
                                   }, 
                            ],
                            
                    ],             
                        ],
                    ]); ?>
                <?php Pjax::end(); ?>
            </div>            
        </div>    
           
        
    </div>
</div>
<?php 
    $this->registerJsFile('@web/js/wdgtbuscadorservicio.js', ['depends'=>[app\assets\AppAsset::className()]]);
?>