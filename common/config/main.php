<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=vip-master1-vpc.mysql.rds.aliyuncs.com;port=3306;dbname=music',
            'username' => 'vipapp',
            'password' => 'th$OumL*a$N3fqM!',
            'charset' => 'utf8',
        ],
        'db_pnl' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=vip-master1-vpc.mysql.rds.aliyuncs.com;port=3306;dbname=music_school',
            'username' => 'vipapp',
            'password' => 'th$OumL*a$N3fqM!',
            'charset' => 'utf8',
        ],
        'db_log' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=vip-master1-vpc.mysql.rds.aliyuncs.com;port=3306;dbname=music_log',
            'username' => 'vipapp',
            'password' => 'th$OumL*a$N3fqM!',
            'charset' => 'utf8',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            'useFileTransport' => true,
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '192.168.1.221',
            'port' => 6379,
            'database' => 0,
        ],
        'wechat_new' => [
            'class' => 'callmez\wechat\sdk\MpWechat',
            'appId' => 'wx4384ef5fb33ba448',
            'appSecret' => 'ed67fd929f7746f72471b3046468ae9f',
            'token' => 'vippnl20160724',
            'encodingAesKey' => 'JAYwgAjcUmKnRZEOWNDzrWme3KVAAQYo560u9GPX2pL'
        ],
        //vip陪练
        'wechat' => [
            'class' => 'callmez\wechat\sdk\MpWechat',
            'appId' => 'wxcdef6dd053995bc7',
            'appSecret' => '12f0ff5316f13bf981de96168a9e5e51',
            'token' => 'vippnl20160724',
            'encodingAesKey' => 'JAYwgAjcUmKnRZEOWNDzrWme3KVAAQYo560u9GPX2pL'
        ],
        'wechat_teacher' => [
            'class' => 'callmez\wechat\sdk\MpWechat',
            'appId' => 'wxd8321c73e70f80f0',
            'appSecret' => '74eb2f0e7530145c75622fbd7330196e',
            'token' => 'vippnl20160724',
            'encodingAesKey' => 'JAYwgAjcUmKnRZEOWNDzrWme3KVAAQYo560u9GPX2pL',
        ]
    ],
];
