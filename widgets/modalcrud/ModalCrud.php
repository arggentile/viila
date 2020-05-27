<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\widgets\modalcrud;

use Yii;

class ModalCrud extends \yii\bootstrap\Widget
{
    public $titulo = '';
    
    public function run(){
        echo $this->render('modal',['titulo'=>$this->titulo]);
    }
    
}