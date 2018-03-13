<?php

/**
 * Created by PhpStorm.
 * User: wangkai
 * Date: 17/2/15
 * Time: 11:07
 */

namespace common\logics\push;

use common\models\music\ClassRoom;
use common\models\music\StudentUserShare;
use common\models\music\User;
use common\models\music\WechatAcc;
use common\services\ErrorService;
use common\widgets\Queue;
use common\widgets\TimeFormatHelper;
use Yii;
use common\widgets\Request;
use yii\base\Object;
use yii\helpers\VarDumper;
use common\widgets\TemplateBuilder;

class MessageLogic extends Object implements IMessage
{

    /** @var  \common\sources\read\complain\ComplainAccess  $RComplainAccess */
    private $RComplainAccess;

    /** @var  \common\sources\read\account\AccountAccess  $RAccountAccess */
    private $RAccountAccess;

    /** @var  \common\sources\read\classes\ClassAccess  $RClassAccess */
    private $RClassAccess;

    /** @var  \common\sources\read\student\StudentAccess  $RStudentAccess */
    private $RStudentAccess;

    /** @var  \common\sources\read\chat\ChatAccess  $RChatAccess */
    private $RChatAccess;

    /** @var  \common\sources\read\channel\ChannelAccess  $RChannelAccess */
    private $RChannelAccess;

    /** @var  \common\sources\read\chat\ChannelChatAccess  $RChannelChatAccess */
    private $RChannelChatAccess;

    /** @var  \common\sources\write\chat\ChannelChatAccess  $WChannelChatAccess */
    private $WChannelChatAccess;

    public function init()
    {
        $this->RStudentAccess = Yii::$container->get('RStudentAccess');
        $this->RClassAccess = Yii::$container->get('RClassAccess');
        $this->RComplainAccess = Yii::$container->get('RComplainAccess');
        $this->RAccountAccess = Yii::$container->get('RAccountAccess');
        $this->RChatAccess = Yii::$container->get('RChatAccess');
        $this->RChannelAccess = Yii::$container->get('RChannelAccess');
        $this->RChannelChatAccess = Yii::$container->get('RChannelChatAccess');
        $this->WChannelChatAccess = Yii::$container->get('WChannelChatAccess');
    }

    public function sendKefuMessage($msg)
    {
        $wechat = Yii::$app->wechat;
        $msg = json_decode($msg, true);
        if (!$wechat->sendMessage($msg)) {
            //error_hander
        }
    }

    public function sendChannelKefuMessage($msg)
    {
        $wechat = Yii::$app->wechat_new;
        if (!$wechat->sendMessage($msg)) {
            //error_hander
        }
    }

    public function sendTeacherKefuMessage($msg)
    {
        $wechat = Yii::$app->wechat_teacher;

        if (!$wechat->sendMessage($msg)) {
            //error_hander
        }
    }


    private function buildMessage($msg, $openId)
    {
        return array(
            'touser' => $openId,
            'msgtype' => 'text',
            'text' => array('content' => $msg),
        );
    }

    public function sendManualAssignClassMessage($classId)
    {
        $open_id = $this->RClassAccess->getMessageOpenId($classId);

        $classInfo = $this->RClassAccess->getClassTimeAndStudentName($classId);
        $weekarray = array("日", "一", "二", "三", "四", "五", "六");
        $time = date('m月d日', $classInfo['time_class']) . ' 周' . $weekarray[date('w', $classInfo['time_class'])] . ' ' . date('H:i', $classInfo['time_class']);


        $message = $classInfo['nick'] . "家长，您的课程已安排：\n{$time} 开始\n"
                . "<a href='" . Yii::$app->params['down_class_url'] . "'>(点击下载上课端，如已下载请忽略)</a> \n"
                . "请在上课前5分钟打开上课端并进入教室。在上课端可自行添加陪练乐谱，或留言由您的专属服务为您添加。\n\n"
                . "<a href='" . Yii::$app->params['sales_url'] . "'>我的专属服务</a>";

        return Queue::produce(
                        $this->buildMessage($message, $open_id), 'async', 'kefu_msg'
        );
    }

