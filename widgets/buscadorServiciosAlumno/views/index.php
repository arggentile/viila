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
    <?php 
    if($buscador) 
    echo $this->render('_search', 
        ['model' => $searchModel, 
         'filtros'=>$filtros,
            'notDisplaySearch'=>$notDisplaySearch
        ]); 
    ?>
    
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
            <?= 
            GridView::widget([
                'id'=>'reporte-servicios-alumno',
                'dataProvider' => $dataProvider,                        
                'columns' => [
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{selectServicio}',
                        'buttons' => [
                                'selectServicio' => function ($url, $model, $key) {
                                        $obarr = \yii\helpers\Json::encode(yii\helpers\ArrayHelper::toArray($model));                                                
                                        $expr = new yii\web\JsExpression($obarr);
                                        return Html::a('Seleccionar', null, [ 'class' => 'btn btn-primary btn-xs', 'onclick' => '(function ( $event ) { jQuery("body").trigger("servicioalumno:seleccionado", ['.$expr.']); })();' ]);
                                }
                        ],
                        'contentOptions' => ['nowrap'=>'nowrap'],
                        'visible'=> $selectRow
                    ],
                    [
                        'label' => 'Alumno',
                         'value' => function($model) {
                            return $model->datosMiAlumno;
                        },
                        'visible'=> !in_array('alumno', $notDisplayColumn)            
                    ],
                    [
                        'label' => 'Servicio',
                         'value' => function($model) {
                            return $model->datosMiServicio;
                        },
                        'visible'=> !in_array('servicio', $notDisplayColumn)         
                    ],
                    [
                        'label' => '$.Servicio',
                         'value' => function($model) {
                            return $model->importe_servicio;
                        },
                        'visible'=> !in_array('precio_servicio', $notDisplayColumn)       
                    ],
                    [
                        'label' => '$.Descuento',
                         'value' => function($model) {
                            return $model->importe_descuento;
                        },
                        'visible'=> !in_array('precio_descuento', $notDisplayColumn)        
                    ],            
                    [
                        'label' => '$.Abonado',
                        'value' => function($model) {
                            return $model->importe_abonado;
                        },
                        'visible'=> !in_array('precio_abonado', $notDisplayColumn)        
                    ],
                    [
                        'label' => '$.Abonar',
                         'value' => function($model) {
                            return $model->importeAbonar;
                        },
                        'visible'=> !in_array('precio_abonar', $notDisplayColumn)        
                    ], 
                    [
                        'label' => 'Estado',
                        'format'=>'raw',
                        'value' => function($model) {
                            return $model->detalleEstado;
                        },
                        'visible'=> !in_array('detalle_estado', $notDisplayColumn)        
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'headerOptions' => ['width' => '', 'class'=>'actionsgrid'],
                        'template'=>'{view} {editar} {remover}',
                        'buttons' => 
                        [
                            'view' => function ($url, $model) {   
                                if(Yii::$app->user->can('verDetalleServicioAlumno')){                                   
                                    //$class='disabled readonly';
                                    return Html::button( '<i class="fa fa-eye"></i>',
                                           [
                                               'class'=>'btn btn-primary btn-xs btn-view-detalle-servicio', 
                                               'title'=>'Visualiza detalle servicio',
                                                'alt'=>'Visualiza detalle servicio',
                                               'url-edit'=> Url::to(['/servicio-alumno/visualizar-detalle-serivicio-alumno','id'=>$model->id])
                                            ]
                                    );
                                    
                                }
                            },    
                            'editar' => function ($url, $model) {     
                                if(Yii::$app->user->can('editarServicioAlumno') && $model->id_estado == app\models\EstadoServicio::ID_ABIERTA){
                                    return Html::button( '<i class="fa fa-edit"></i>',
                                           [
                                               'class'=>'btn btn-primary btn-xs btn-edit-servicio', 
                                               'title'=>'Edita el servicio al alumno',
                                                'alt'=>'Edita el servicio al alumno',
                                               'url-edit'=> Url::to(['/servicio-alumno/action-editar-serivicio-alumno','idservicio'=>$model->id])
                                            ]
                                    );
                                    
                                }
                            },    
                            'remover' => function ($url, $model) {     
                                if(Yii::$app->user->can('eliminarServicioAlumno') && $model->id_estado == app\models\EstadoServicio::ID_ABIERTA ){
                                    if($model->id_estado != \app\models\EstadoServicio::ID_ABIERTA){
                                        $class='disabled readonly';
                                        return Html::button( '<i class="fa fa-remove"></i>',
                                                                   [
                                                                       'class'=>'btn btn-danger btn-xs '. $class, 
                                                                       'title'=>'Remueve el servicio al alumno',
                                                                        'alt'=>'Remueve el servicio al alumno']
                                                           );
                                    }
                                    else{
                                        $class='';
                                            return Html::button( '<i class="fa fa-remove"></i>',
                                                                   ['data-xhref'=> Url::to(['/servicio-alumno/remover', 'id'=>$model['id']]),
                                                                       'class'=>'btn btn-danger btn-xs btn-remove-servicio '. $class, 
                                                                       'title'=>'Remueve el servicio al alumno',
                                                                        'alt'=>'Remueve el servicio al alumno']
                                                           );
                                    }
                                }

                               }, 
                        ],
                    ],    
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'headerOptions' => ['width' => '', 'class'=>'actionsgrid'],
                        'template'=>'{factura}',
                        'buttons' => 
                        [
                            'factura' => function ($url, $model) {     
                                $estadoAbonadas= [\app\models\EstadoServicio::ID_ABONADA, \app\models\EstadoServicio::ID_ABONADA_EN_CONVENIOPAGO, \app\models\EstadoServicio::ID_ABONADA_EN_DEBITOAUTOMATICO];
                                $modelFactura =     $model->getMiFactura();
                                if(in_array($model->id_estado, $estadoAbonadas) && !empty($modelFactura)) {
                                    return Html::a( '<i class="fa fa-file-pdf-o"></i>', Url::to(['caja/pdf-factura','idFactura'=>$modelFactura->id]),
                                        [
                                        'class'=>'btn btn-xs', 
                                        'title'=>'Imprimir Factura',
                                        'alt'=>'Imprimir Factura'
                                        ]);
                                }
                            },          
                        ] 
                    ]
                ],
            ]); 
            ?>
            <?php Pjax::end(); ?>
        </div>            
    </div>
</div>
<input type="hidden" name="urlreloadgrilla" id="urlreloadgrilla" value="<?= yii\helpers\Url::current();?>" />

<?php
yii\bootstrap\Modal::begin([        
    'id'=>'modalEditServicio',
    'class' =>'modal-scrollbar',
    'size'=>'modal-lg',
    ]);
    echo "<div id='modalContent'></div>";
yii\bootstrap\Modal::end();
?>

<?php
$this->registerJs("
   $('#form-search-serviciosalumno .btn-export-listado').click(function(){
      
        urlExport = '" . Url::to(['/servicio-alumno/down-listado-servicios-alumno']) ."';
        
        if (urlExport.indexOf('?') !== -1)
            var urlExport = urlExport + '&' + $('#form-search-serviciosalumno :not(input[name=r])').serialize();
        else
            var urlExport = urlExport + '?' + $('#form-search-serviciosalumno :not(input[name=r])').serialize();
        
       
        window.open(urlExport,'_blank');       
    });
    


    
    
   
    
", \yii\web\View::POS_READY);
?>
<?php 
    $this->registerJsFile('@web/js/wdgtbuscadorservicio.js', ['depends'=>[app\assets\AppAsset::className()]]);
?>