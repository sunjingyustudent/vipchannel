<?php
namespace console\controllers;

use yii\console\Controller;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function beforeAction($action)
    {
        return parent::beforeAction($action); // TODO: Change the autogenerated stub
    }

    public function afterAction($action, $result)
    {
        return parent::afterAction($action, $result); // TODO: Change the autogenerated stub
    }

    public function actionIndex()
    {
        echo "start:" . date('Y-m-d H:s') . "\n";
        sleep(1);
        echo "123456\n\n";
        sleep(2);
        echo "end:" . date('Y-m-d H:s') . "\n";
    }

    public function actionErrorMonitor()
    {
        return 1;
    }
}