    public function sendUpdateClassTimeMessage($classId, $initTime, $updateTime)
    {
        $open_id = $this->RClassAccess->getMessageOpenId($classId);
        $nick = $this->RStudentAccess->getNickByOpenId($open_id);

        $weekarray = array("日", "一", "二", "三", "四", "五", "六");
        $initTime = date('m月d日', $initTime) . ' 周' . $weekarray[date('w', $initTime)] . ' ' . date('H:i', $initTime);

        $updateTime = $this->timeFormat($updateTime);
        $updateTime = date('m月d日', $updateTime) . ' 周' . $weekarray[date('w', $updateTime)] . ' ' . date('H:i', $updateTime);

        $message = $nick . "家长，您的 {$initTime} 已被更改为：\n{$updateTime} 开始\n"
                . "<a href='" . Yii::$app->params['down_class_url'] . "'>（点击下载上课端，如已下载请忽略）</a>\n"
                . "请在上课前5分钟打开上课端并进入教室。\n"
                . "在上课端可自行添加陪练乐谱，或留言由您的专属服务为您添加。\n\n"
                . "<a href='" . Yii::$app->params['sales_url'] . "'>我的专属服务</a>";

        return Queue::produce(
                        $this->buildMessage($message, $open_id), 'async', 'kefu_msg'
        );
    }

    private function timeFormat($time)
    {
        $arr = explode('T', $time);

        return strtotime($arr[0] . ' ' . $arr[1]);
    }


    public function sendBeingProcessComplainMessage($complainId)
    {
        $complain_info = $this->RComplainAccess->getComplainById($complainId);

        $nick = $this->RStudentAccess->getNickByOpenId($complain_info['open_id']);

        $weekarray = array("日", "一", "二", "三", "四", "五", "六");
        $time = date('m月d日', $complain_info['time_created']) . ' 周' . $weekarray[date('w', $complain_info['time_created'])] . ' ' . date('H:i', $complain_info['time_created'])
                . '的投诉已被接受处理';

        $message = $nick . "家长，您于{$time}。如有需要我们的相关处理人员会电话与您沟通，\n"
                . "如有其他问题请联系我们的专属服务。\n\n"
                . "<a href='" . Yii::$app->params['sales_url'] . "'>我的专属服务</a>";


        return Queue::produce(
                        $this->buildMessage($message, $complain_info['open_id']), 'async', 'kefu_msg'
        );
    }

    public function sendProcessComplainMessage($complainId)
    {
        $complain_info = $this->RComplainAccess->getComplainById($complainId);

        $nick = $this->RStudentAccess->getNickByOpenId($complain_info['open_id']);

        $weekarray = array("日", "一", "二", "三", "四", "五", "六");
        $time = date('m月d日', $complain_info['time_created']) . ' 周' . $weekarray[date('w', $complain_info['time_created'])] . ' ' . date('H:i', $complain_info['time_created'])
                . '的投诉已处理完毕';

        $message = $nick . "家长，您于{$time}，感谢您帮助我们进步。\n"
                . "如有其他问题请联系我们的专属服务。\n\n"
                . "<a href='" . Yii::$app->params['sales_url'] . "'>我的专属服务</a>";


        return Queue::produce(
                        $this->buildMessage($message, $complain_info['open_id']), 'async', 'kefu_msg'
        );
    }

    public function sendUpdateOrderMessage($orderInfo)
    {
        $nick = $this->RStudentAccess->getNickByOpenId($orderInfo['open_id']);
        $message = $nick . "家长，您购买的课程套餐[{$orderInfo['pname']}]已修改了价格。\n"
                . '当前价格为：' . $orderInfo['price'] . "元\n\n"
                . "<a href='" . Yii::$app->params['base_url'] . "product/pay?pid=" . $orderInfo['pid'] . "&orderID=" . $orderInfo['orderNo'] . "'>点击立即支付</a>";


        return Queue::produce(
                        $this->buildMessage($message, $orderInfo['open_id']), 'async', 'kefu_msg'
        );
    }


