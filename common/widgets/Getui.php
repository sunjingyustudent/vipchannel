<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/10/25
 * Time: 下午8:16
 */
namespace common\widgets;

//header("Content-Type: text/html; charset=utf-8");

include_once dirname(__DIR__) . '/lib/GETUI_PHP_SDK/GETUI_PHP_SDK/php Demo 4.0.1.0/IGt.Push.php';
include_once dirname(__DIR__) . '/lib/GETUI_PHP_SDK/GETUI_PHP_SDK/php Demo 4.0.1.0/igetui/IGt.AppMessage.php';
include_once dirname(__DIR__) . '/lib/GETUI_PHP_SDK/GETUI_PHP_SDK/php Demo 4.0.1.0/igetui/IGt.APNPayload.php';
include_once dirname(__DIR__) . '/lib/GETUI_PHP_SDK/GETUI_PHP_SDK/php Demo 4.0.1.0/igetui/template/IGt.BaseTemplate.php';
include_once dirname(__DIR__) . '/lib/GETUI_PHP_SDK/GETUI_PHP_SDK/php Demo 4.0.1.0/IGt.Batch.php';
include_once dirname(__DIR__) . '/lib/GETUI_PHP_SDK/GETUI_PHP_SDK/php Demo 4.0.1.0/igetui/utils/AppConditions.php';

define('HOST', 'http://sdk.open.api.igexin.com/apiex.htm');

class Getui {

    public static function pushAPN($appId, $appKey, $masterSecret, $title, $content, $params, $clientId, $sound)
    {
        //APN简单推送
        $igt = new \IGeTui(HOST, $appKey, $masterSecret);
        $template = new \IGtAPNTemplate();
        $apn = new \IGtAPNPayload();
        $alertmsg = new \SimpleAlertMsg();
        $alertmsg->alertMsg = $content;
        $apn->alertMsg = $alertmsg;
        $apn->badge = 1;
        $apn->sound = $sound;
        $apn->add_customMsg("payload", "payload");
        $apn->contentAvailable = 1;
        $apn->category = $params;
        $template->set_apnInfo($apn);
        $message = new \IGtSingleMessage();
        $message->set_data($template);

        $alertmsg = new \DictionaryAlertMsg();
        $alertmsg->body = "body";
        $alertmsg->actionLocKey = "ActionLockey";
        $alertmsg->locKey = "LocKey";
        $alertmsg->title = "Title";
        $alertmsg->titleLocKey = "TitleLocKey";
        $alertmsg->titleLocArgs = array("TitleLocArg");

        $ret = $igt->pushAPNMessageToSingle($appId, $clientId, $message);
        //var_dump($ret);
        return $ret;
    }


//
//服务端推送接口，支持三个接口推送
//1.PushMessageToSingle接口：支持对单个用户进行推送
//2.PushMessageToList接口：支持对多个用户进行推送，建议为50个用户
//3.pushMessageToApp接口：对单个应用下的所有用户进行推送，可根据省份，标签，机型过滤推送
//

//单推接口案例
    public static function pushMessageToSingle($appId, $appKey, $masterSecret, $title, $content, $params, $clientId)
    {
        $igt = new \IGeTui(NULL, $appKey, $masterSecret, false);

        $template = self::IGtTransmissionTemplate($appId, $appKey, $title, $content, $params);

        $message = new \IGtSingleMessage();

        $message->set_isOffline(true);//是否离线
        $message->set_offlineExpireTime(3600 * 12 * 1000);//离线时间
        $message->set_data($template);//设置推送消息类型
    	$message->set_PushNetWorkType(0);//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
        //接收方
        $target = new \IGtTarget();
        $target->set_appId($appId);
        $target->set_clientId($clientId);


        try {
            $rep = $igt->pushMessageToSingle($message, $target);
            //var_dump($rep);
            //var_dump($clientId);
            return $rep;

        } catch (RequestException $e) {
            $requstId = e . getRequestId();
            $rep = $igt->pushMessageToSingle($message, $target, $requstId);
            //var_dump($rep);
            return $rep;
        }

    }

    
    private static function IGtTransmissionTemplate($appId, $appKey, $title, $content, $params)
    {
        $template = new \IGtTransmissionTemplate();
        $template->set_appId($appId);//应用appid
        $template->set_appkey($appKey);//应用appkey
        $template->set_transmissionType(2);//透传消息类型
        $template->set_transmissionContent($content . $params);//透传内容

        //APN高级推送
        $apn = new \IGtAPNPayload();
        $alertmsg = new \DictionaryAlertMsg();
        $alertmsg->body = "body";
        $alertmsg->actionLocKey = "ActionLockey";
        $alertmsg->locKey = "LocKey";
        $alertmsg->locArgs = array("locargs");
        $alertmsg->launchImage = "launchimage";
//        IOS8.2 支持
        $alertmsg->title = $title;
        $alertmsg->titleLocKey = "TitleLocKey";
        $alertmsg->titleLocArgs = array("TitleLocArg");

        $apn->alertMsg = $alertmsg;
        $apn->badge = 1;
        $apn->sound = '';
        $apn->add_customMsg("payload", "payload");
        $apn->contentAvailable = 1;
        $apn->category = "ACTIONABLE";
        $template->set_apnInfo($apn);

        return $template;
    }
}