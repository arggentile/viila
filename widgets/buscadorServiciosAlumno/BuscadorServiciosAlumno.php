<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\widgets\buscadorServiciosAlumno;

use Yii;

use app\widgets\buscadorServiciosAlumno\BuscadorServiciosAlumnoAsset;

class BuscadorServiciosAlumno extends \yii\bootstrap\Widget
{       
    public $searchModel;
    public $dataProvider; 
    
    public $buscador = true;
    public $filtrosgrilla = false;   
    
    public $notDisplayColumn = [];
    public $notDisplaySearch = [];
    
    public $selectRow = false;
    
    public function run()
    {      
        BuscadorServiciosAlumnoAsset::register($this->getView());
        
        $searchModel = $this->searchModel;
        
        $estadosServicios = \app\models\EstadoServicio::find()->all();
        $estadosServicios = \yii\helpers\ArrayHelper::map($estadosServicios, 'id' , 'descripcion');
      
        $filter['estado_servicio'] = $estadosServicios;
        $filter['servicios'] = \app\models\ServicioOfrecido::getServiciosDrop();
        
        $filtro_establecimiento= \app\models\Establecimiento::getEstablecimientos();
        $filter['establecimiento'] = $filtro_establecimiento;
        if(!empty($searchModel->establecimiento)){
            $filtro_divisiones = \app\models\DivisionEscolar::getDivisionesEstablecimiento($searchModel->establecimiento);
        }else{
            $filtro_divisiones=[];
        }
        $filter['divisiones'] = $filtro_divisiones;
        
        echo $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $this->dataProvider,
            'buscador'=>$this->buscador,
            'filtros'=>$filter,
            'filtrosgrilla'=>$this->filtrosgrilla,
            'notDisplayColumn'=>$this->notDisplayColumn,
            'notDisplaySearch'=>$this->notDisplaySearch,
            'selectRow'=>$this->selectRow
        ]);
    }
    
}
