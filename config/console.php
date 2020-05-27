<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$dbaudit = require __DIR__ . '/dbaudit.php';

$config = [
    'id' => 'Console Hermanos Don Bosco',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
        '@tests' => '@app/tests',
    ],
    'components' => [
         'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
           
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'localhost',
                'username' => 'hermanos',
                'password' => 'NoaH???159',
                'port' => '587',
                'encryption' => 'tls',
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'logFile' => '@app/runtime/logs/trace.log',
                ],
            ],
        ],
    'mail' => [
            'class' => 'yii\swiftmailer\Mailer',
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.gmail.com',
                'username' => 'arg.gentile@gmail.com',
                'password' => 'EmilYA???',
                'port' => '587',
                'encryption' => 'tls',
                            ],
            ],
        'db' => $db,
        'dbaudit' => $dbaudit,
        'authManager' => [
            'class' => 'yii\rbac\DbManager',            
        ],
    ],
    
    'modules' => [
        'user' => [
            'class' => Da\User\Module::class,    
            'enableEmailConfirmation' => false,
            'generatePasswords'       => false,  
            'allowUnconfirmedEmailLogin'=> true,
            'enableRegistration'=> false,         
        ]
    ],
    
    'controllerMap' => [
        'migrate-audit' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationNamespaces' => [              
                'bedezign\yii2\audit\migrations',
            ],
        ],
        'migrate-users' => [
            'class' => \yii\console\controllers\MigrateController::class,
            'migrationPath' => [
                '@yii/rbac/migrations', // Just in case you forgot to run it on console (see next note)
            ],
            'migrationNamespaces' => [
                'Da\User\Migration',
            ],
        ],
    ],
    
    'params' => $params,    
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
