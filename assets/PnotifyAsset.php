<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class PnotifyAsset extends AssetBundle
{
    public $sourcePath = '@app/plugins';
    public $css = [
       'pnotify.custom.min.css',
    ];
    public $js = [
	'pnotify.custom.min.js',        
    ];
    public $depends = [
        'yii\bootstrap\BootstrapAsset',
    ];
}
