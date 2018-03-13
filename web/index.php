<?php
define('ENV_EXIST', file_exists(__DIR__ . '/../env.php'));

if (ENV_EXIST)
{
    require(__DIR__ . '/../env.php');
}

if (ENV_EXIST && ENV_CONFIG === 'dev')
{
    defined('YII_DEBUG') or define('YII_DEBUG', true);
    defined('YII_ENV') or define('YII_ENV', 'dev');
}


//defined('YII_DEBUG') or define('YII_DEBUG', true);

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../common/config/bootstrap.php');
require(__DIR__ . '/../config/bootstrap.php');
require(__DIR__ . '/../common/helpers/function.php');

if (ENV_EXIST && ENV_CONFIG === 'dev')
{
    $config = yii\helpers\ArrayHelper::merge(
        require(__DIR__ . '/../common/config/main-local.php'),
        require(__DIR__ . '/../config/main-local.php')
    );
} else {
    $config = yii\helpers\ArrayHelper::merge(
        require(__DIR__ . '/../common/config/main.php'),
        require(__DIR__ . '/../config/main.php')
    );
}

//向容器注册接口
require(__DIR__ . '/reg.php');

$application = new yii\web\Application($config);


$application->run();
