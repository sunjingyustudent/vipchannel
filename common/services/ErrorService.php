<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 16/7/20
 * Time: 上午9:57
 */
namespace common\services;

use common\models\ErrorAPIBean;
use common\models\ErrorAppPush;
use common\models\ErrorStudentTemplate;
use common\models\logs\ErrorChannelLogs;
use common\widgets\Queue;
//use common\widgets\QueueNew;
use yii;
use common\models\ErrorLogBean;

class ErrorService
{
    public static function addError($ex, $request, $indexname, $type)
    {
        if ($ex && $ex->getMessage() != "Login Required") {
            $logs = [];
            $logs["indexname"] = $indexname;
            $logs["type"] = $type;
            if (Yii::$app->user->isGuest) {
                $logs["uid"] = 0;
                $logs["name"] = "0";
            } else {
                $logs["uid"] = Yii::$app->user->id;
                $logs["name"] = Yii::$app->user->identity->nickname;
            }

            $file = $ex->getFile();
            $line = $ex->getLine();

            $error_path = "file: {$file} [line: {$line}]";

            $logs["ip_address"] = $request->userIP;
            $logs["error_code"] = $ex->getCode();
            $logs["error_msg"] = $ex->getMessage();
            $logs["error_file"] = $error_path;
            $logs["error_url"] = $request->absoluteUrl;
            $logs["error_param"] = http_build_query($_POST);
            $logs["time_created"] = time();

            Queue::produceLogs($logs, 'logstash', 'app_logs_routing');
        }
    }

    public static function addChannelError($ex, $request)
    {
        if ($ex && $ex->getMessage() != "Login Required") {
            $logs = [];
            $logs["indexname"] = "weberror";
            $logs["type"] = "channelWeb";
            if (Yii::$app->user->isGuest) {
                $logs["uid"] = 0;
                $logs["name"] = "0";
            } else {
                $logs["uid"] = Yii::$app->user->id;
                $logs["name"] = Yii::$app->user->identity->nickname;
            }

            $file = $ex->getFile();
            $line = $ex->getLine();

            $error_path = "file: {$file} [line: {$line}]";

            $logs["ip_address"] = $request->userIP;
            $logs["error_code"] = $ex->getCode();
            $logs["error_msg"] = $ex->getMessage();
            $logs["error_file"] = $error_path;
            $logs["error_url"] = $request->absoluteUrl;
            $logs["error_param"] = http_build_query($_POST);
            $logs["time_created"] = time();

            Queue::produceLogs($logs, 'logstash', 'app_logs_routing');
        }
    }

    public function addOperationError($ex, $request)
    {
        if ($ex && $ex->getMessage() != "Login Required") {
            $logs = [];
            $logs["indexname"] = "weberror";
            $logs["type"] = "optWeb";
            if (Yii::$app->user->isGuest) {
                $logs["uid"] = 0;
                $logs["name"] = "0";
            } else {
                $logs["uid"] = Yii::$app->user->id;
                $logs["name"] = Yii::$app->user->identity->nickname;
            }

            $file = $ex->getFile();
            $line = $ex->getLine();

            $error_path = "file: {$file} [line: {$line}]";

            $logs["ip_address"] = $request->userIP;
            $logs["error_code"] = $ex->getCode();
            $logs["error_msg"] = $ex->getMessage();
            $logs["error_file"] = $error_path;
            $logs["error_url"] = $request->absoluteUrl;
            $logs["error_param"] = http_build_query($_POST);
            $logs["time_created"] = time();

            Queue::produceLogs($logs, 'logstash', 'app_logs_routing');
        }
    }

    public static function addAPIError($ex, $request)
    {
        if ($ex) {
//            $err = new ErrorAPIBean();

            $file = $ex->getFile();
            $line = $ex->getLine();

            $error_path = "file: {$file} [line: {$line}]";
            $error_url = $request->absoluteUrl;
            $error_param = http_build_query($_POST);

//            $err->error_code = $ex->getCode();
//            $err->error_msg = $ex->getMessage();
//            $err->error_file = $error_path;
//            $err->error_url = $error_url;
//            $err->error_param = $error_param;
//            $err->time_created = time();
//            $err->save();


            $list = array(
                "indexname" => "error_api_logs" ,
                "error_code" => $ex->getCode() ,
                "error_msg" => $ex->getMessage() ,
                "error_file" => $error_path ,
                "error_url" => $error_url ,
                "error_param" => $error_param ,
                "time_created" => time()
            );
            Queue::produceLogs($list, 'logstash', 'app_logs_routing');
        }
    }
    
    public static function addAppPushError($res, $clientId)
    {
        if (!empty($clientId)) {
//            $err = new ErrorAppPush();
//
//            $err->err_msg = $res;
//            $err->clientID = $clientId;
//
//            $err->save();
//
//            Yii::$app->db_log->close();



            $list = array(
                "indexname" => "error_app_push",
                "err_msg" => $res,
                "clientId" => $clientId,
            );
            Queue::produceLogs($list, 'logstash', 'app_logs_routing');
        }
    }

    public static function addStudentTemplateError($msg, $errMsg = '')
    {
        $errLogs = new ErrorStudentTemplate();
        $errLogs->open_id = $msg['open_id'];
        $errLogs->type = $msg['type'];
        $errLogs->err_msg = $errMsg;
        $errLogs->save();

        Yii::$app->db_log->close();
    }
}
