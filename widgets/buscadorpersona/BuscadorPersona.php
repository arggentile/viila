<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\widgets\buscadorpersona;

use Yii;

class BuscadorPersona extends \yii\bootstrap\Widget
{
    public $searchModel;
    public $dataProvider;
    
    public function run()
    {
        
        echo $this->render('index', [
            'searchModel' => $this->searchModel,
            'dataProvider' => $this->dataProvider
        ]);
    }
    
}