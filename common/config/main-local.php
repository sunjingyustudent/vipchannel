<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=192.168.40.219;dbname=music',
            'username' => 'viptest',
            'password' => 'viptest_2017',
            'charset' => 'utf8',
        ],
        'db_pnl' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=192.168.40.219;dbname=music_school',
            'username' => 'viptest',
            'password' => 'viptest_2017',
            'charset' => 'utf8',
        ],
        'db_log' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=192.168.40.219;dbname=music_log',
            'username' => 'viptest',
            'password' => 'viptest_2017',
            'charset' => 'utf8',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            'useFileTransport' => true,
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '192.168.40.221',
            'port' => 6379,
            'database' => 0,
        ],
        'wechat' => [
            'class' => 'callmez\wechat\sdk\MpWechat',
            'appId' => 'wx3e2fc83bfa5f2d52',
            'appSecret' => '9ab80e0fea9ddffc516eb6d58e392aed',
            'token' => 'vippnl20160724',
            'encodingAesKey' => 'JAYwgAjcUmKnRZEOWNDzrWme3KVAAQYo560u9GPX2pL',
        ],
        'wechat_new' => [
            'class' => 'callmez\wechat\sdk\MpWechat',
            'appId' => 'wxdf0ae7354d12c4fd',
            'appSecret' => '9c880b392f5b6b276ac4489fda832124',
            'token' => 'vippnl20160724',
            'encodingAesKey' => 'JAYwgAjcUmKnRZEOWNDzrWme3KVAAQYo560u9GPX2pL',
        ],
        'wechat_teacher' => [
            'class' => 'callmez\wechat\sdk\MpWechat',
            'appId' => 'wxc6d365973689373c',
            'appSecret' => '64e221aa61cd75c58030558cbd468ea1',
            'token' => 'vippnl20160724',
            'encodingAesKey' => 'JAYwgAjcUmKnRZEOWNDzrWme3KVAAQYo560u9GPX2pL',
        ]

    ],
/*
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=114.215.170.174;port=5937;dbname=music',
            'username' => 'viptest',
            'password' => 'viptest_2017',
            'charset' => 'utf8',
        ],
        'db_pnl' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=114.215.170.174;port=5937;dbname=music_school',
            'username' => 'viptest',
            'password' => 'viptest_2017',
            'charset' => 'utf8',
        ],
        'db_log' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=114.215.170.174;port=5937;dbname=music_log',
            'username' => 'viptest',
            'password' => 'viptest_2017',
            'charset' => 'utf8',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            'useFileTransport' => true,
        ],
        'wechat' => [
            'class' => 'callmez\wechat\sdk\MpWechat',
            'appId' => 'wx3e2fc83bfa5f2d52',
            'appSecret' => '9ab80e0fea9ddffc516eb6d58e392aed',
            'token' => 'vippnl20160724',
            'encodingAesKey' => 'JAYwgAjcUmKnRZEOWNDzrWme3KVAAQYo560u9GPX2pL',
        ],
        'wechat_new' => [
            'class' => 'callmez\wechat\sdk\MpWechat',
            'appId' => 'wxdf0ae7354d12c4fd',
            'appSecret' => '9c880b392f5b6b276ac4489fda832124',
            'token' => 'vippnl20160724',
            'encodingAesKey' => 'JAYwgAjcUmKnRZEOWNDzrWme3KVAAQYo560u9GPX2pL',
        ],
        'wechat_teacher' => [
            'class' => 'callmez\wechat\sdk\MpWechat',
            'appId' => 'wxc6d365973689373c',
            'appSecret' => '64e221aa61cd75c58030558cbd468ea1',
            'token' => 'vippnl20160724',
            'encodingAesKey' => 'JAYwgAjcUmKnRZEOWNDzrWme3KVAAQYo560u9GPX2pL',
        ]
]
    */
    
];
