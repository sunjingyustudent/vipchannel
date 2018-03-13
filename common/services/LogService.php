<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 16/7/20
 * Time: 上午9:57
 */

namespace common\services;

use common\models\logs\ChannelCrmLogs;
use common\models\logs\ResponseLog;
use common\sources\read\visit\VisitAccess;
use common\widgets\Queue;
//use common\widgets\QueueNew;
use yii;
use common\models\logs\ActionAppLog;
use common\models\AppPushLog;
use common\models\TeacherLog;
use common\models\ActionLogBean;

class LogService
{
//    public static function InputLog($request)
//    {
//        $logs = new ActionLogBean();
//        if (Yii::$app->user->isGuest) {
//            $logs->uid = 0;
//            $logs->name = "0";
//        } else {
//            $logs->uid = Yii::$app->user->id;
//            $logs->name = Yii::$app->user->identity->nickname;
//        }
//
//        $logs->auto_id = uniqid($logs->uid) . mt_rand(100000, 999999);
//        $logs->ip_address = $request->userIP;
//        $logs->method = $request->method;
//        $logs->user_agent = $request->userAgent;
//        $logs->action_url = $request->absoluteUrl;
//        $logs->action_input = serialize($request->bodyParams);
//        $logs->time_input = time();
//
//        $logs->action_type = '0';
//        $logs->action_output = '0';
//        $logs->comment = '0';
//        $logs->time_output = '0';
//
//        $logs->save();
//
//        return $logs->auto_id;
//    }


    public static function inputLog($request)
    {
        $logs = [];
        $logs['indexname'] = "vipchannel_logs";
        $logs["type"] = 0;

        //判断用户是否登录
        if (Yii::$app->user->isGuest) {
            $logs['uid'] = 0;
            $logs['name'] = '0';
        } else {
            $logs['uid'] = Yii::$app->user->id;
            $logs['name'] = Yii::$app->user->identity->nickname;
        }

        $logs['log_id'] = uniqid($logs['uid']) . mt_rand(100000, 999999);

        $logs['ip_address'] = $request->userIP;
        $logs['method'] = $request->method;
        $logs['user_agent'] = $request->userAgent;
        $logs['action_url'] = $request->absoluteUrl;
        $logs['params_input'] = empty($request->bodyParams) ? '0' : json_encode($request->bodyParams, JSON_UNESCAPED_UNICODE);
        $logs['time_input'] = time();

        Queue::produceLogs($logs, 'logstash', 'app_logs_routing');

        return $logs['log_id'];
    }

//    public static function OutputLog($logid,$action_type,$action_output,$comment)
//    {
//
//        $logs = new ActionLogBean();
//        $data = $logs::findOne(['auto_id' => $logid]);
//
//        $data->action_type = $action_type;
//        $data->action_output = $action_output;
//        $data->comment = $comment;
//        $data->time_output = time();
//
//        $data->save();
//    }

    public static function outputLog($logid, $actionOutput)
    {
        $logs = [];
        $logs['indexname'] = 'vipchannel_logs';
        $logs["type"] = 1;
        $logs["log_id"] = $logid;
        $logs["result_output"] = empty($actionOutput) ? '0' : json_encode($actionOutput, JSON_UNESCAPED_UNICODE);
        $logs["time_output"] = time();

        Queue::produceLogs($logs, 'logstash', 'app_logs_routing');
    }

