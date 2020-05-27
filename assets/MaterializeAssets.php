<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class MaterializeAssets extends AssetBundle
{
    public $sourcePath = '@app/plugins';
    public $css = [
        'materialize/css/materialize.min.css',
    ];
    public $js = [
        'materialize/js/materialize.min.js',
        
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
