<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\widgets\formulariopersonatutor;

use Yii;

class FormularioPersona extends \yii\bootstrap\Widget
{
    public $model;
    
    public function run(){
        echo $this->render('_form',['model' => $this->model ]);
    }
    
}