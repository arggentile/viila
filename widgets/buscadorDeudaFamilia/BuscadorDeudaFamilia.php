<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\widgets\buscadorDeudaFamilia;

use Yii;

class BuscadorDeudaFamilia extends \yii\bootstrap\Widget
{       
    public $searchModel;
    public $dataProvider; 
    
    public $buscador = true;
    public $filtrosgrilla = false;   
    
    public function run()
    {      
        $searchModel = $this->searchModel;
        
        echo $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $this->dataProvider,
            'buscador'=>$this->buscador,
        ]);
    }
    
}