    public function sendBindDistributeUserAccount($userId, $kefuId)
    {
        $open_id = $this->RStudentAccess->getOpenidByUid($userId);
        $nick = $this->RStudentAccess->getNickByOpenId($open_id);
        $kefu_info = $this->RAccountAccess->getUserAccountOne($kefuId);

        $message = $nick . "家长您好，我是您的班主任{$kefu_info['nickname']}老师。"
                . "我将衔接您孩子与陪练老师之间的良好沟通，让我们一起帮助孩子更好的成长！\n\n"
                . "<a href='" . Yii::$app->params['sales_url'] . "'>点击联系我的专属服务</a>";


        return Queue::produce(
                        $this->buildMessage($message, $open_id), 'async', 'kefu_msg'
        );
    }

//    public function sendFollowUser($open_id)
//    {
//        $time = strtotime('18:00');
//
//        $wechatname = $this->RChannelAccess->getSaleChannelTime($open_id, $time);
//        if(empty($wechatname)) {
//
//            $class_name = $this->RClassAccess->getWechatClassName($open_id);
//
//            if(empty($class_name)) {
//                $message = "哈喽{$wechatname}，你来啦，感谢您的关注，请问有什么可以帮您？\ue105";
//            } else {
//                $message = "亲看到您分享我们的【微课】提供的《$class_name》课程，顺便问一下您是否了解过线上陪练？";
//            }
//
//            return Queue::produce(
//                $this->buildMessage($message, $open_id),
//                'async',
//                'channel_msg'
//            );
//
//        }
//    }


    public function sendUserMessage($bindOpenid)
    {
        $start = strtotime('9:00');
        $end = strtotime('20:00');
        $time = time();

        if ($time >= $start && $time <= $end) {
            $message = "亲，您的提现请求我们已经收到，小伙伴正在查询中，请稍等...";
        } else {
            $message = "亲，我们的工作时间是9:00 - 20:00，您的提现请求我们已经收到，我们会尽快处理";
        }

        return Queue::produce(
                        $this->buildMessage($message, $bindOpenid), 'async', 'ckefu_msg'
        );
    }

    public function sendChannelRewardMessage($uid, $messageInfo, $historyId)
    {
        $open_id = $this->RChannelAccess->getChannelBindOpenid($uid);

//        $message = "$messageInfo\n\n"
//            . "<a href='" . Yii::$app->params['reward_info'] . $historyId . "'>查看本次奖励明细</a>";

        $message = "$messageInfo";

        return Queue::produce(
                        $this->buildMessage($message, $open_id), 'async', 'ckefu_msg'
        );
    }

    public function sendHisotryChannelRewardMessage($uid, $historyId)
    {
        $open_id = $this->RChannelAccess->getChannelBindOpenid($uid);

        $info = $this->RChannelAccess->getHistoryTradeTime($historyId);
        $money = $this->RChannelAccess->getTotaltalAmount($uid);

        $time = date('m月d日', $info['create_time']);

        $message = "亲爱的用户，" . $time . $info['total_amount'] . "元，累计提现" . $money . "元。\n\n"
                . "<a href='" . Yii::$app->params['reward_info'] . $historyId . "'>查看本次奖励明细</a>";

        return Queue::produce(
                        $this->buildMessage($message, $open_id), 'async', 'ckefu_msg'
        );
    }
    public function sendCancelexRecord($classId, $saleId)
    {
        //获取用户信息
        $userinfo = $this->RChannelAccess->getRowBySalesId($saleId);
        if ((time() - $userinfo['update_time']) > 2 * 24 * 60 * 60) {
            return ['error' => '不能发送客服消息', 'data' => ''];
        } else {
            //获取课程信息
            $classInfo = $this->RClassAccess->getClassInfo($classId);
            //获取课程记录信息
            $classRecord = $this->RClassAccess->queryViewclassData($classId);
            //获取学生信息
            $user = $this->RStudentAccess->getUserById($classInfo['student_id']);
            $message = "老师您好，您推荐的学生[" . $user['nick'] . "]，家长手机号:" . $user['mobile'] . ", 预约".date('Y年m月d日 H:i:s', $classInfo["time_class"])." 体验课取消<br>"
                    . "取消原因：  " . $classRecord["undo_reason"] . " <br>"
                    . "老师您可以记得在学生回课时推荐学生来免费体验哟~<br>";
//            Queue::produce(
//                    $this->buildMessage($message, $userinfo['bind_openid']), 'async', 'ckefu_msg'
//            );
            $data = array(
                'user_id' => $saleId,
                'message' => $message
            );
            return ['error' => 0, 'data' => $data];
        }
    }
    
