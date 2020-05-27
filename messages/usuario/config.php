<?php

return [ 
    'sourcePath' => \Yii::getAlias('@vendor/2amigos/yii2-usuario/src/User'),
    'messagePath' => \Yii::getAlias('@app/messages/usuario'),
    'languages' => [
        'es',
    ],
    'translator' => 'Yii::t',
    'sort' => false,
    'overwrite' => true,
    'removeUnused' => false,
    'only' => ['*.php'],
    'except' => [
        '.svn',
        '.git',
        '.gitignore',
        '.gitkeep',
        '.hgignore',
        '.hgkeep',
    ],
    'format' => 'php',
];

?>
