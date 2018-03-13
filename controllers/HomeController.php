<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 16/6/20
 * Time: 下午1:50
 */
namespace app\controllers;

use crm\models\chat\ChatWaitKefu;
use Yii;
use yii\base\Exception;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\db\Query;
use yii\data\Pagination;
use common\models\passwordForm;
use crm\models\chat\ChatWait;
use common\services\LogService;
use common\models\music\ChannelRedChance;
use common\models\User;

class HomeController extends BaseController
{

    /**
     * CRM首页入口
     */
    public function actionPortal()
    {
        $identity = User::isDenyPasswd(Yii::$app->user->identity->password_hash);

        return $this->render('index', ['identity' => $identity]);
    }

    /*
     * 用户密码修改
     */
    public function actionPassword()
    {
        return $this->renderPartial('password');
    }

    public function actionUpdatePassword()
    {
        $req = YII::$app->request;

        if ($req->isPost) {
            if (($res = User::isDenyPasswd($req->post('pwd'), false)) !== true) {
                return $res;
            }

            $model = new passwordForm();
            $model->token = $model->getPasswordResetToken();
            $model->password = $req->post('pwd');

            if ($model->changePassword()) {
                Yii::$app->user->logout();
                return 1;
            }

            return 0;
        }
        return 0;
    }
}