    public function sendCancelexTemplet($classId, $saleId)
    {
        $userinfo = $this->RChannelAccess->getRowBySalesId($saleId);
        //获取课程信息
        $classInfo = $this->RClassAccess->getClassInfo($classId);
        //获取课程记录信息
        $classRecord = $this->RClassAccess->queryViewclassData($classId);
        //获取学生信息
        $user = $this->RStudentAccess->getUserById($classInfo['student_id']);
        $param = array(
            'template_id' => Yii::$app->params['channel_template_todo'],
            'firstValue' => '您推荐的陪练体验课取消',
            'key1word' => '体验课取消',
            'key2word' => $classRecord["undo_reason"],
            'key3word' => date('Y年m月d日 H:i:s', $classInfo["time_class"]),
            'remark' => '您邀请的用户['.$user['nick'].']取消体验课预约，请老师悉知。',
            'url' => '',
            'keyword_num' => 3
        );
        $message = TemplateBuilder::build($param, $userinfo['bind_openid']);
        $result = Queue::produce($message, 'template', 'channel_template');
        if (empty($result)) {
            return ['error' => '发送模板消息失败', 'data' => ''];
        } else {
            $data = array(
                'user_id' => $saleId,
                'message' => "您推荐的学员".$user['nick']."取消了体验课\n"."请老师悉知"
            );
            return ['error' => 0, 'data' => $data];
        }
    }

    public function sendClassRewardMessage($classId)
    {
        $classInfo = $this->RClassAccess->getClassInfo($classId);
        $classRecord = $this->RClassAccess->queryViewclassData($classId);
        $user = $this->RStudentAccess->getUserById($classInfo['student_id']);
        $open_id = $this->RChannelAccess->getChannelBindOpenid($user['sales_id']);
        if ($classInfo['is_ex_class'] == 1) {
            $message = "您的学生" . $user['nick'] . "刚刚完成了VIP陪练体验课程。您将获得一笔奖励，请在公众号输入'提现'领取您的奖励\n\n"
                    . "<a href='" . Yii::$app->params['record_path'] . $classRecord['id'] . "'>查看本次陪练课课单</a>";
        } else {
            if ($classInfo['time_end'] - $classInfo['time_class'] == 1500) {
                $type = '25分钟课程';
            } elseif ($classInfo['time_end'] - $classInfo['time_class'] == 3000) {
                $type = '50分钟课程';
            } elseif ($classInfo['time_end'] - $classInfo['time_class'] == 2700) {
                $type = '45分钟课程';
            } else {
                $type = '因为是测试号所以不准确';
            }
            $message = "您的学生" . $user['nick'] . '刚刚完成了VIP陪练' . $type . "。赶紧查看陪练课单\n\n"
                    . "<a href='" . Yii::$app->params['record_path'] . $classRecord['id'] . "'>查看课单</a>";
        }
        Queue::produce(
                $this->buildMessage($message, $open_id), 'async', 'ckefu_msg'
        );

        $data = array(
            'user_id' => $user['sales_id'],
            'message' => $message
        );
        return ['error' => 0, 'data' => $data];
    }

