<?php
$params = array_merge(
    require(__DIR__ . '/../common/config/params.php'),
    //require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php')
    //require(__DIR__ . '/params-local.php')
);

$config = [
    'id' => 'app-channel',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'app\controllers',
    'defaultRoute' => '/home/portal',
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'X5fkO11SJRLNpeWi_bdkrTM_dKsQgSA-',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'maxSourceLines' => 20,
            'errorAction' => 'base/error-monitor',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        'assetManager'=>[
            'bundles'=>[
                'yii\bootstrap\BootstrapAsset' => [
                    'css' => []
                ],
                'yii\bootstrap\BootstrapPluginAsset' => [
                    'js'=>[]
                ],
                'yii\web\JqueryAsset' => [
                    'sourcePath' => null,
                    'js' => []
                ],
            ],
        ],
        'session' => [
            'class' => 'yii\redis\Session',
            'timeout' => 86400,
            'keyPrefix'=>'channel_',
            'cookieParams' => [
                'path' => '/',
                'domain' => ".pnlyy.com",
            ],
            'redis' => [
                'class' => 'yii\redis\Connection',
                'hostname' => 'r-bp1a043da361fc54195.redis.rds.aliyuncs.com',
                'port' => 6379,
                'database' => 1,
                'password' => '9YGfKuDKPwhOY65D'
            ],
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=vip-master1-vpc.mysql.rds.aliyuncs.com;port=3306;dbname=music',
            'username' => 'vipapp',
            'password' => 'th$OumL*a$N3fqM!',
            'charset' => 'utf8',
            'slaveConfig' => [
                'username' => 'vipapp',
                'password' => 'th$OumL*a$N3fqM!',
                'charset' => 'utf8',
            ],
            'slaves' => [
                ['dsn' => 'mysql:host=vip-slave1-vpc.mysql.rds.aliyuncs.com;port=3306;dbname=music'],
                ['dsn' => 'mysql:host=vip-slave2-vpc.mysql.rds.aliyuncs.com;port=3306;dbname=music'],
                ['dsn' => 'mysql:host=vip-slave4-vpc.mysql.rds.aliyuncs.com;port=3306;dbname=music'],
//                ['dsn' => 'mysql:host=vip-slave5-57.mysql.rds.aliyuncs.com;port=3306;dbname=music']
                ['dsn' => 'mysql:host=172.16.3.211;port=3306;dbname=music']
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_TEST) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs'=>['*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs'=>['*']
    ];
}

return $config;
