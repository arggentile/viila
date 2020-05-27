<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\widgets\buscadorAlumnos;

use Yii;

class BuscadorAlumnos extends \yii\bootstrap\Widget
{       
    public $searchModel;
    public $dataProvider;
    
    public function run()
    {        
        try{
            return $this->render('index', [
                'searchModel' => $this->searchModel,
                'dataProvider' => $this->dataProvider
            ]);
        }catch (\Exception $e) { 
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));           
            throw new GralException('Error al renderizar el widger de buscador de alumno.');                        
        }
    }
    
}
