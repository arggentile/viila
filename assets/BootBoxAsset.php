<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class BootBoxAsset extends AssetBundle
{
    public $sourcePath = '@app/plugins';
    public $css = [
       
    ];
    public $js = [
	'bootbox.min.js',        
    ];
    public $depends = [
        'yii\bootstrap\BootstrapAsset',
    ];
}