    public function sendWechatClassSubscribe($share, $openId)
    {
        if (!empty($share)) {
            //扫码关注
            if ($share[0]['is_back'] == 0) {
                $content = "感谢您关注VIP微课，我们为您提供最专业的在线音乐讲座\n\n点击查看"
                        . "<a href='" . Yii::$app->params['channel_base_url'] . "live/live-show?classid=" . $share[0]['id']
                        . "&stu=" . $share[0]['is_back']
                        . "'>【" . $share[0]['title']
                        . "】</a>直播课程";
            } else {
                $content = "感谢您关注VIP微课，我们为您提供最专业的在线音乐讲座\n\n点击查看"
                        . "<a href='" . Yii::$app->params['channel_base_url'] . "live/live-show?classid=" . $share[0]['id']
                        . "&stu=" . $share[0]['is_back']
                        . "'>【" . $share[0]['title']
                        . "】</a>课程回顾";
            }
        } else {
            //直接搜公众号关注
            $content = "感谢您关注VIP微课，我们为您提供最专业的在线音乐讲座\n\n点击查看"
                    . "<a href='" . Yii::$app->params['channel_base_url'] . "live/recently-live'>最近直播</a>列表";
        }
//        if(!empty($share)){
//            //扫码关注
//            if($share[0]['is_back'] == 0){
//                $content = "VIP微课&陪练——\n已合作数百位演奏家/音乐教育家提供高水准的免费讲座；拥有全职陪练老师千余名，已服务超过11个国家数万名主课老师的琴童，帮助主课老师的学生解决在家练琴的音符问题、节奏问题。\n\n点击查看"
//                    ."<a href='" . Yii::$app->params['channel_base_url'] . "live/live-show?classid=".$share[0]['id']
//                    ."&stu=".$share[0]['is_back']
//                    ."'>【".$share[0]['title']
//                    ."】</a>直播课程";
//            }else{
//                $content = "VIP微课&陪练——\n已合作数百位演奏家/音乐教育家提供高水准的免费讲座；拥有全职陪练老师千余名，已服务超过11个国家数万名主课老师的琴童，帮助主课老师的学生解决在家练琴的音符问题、节奏问题。\n\n点击查看"
//                    ."<a href='" . Yii::$app->params['channel_base_url'] . "live/live-show?classid=".$share[0]['id']
//                    ."&stu=".$share[0]['is_back']
//                    ."'>【".$share[0]['title']
//                    ."】</a>课程回顾";
//            }
//
//        }else{
//            //直接搜公众号关注
//            $content = "VIP微课&陪练——\n已合作数百位演奏家/音乐教育家提供高水准的免费讲座；拥有全职陪练老师千余名，已服务超过11个国家数万名主课老师的琴童，帮助主课老师的学生解决在家练琴的音符问题、节奏问题。\n\n点击查看"
//                ."<a href='" . Yii::$app->params['channel_base_url'] . "live/recently-live'>最近直播</a>列表";
//        }
        $message_info = '[系统提示：' . $content . ']';
        $this->WChannelChatAccess->doPassiveSaveChatMessage($openId, $message_info);
        return Queue::produce(
                        $this->buildMessage($content, $openId), 'async', 'ckefu_msg'
        );
    }

    public function sendChannelSubscribeDelayMessage($message)
    {
        array(
            'error' => 0,
            'data' => []
        );
        $content = '哈喽~感谢您关注VIP微课，VIP微课是由VIP陪练提供的。我们VIP陪练是一家提供真人1对1在线陪练服务的互联网公司。目前已经有来自11个国家的孩子选择了我们的服务，回复“1”即可加入我们哦~';
        $message_info = '[系统提示：' . $content . ']';
        $this->WChannelChatAccess->doPassiveSaveChatMessage($message['open_id'], $message_info);
        return Queue::produce(
                        $this->buildMessage($content, $message['open_id']), 'async', 'ckefu_msg'
        );

        /*
          $isTalk = $this->RChannelChatAccess->checkHaveTalk($message['open_id']);

          if (empty($isTalk))
          {
          $classId = $this->RChannelAccess->getClassShareByUid($message['uid']);

          if (empty($classId)) {
          $name = $this->RChannelAccess->getSalesChannelNameById($message['uid']);
          $content = '哈喽' . $name . ',你来啦,感谢您的关注,请问有什么可以帮您?';
          } else {
          $className = $this->RChannelAccess->getLiveClassNameById($classId);
          $content = '亲,看到您分享我们[VIP微课］提供的《' . $className . '》课程,顺便问一下您是否了解过线上陪练?';
          }

          return Queue::produce(
          $this->buildMessage($content, $message['open_id']),
          'async',
          'ckefu_msg'
          );
          }
         */
    }

