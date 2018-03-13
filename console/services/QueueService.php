<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/10/25
 * Time: 下午1:00
 */
namespace console\services;

use common\services\ErrorService;
use common\services\LogService;
use common\widgets\Debug;
use common\widgets\Getui;
use console\models\channel\ClassRoom;
use console\models\queue\AppPushLog;
use console\models\queue\ClassPushDevice;
use Yii;

class QueueService {

    public function processTeacher($envelope, $queue)
    {
        $msg = $envelope->getBody();
        $this->dealTeacherPushApp($msg);
        $queue->ack($envelope->getDeliveryTag());
        Yii::$app->db->close();
        //echo $msg . "\n";
    }

    public function processStudent($envelope, $queue)
    {
        $msg = $envelope->getBody();
        $this->dealStudentPushApp($msg);
        $queue->ack($envelope->getDeliveryTag());
        Yii::$app->db->close();
        //echo $msg . "\n";
    }

    public function processStemplate($envelope, $queue)
    {
        $msg = $envelope->getBody();
        $this->dealStudentTemplate($msg);
        $queue->ack($envelope->getDeliveryTag());
        Yii::$app->db->close();
    }

    public function processTeacherDev($envelope, $queue)
    {
        $msg = $envelope->getBody();
        $this->dealTeacherPushAppDev($msg);
        $queue->ack($envelope->getDeliveryTag());
        Yii::$app->db->close();
        echo '>>>>>>' . $msg . "\n";
    }

    public function processStudentDev($envelope, $queue)
    {
        $msg = $envelope->getBody();
        $this->dealStudentPushAppDev($msg);
        $queue->ack($envelope->getDeliveryTag());
        Yii::$app->db->close();
        //echo $msg . "\n";
    }

    private function dealTeacherPushApp($msg)
    {
        $msg = json_decode($msg, true);
        
        if (isset($msg['user_id'])) 
        {
            $this->push(0, $msg);

            LogService::appPushLog($msg, 0);
        }
        
        return true;
    }

    private function dealStudentPushApp($msg)
    {
        $msg = json_decode($msg, true);

        if (isset($msg['user_id'])) 
        {
            $this->push(1, $msg);

            LogService::appPushLog($msg, 1);
        }
        return true;
    }

    private function dealStudentTemplate($msg)
    {
        $msg = json_decode($msg, true);
        $this->sendStudentTemplate($msg);
        //LogService::sendStudentTemplate($msg);
        return true;
    }

    private function dealTeacherPushAppDev($msg)
    {
        $msg = json_decode($msg, true);

        if (isset($msg['user_id']))
        {
            $this->pushDev(0, $msg);

            LogService::appPushLog($msg, 0);
        }

        return true;
        Debug::debug();
    }

    private function dealStudentPushAppDev($msg)
    {
        $msg = json_decode($msg, true);

        if (isset($msg['user_id']))
        {
            $this->pushDev(1, $msg);

            LogService::appPushLog($msg, 1);
        }
        return true;
    }
    
    private function push($type, $msg)
    {
        $client = ClassPushDevice::find()
            ->select('clientID, clientType')
            ->where([
                'uid' => $msg['user_id'],
                'type' => $type
            ])->asArray()->one();

        $sound = $this->getSound($msg['type']);

        switch ($type)
        {
            case 0 :
                $AppId = Yii::$app->params['student_getui_appid'];
                $Appkey = Yii::$app->params['student_getui_appkey'];
                $MasterSecret =  Yii::$app->params['student_getui_mastersecret'];
                break;
            case 1 :
                $AppId = Yii::$app->params['teacher_getui_appid'];
                $Appkey = Yii::$app->params['teacher_getui_appkey'];
                $MasterSecret =  Yii::$app->params['teacher_getui_mastersecret'];
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
        }

        if ($res['result'] != 'ok')
        {
            ErrorService::AddAppPushError($res['result'], $client['clientID']);
        }

        return true;
        
    }

    private function pushDev($type, $msg)
    {
        $client = ClassPushDevice::find()
            ->select('clientID, clientType')
            ->where([
                'uid' => $msg['user_id'],
                'type' => $type
            ])->asArray()->one();

        $sound = $this->getSound($msg['type']);

        switch ($type)
        {
            case 0 :
                $AppId = Yii::$app->params['student_getui_appid_dev'];
                $Appkey = Yii::$app->params['student_getui_appkey_dev'];
                $MasterSecret =  Yii::$app->params['student_getui_mastersecret_dev'];
                break;
            case 1 :
                $AppId = Yii::$app->params['teacher_getui_appid_dev'];
                $Appkey = Yii::$app->params['teacher_getui_appkey_dev'];
                $MasterSecret =  Yii::$app->params['teacher_getui_mastersecret_dev'];
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
        }

        if ($res['result'] != 'ok')
        {
            ErrorService::AddAppPushError($res['result'], $client['clientID']);
        }

        return true;

    }
    
    private function sendStudentTemplate($msg)
    {
        $wechat = Yii::$app->wechat;
        $message = $this->buildMessage($msg);
        
        $classInfo = ClassRoom::find()
            ->select('time_class, time_end')
            ->where(['id' => $msg['class_id']])
            ->asArray()
            ->one();
        
        $message['url'] .= $msg['class_id'];
        $message['data']['keyword2'] = array('value' => date('m-d H:i', $classInfo['time_class']) . '-' . date('H:i', $classInfo['time_end']));
        
        if (!$wechat->sendTemplateMessage($message))
        {
            ErrorService::AddStudentTemplateError($msg, '发送失败');
        }
    }

    private function buildMessage($msg)
    {
        $param = $this->templateMapper($msg['type']);

        if (!empty($param))
        {
            $data = array(
                'first' => array('value' => $param['firstValue']),
                'keyword1' => array('value' => $param['key1word']),
                'keyword2' => array('value' => $param['key2word']),
                'remark' => array('value' => $param['remark'])
            );

            $message = array(
                'touser' => $msg['open_id'],
                'template_id' => $param['template_id'],
                'url' => $param['url'],
                'data' => $data
            );

            return $message;
        }else
        {
            ErrorService::AddStudentTemplateError($msg, '错误的type类型');
        }
    }
    
    private function templateMapper($type)
    {
        switch ($type)
        {
            case 1 :
                $param = Yii::$app->params['student_template_class_alarm'];
                break;
            default :
                $param = [];
        }
        
        return $param;
    }

    private function getSound($type)
    {
        $soundList = ['', 'apns.caf'];

        return $soundList[$type];
    }
}