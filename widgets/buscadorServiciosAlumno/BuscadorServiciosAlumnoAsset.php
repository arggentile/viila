<?php

namespace app\widgets\buscadorServiciosAlumno;

use yii\web\AssetBundle;


class BuscadorServiciosAlumnoAsset extends AssetBundle
{
    public $js = [
        'wdgt-buscadorServicioAlumno.js'
    ];

    public $css = [       
        'wdgt-buscadorServicioAlumno.css'
    ];

    public $depends = [
        'yii\web\JqueryAsset'
    ];

    public function init()
    {
        // Tell AssetBundle where the assets files are
        $this->sourcePath = __DIR__ . "/assets";
        parent::init();
    }
}

