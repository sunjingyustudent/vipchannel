<?php
namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use common\models\LoginForm;
use common\services\LogService;
use common\services\ErrorService;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Qiniu\Storage\BucketManager;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /** @var  \common\logics\account\AccountLogic $musicService */
    private $accountService;

    public function init()
    {
        $this->accountService = Yii::$container->get('accountService');
        parent::init();
    }

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * 加载错误action
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * CRM用户登录页面
     */
    public function actionLogin()
    {
        //用户已注册
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        return $this->renderPartial('login');
    }

    /**
     * 登陆post
     */
    public function actionLogon()
    {
        $req = Yii::$app->request;

        return $this->accountService->doChannelLogon($req);
    }

    /**
     * 用户Logout
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
    
    public function actionChangeHead()
    {
        return $this->renderPartial('head');
    }

    public function actionDoChangeHead()
    {
        $logid = $this->logid;

        return $this->accountService->doChangeHead($logid);
    }
}