    public static function appPushLog($msg, $role)
    {
        $pushLog = new AppPushLog();

        $pushLog->role = $role;
        $pushLog->user_id = $msg['user_id'];
        $pushLog->type = $msg['push_type'];
        $pushLog->title = $msg['title'];
        $pushLog->content = $msg['content'];
        $pushLog->icon = 'http://static.pnlyy.com/msg3.png';
        $pushLog->time_created = time();

        $pushLog->save();

        Yii::$app->db_log->close();
        //入队列

        $list = array(
            "indexname" => "app_push_log",
            "role" => $role,
            "user_id" => $msg['user_id'],
            "type" => $msg['push_type'],
            "title" => $msg['title'],
            "content" => $msg['content'],
            "icon" => 'http://static.pnlyy.com/msg3.png',
            "time_created" => time()
        );
        try {
            Queue::produceLogs($list, 'logstash', 'app_logs_routing');
//            QueueNew::produce($list) ;
        } catch (\Throwable $e) {
            return $e;
        }
    }

//    public static function InputTeacherLog($request)
//    {
//        $logs = new TeacherLog();
//        if (Yii::$app->user->isGuest) {
//            $logs->uid = 0;
//            $logs->name = "0";
//        } else {
//            $logs->uid = Yii::$app->user->id;
//            $logs->name = Yii::$app->user->identity->nickname;
//        }
//
//        $logs->auto_id = uniqid($logs->uid) . mt_rand(100000, 999999);
//        $logs->ip_address = $request->userIP;
//        $logs->method = $request->method;
//        $logs->user_agent = $request->userAgent;
//        $logs->action_url = $request->absoluteUrl;
//        $logs->action_input = serialize($request->bodyParams);
//        $logs->time_input = time();
//
//        $logs->action_type = '0';
//        $logs->action_output = '0';
//        $logs->comment = '0';
//        $logs->time_output = '0';
//
//        $logs->save();
//
//        return $logs->auto_id;
//    }

//    public static function OutputTeacherLog($logid, $actionType, $actionOutput, $comment)
//    {
//
//        $logs = new TeacherLog();
//        $data = $logs::findOne(['auto_id' => $logid]);
//
//        $data->action_type = $actionType;
//        $data->action_output = $actionOutput;
//        $data->comment = $comment;
//        $data->time_output = time();
//
//        $data->save();
//    }

//    public static function InputAppLog($uid, $request)
//    {
//        $logs = [];
//        $logs['indexname'] = "app_logs";
//        $logs["type"] = 0;
//        $logs["uid"] = $uid;
//        $logs["log_id"] = uniqid($uid) . mt_rand(100000, 999999);
//        $logs["ip_address"] = $request->userIP;
//        $logs["method"] = $request->method;
//        $logs["user_agent"] = $request->userAgent;
//        $logs["action_url"] = $request->absoluteUrl;
//        $logs["params_input"] = empty($request->bodyParams) ? '0' : json_encode($request->bodyParams, JSON_UNESCAPED_UNICODE);
//        $logs["time_input"] = time();
//
//        Queue::produceLogs($logs, 'logstash', 'app_logs_routing');
//
//        return $logs["log_id"];
//    }

//    public static function OutputAppLog($logID, $timing, $actionOutput)
//    {
//        $logs = [];
//        $logs['indexname'] = "app_logs";
//        $logs["type"] = 1;
//        $logs["log_id"] = $logID;
//        $logs["timing"] = $timing;
//        $logs["result_output"] = empty($actionOutput) ? '0' : json_encode($actionOutput, JSON_UNESCAPED_UNICODE);
//        $logs["time_output"] = time();
//
//        Queue::produceLogs($logs, 'logstash', 'app_logs_routing');
//    }

    public static function addAlertLog($data)
    {
        $timeNow = time();

        $log = new ResponseLog();

        foreach ($data as $row) {
            $_log = clone $log;

            $_log->open_id = $row['openid'];
            $_log->name = $row['name'];
            $_log->time_created = $timeNow;

            $_log->save();
        }

        return true;
    }

//    public static function InputChannelLog($request)
//    {
//        $logs = new ChannelCrmLogs();
//
//        if (Yii::$app->user->isGuest) {
//            $logs->uid = 0;
//            $logs->name = "0";
//        } else {
//            $logs->uid = Yii::$app->user->id;
//            $logs->name = Yii::$app->user->identity->nickname;
//        }
//
//        $logs->auto_id = uniqid($logs->uid) . mt_rand(100000, 999999);
//        $logs->ip_address = $request->userIP;
//        $logs->method = $request->method;
//        $logs->user_agent = $request->userAgent;
//        $logs->action_url = $request->absoluteUrl;
//        $logs->action_input = serialize($request->bodyParams);
//        $logs->time_input = time();
//
//        $logs->save();
//
//        return $logs->auto_id;
//    }

//    public static function OutputChannelLog($logid, $actionOutput)
//    {
//        $data = ChannelCrmLogs::findOne(['auto_id' => $logid]);
//
//        $data->action_output = $actionOutput;
//        $data->time_output = time();
//
//        $data->save();
//    }
}