    public function sendAutoAnswer($msg)
    {
        $auto_word = $msg["auto_word"];
        $xml = $msg["xml"];

        //发海报
        if ($auto_word["image_url"] != "") {
            //获取用户二维码
            $weicode = $this->RChannelAccess->getUserWeicode($xml["FromUserName"]);
            if (!empty($weicode["weicode_path"])) {
                $poster["open_id"] = $xml["FromUserName"];
                $poster["img_path"] = Yii::$app->params['vip_static_path'] . $auto_word["image_url"];
                $poster["weicode"] = Yii::$app->params['pnl_static_path'] . $weicode["weicode_path"];
                Queue::produce($poster, 'async', 'channel_poster');
            }
        }

        if ($auto_word["article_one"] != "") {
            $article_one_word = $auto_word["article_one"];
            $article_one = array(
                'touser' => (string) $xml['FromUserName'],
                'msgtype' => 'text',
                'text' => array('content' => $article_one_word));
            Queue::produce($article_one, 'async', 'ckefu_msg');
            $message = '[系统提示：' . $article_one_word . ']';
            $this->WChannelChatAccess->doPassiveSaveChatMessage($xml['FromUserName'], $message);
        }

        if ($auto_word["article_two"] != "") {
            $article_two_word = $auto_word["article_two"];
            $article_two = array(
                'touser' => (string) $xml['FromUserName'],
                'msgtype' => 'text',
                'text' => array('content' => $article_two_word));
            Queue::produce($article_two, 'async', 'ckefu_msg');

            $message = '[系统提示：' . $article_two_word . ']';
            $this->WChannelChatAccess->doPassiveSaveChatMessage($xml['FromUserName'], $message);
        }
    }

    public function sendStudentUnregisteredDelayMessage($message)
    {
        $open_id = $message['ToUserName'];
//      $user_wechat_nick = $this->RStudentAccess->getUserName($open_id);
        $this->RStudentAccess->getUserName($open_id);
        $user_info = $this->RStudentAccess->getWechatRowByOpenId($open_id);

        if (empty($user_info)) {
            /*
              $content =  "您好". $user_wechat_nick ."，VIP陪练成立以来，已经服务了超过11个国家，3万多名小朋友，希望我们可以成为小朋友练琴时最高效的陪伴。 \n"
              . "点击立即领取价值100元的免费体验课，我们将用心为您服务 \n"
              . "<a href='" . Yii::$app->params['free_class_url'] . "'>点击立即领取</a>";
             */
            $content = "感谢您关注VIP陪练，我们已服务超过11个国家的数万名琴童，著名滴滴投资人、金沙江创投合伙人朱啸虎的孩子、姚明的孩子也在用了，你还不来试试?\n\n"
                    . "<a href='" . Yii::$app->params['free_class_url'] . "'>点击立即领取价值100元的免费体验课，我们将用心为您服务</a>";
            Queue::produce(
                    $this->buildMessage($content, $open_id), 'async', 'ckefu_msg'
            );
        }
        return;
    }

    public function sendPnlFifteenMinuteNotWechatclass($openId)
    {
        $count = ClassRoom::find()
                ->alias('c')
                ->leftJoin('wechat_acc AS w', 'w.uid = c.student_id')
                ->where('is_ex_class = 1 AND (c.status = 1 OR c.status = 0) AND w.openid = :openid', [
                    ':openid' => $openId
                ])
                ->count();
        $uid = WechatAcc::find()
                ->select('uid')
                ->where(['openid' => $openId])
                ->scalar();

        if (!empty($uid)) {
            $nick = User::find()
                    ->select('nick')
                    ->where(['id' => $uid])
                    ->scalar();
            $name = $nick . '家长，';
        } else {
            $name = '家长您好，';
        }

        if (empty($count)) {
            $wechat = Yii::$app->wechat;
            $content = $name . "感谢您关注VIP陪练，我们已服务超过11个国家的数万名琴童，著名滴滴投资人、金沙江创投合伙人朱啸虎的孩子、姚明的孩子也在用了，你还不来试试?\n\n<a href='" . Yii::$app->params['base_url'] . "student/intro'>点击立即领取价值100元的免费体验课，我们将用心为您服务</a>";
            //文本消息
            $data = [
                'touser' => $openId,
                'msgtype' => 'text',
                'text' => ['content' => $content]
            ];
            $wechat->sendMessage($data);
        }
    }
}
