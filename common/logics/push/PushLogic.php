<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/6
 * Time: 下午8:39
 */
namespace common\logics\push;

use common\services\ErrorService;
use common\services\LogService;
use common\widgets\Getui;
use Yii;
use yii\base\Object;

class PushLogic extends Object implements IPush
{
    /** @var  \common\sources\read\push\PushAccess  $RPushAccess */
    private $RPushAccess;

    public function init()
    {
        $this->RPushAccess = Yii::$container->get('RPushAccess');
    }

    public function dealTeacherPushApp($msg)
    {
        $msg = json_decode($msg, true);

        if (isset($msg['user_id']))
        {
            $this->push(0, $msg);

            LogService::appPushLog($msg, 0);
        }

        return true;
    }

    public function dealStudentPushApp($msg)
    {
        $msg = json_decode($msg, true);

        if (isset($msg['user_id']))
        {
            $this->push(1, $msg);

            LogService::appPushLog($msg, 1);
        }
        
        return true;
    }

    public function dealStudentPushAppDev($msg)
    {
        $msg = json_decode($msg, true);

        if (isset($msg['user_id']))
        {
            $this->pushDev(1, $msg);

            LogService::appPushLog($msg, 1);
        }
        
        return true;
    }

    public function dealTeacherPushAppDev($msg)
    {
        $msg = json_decode($msg, true);

        if (isset($msg['user_id']))
        {
            $this->pushDev(0, $msg);

            LogService::appPushLog($msg, 0);
        }

        return true;
    }

    private function push($type, $msg)
    {
        $client = $this->RPushAccess->getPushClientInfo($type, $msg['user_id']);

        $sound = $this->getSound($msg['type']);

        //老师的iphone的版本,设置type为2
        if($type== 1 && strstr($client['deviceInfor'] , "iPhone") && $client['clientType'] == 2)
        {
            $type = 2 ;
        }
        //判断是否获取稳定版本的可以
        $stable = "" ;
        switch ($client['is_stable_version'])
        {
            case 0:
                $stable = "" ;
                break ;
            case 1:
                $stable = "_stable" ;
                break ;
        }
        switch ($type)
        {
            case 0 :
                $AppId = Yii::$app->params['student_getui'.$stable.'_appid'];
                $Appkey = Yii::$app->params['student_getui'.$stable.'_appkey'];
                $MasterSecret =  Yii::$app->params['student_getui'.$stable.'_mastersecret'];
                break;
            case 1 :
                $AppId = Yii::$app->params['teacher_getui'.$stable.'_appid'];
                $Appkey = Yii::$app->params['teacher_getui'.$stable.'_appkey'];
                $MasterSecret = Yii::$app->params['teacher_getui'.$stable.'_mastersecret'];
                break;
            case 2 :
                $AppId = Yii::$app->params['teacher_getui_iPhone'.$stable.'_appid'];
                $Appkey = Yii::$app->params['teacher_getui_iPhone'.$stable.'_appkey'];
                $MasterSecret =  Yii::$app->params['teacher_getui_iPhone'.$stable.'_mastersecret'];
                break;
        }

        switch ($client['clientType'])
        {
            case 0 :
                $res = Getui::pushAPN(
                    $AppId, $Appkey, $MasterSecret,
                    $msg['title'], $msg['content'],
                    $msg['params'], $client['clientID'], $sound
                );

                break;
            case 1 :
                $str = empty($sound) ? '' : '@music:' . $sound;
                $str .= empty($msg['params']) ? '' : '@' . $msg['params'];
                $str .= '@time:' . time();
                $res = Getui::pushMessageToSingle(
                    $AppId, $Appkey, $MasterSecret,
                    $msg['title'], $msg['content'],
                    $str, $client['clientID']
                );

                break;
            case 2 :
                $res = Getui::pushAPN(
                    $AppId, $Appkey, $MasterSecret,
                    $msg['title'], $msg['content'],
                    $msg['params'], $client['clientID'], $sound
                );

                break;
        }

        if ($res['result'] != 'ok')
        {
            ErrorService::AddAppPushError($res['result'], $client['clientID']);
        }

        return true;
    }
    
    public function pushDev($type, $msg)
    {
        $client = $this->RPushAccess->getPushClientInfo($type, $msg['user_id']);

        $sound = $this->getSound($msg['type']);
        $stable = "" ;

        if($type== 1 && strstr($client['deviceInfor'] , "iPhone") && $client['clientType'] == 2)//老师的iphone的版本,设置type为2
        {
            $type = 2 ;
        }
        switch ($client['is_stable_version'])
        {
            case 0:
                $stable = "" ;
                break ;
            case 1:
                $stable = "_stable" ;
                break ;
        }

        switch ($type)
        {
            case 0 :
                $AppId = Yii::$app->params['student_getui'.$stable.'_appid_dev'];
                $Appkey = Yii::$app->params['student_getui'.$stable.'_appkey_dev'];
                $MasterSecret =  Yii::$app->params['student_getui'.$stable.'_mastersecret_dev'];
                break;
            case 1 :
                $AppId = Yii::$app->params['teacher_getui'.$stable.'_appid_dev'];
                $Appkey = Yii::$app->params['teacher_getui'.$stable.'_appkey_dev'];
                $MasterSecret =  Yii::$app->params['teacher_getui'.$stable.'_mastersecret_dev'];
                break;
            case 2 :
                $AppId = Yii::$app->params['teacher_getui_iPhone'.$stable.'_appid_dev'];
                $Appkey = Yii::$app->params['teacher_getui_iPhone'.$stable.'_appkey_dev'];
                $MasterSecret =  Yii::$app->params['teacher_getui_iPhone'.$stable.'_mastersecret_dev'];
                break;
        }

        switch ($client['clientType'])
        {
            case 0 :
                $res = Getui::pushAPN(
                    $AppId, $Appkey, $MasterSecret,
                    $msg['title'], $msg['content'],
                    $msg['params'], $client['clientID'], $sound
                );

                break;
            case 1 :
                $str = empty($sound) ? '' : '@music:' . $sound;
                $str .= empty($msg['params']) ? '' : '@' . $msg['params'];
                $str .= '@time:' . time();
                $res = Getui::pushMessageToSingle(
                    $AppId, $Appkey, $MasterSecret,
                    $msg['title'], $msg['content'],
                    $str, $client['clientID']
                );

                break;
            case 2 :
                $res = Getui::pushAPN(
                    $AppId, $Appkey, $MasterSecret,
                    $msg['title'], $msg['content'],
                    $msg['params'], $client['clientID'], $sound
                );

                break;
        }

        if ($res['result'] != 'ok')
        {
            ErrorService::AddAppPushError($res['result'], $client['clientID']);
        }

        return true;
    }

    private function getSound($type)
    {
        $soundList = ['', 'apns.caf'];

        return $soundList[$type];
    }
}