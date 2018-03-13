<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    //require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php')
    //require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'gii'],
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],
    'controllerNamespace' => 'console\controllers',
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
//        'errorHandler' => [
//            'maxSourceLines' => 20,
//            'errorAction' => 'site/error-monitor',
//        ],
//        'urlManager' => [
//            'enablePrettyUrl' => true,
//            'showScriptName' => false,
//            'rules' => [
//            ],
//        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' =>false,//这句一定有，false发送邮件，true只是生成邮件在runtime文件夹下，不发邮件
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.exmail.qq.com',  //每种邮箱的host配置不一样
                'username' => 'no-reply@pnlyy.com',
                'password' => 'music1234',
                'port' => '465',
                'encryption' => 'ssl',
                ],
            'messageConfig'=>[
                'charset'=>'UTF-8',
                'from'=>['no-reply@pnlyy.com'=>'no-reply']
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
