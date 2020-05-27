<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\ServicioAlumnoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>
<div class="">
   
    
    <div class="row table-responsive">
        <div class="col-sm-12">
            <?php   
            Pjax::begin(
                    [
                    'id'=>'pjax-serviciosalumnos',
                    'enablePushState' => false,
                    'timeout'=>false,

                    ]
            ); ?>    
             <?php
                
                echo GridView::widget([
                    'id'=>'grid-servicios-tiket',
                    'dataProvider' => $dataProvider,                
                    'columns' => [  
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{selects}',
                            'buttons' => [
                                    'selects' => function ($url, $model, $key) {
                                            $obarr =  \yii\helpers\Json::encode(yii\helpers\ArrayHelper::toArray($model));                                                
                                            $expr = new yii\web\JsExpression($obarr);
                                            return Html::a('Seleccionar', null, [ 'class' => 'btn btn-primary btn-xs', 'onclick' => '(function ( $event ) { jQuery("body").trigger("serviciodeuda:seleccionado", ['.$expr.']); })();' ]);
                                    }
                            ],
                            'contentOptions' => ['nowrap'=>'nowrap']
                        ],
                        [
                            'label' => 'TIPO SERVICIO',  
                            'attribute'=>'tiposervicio',
                            'value' => function($model) {
                                if($model['tiposervicio'] == app\models\DebitoAutomatico::ID_TIPOSERVICIO_CONVENIO_PAGO)
                                    return "Cuota CP";
                                else
                                    return "Servicios";
                            },
                        ], 
                        [
                            'label' => 'Servicio',                       
                            'value' => function($model) {
                                if($model['tiposervicio']== app\models\DebitoAutomatico::ID_TIPOSERVICIO_CONVENIO_PAGO){
                                    return \app\models\CuotaConvenioPago::getDetalleDatosCuota($model['idservicio']);
                                }    
                                if($model['tiposervicio']== app\models\DebitoAutomatico::ID_TIPOSERVICIO_SERVICIOS){
                                    return \app\models\ServicioAlumno::getDetalleDatos($model['idservicio']);
                                }

                            },
                        ],
                        'importeservicio',
                        'importeabonado',
                        [
                            'label' => 'Importe Pendiente',                                
                            'value' => function($model) {
                                return $model['importeaabonar'];
                            },
                        ],            

                    ],
                ]); 
                ?>
            <?php Pjax::end(); ?>
        </div>            
    </div>
</div>