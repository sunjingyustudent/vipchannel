<?php

/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/3/23
 * Time: 上午10:19
 */

namespace common\logics\push;

use common\services\ErrorService;
use common\widgets\RedPack;
use Yii;
use common\widgets\Request;
use yii\base\Object;
use common\models\music\RedactiveRecord;
use common\services\QiniuService;
use common\models\music\SalesChannel;
use common\models\music\UserPoster;

use common\widgets\Queue;

class RedpackLogic extends Object implements IRedpack {

    /** @var  \common\logics\chat\ChannelChatLogic $channelChatService */
    private $channelChatService;

    /** @var  \common\sources\read\channel\ChannelAccess  $RChannelAccess */
    private $RChannelAccess;

    /** @var  \common\sources\write\channel\ChannelAccess  $WChannelAccess */
    private $WChannelAccess;
    
    /** @var  \common\sources\write\chat\ChannelChatAccess  $WChannelChatAccess */
    private $WChannelChatAccess;

    /** @var  \common\sources\read\student\StudentAccess  $RStudentAccess */
    private $RStudentAccess;

    public function init() {
        $this->channelChatService = Yii::$container->get('channelChatService');
        $this->RChannelAccess = Yii::$container->get('RChannelAccess');
        $this->WChannelAccess = Yii::$container->get('WChannelAccess');
        $this->WChannelChatAccess = Yii::$container->get('WChannelChatAccess');
        $this->RStudentAccess = Yii::$container->get('RStudentAccess');
        parent::init();
    }

    public function sendRedpack($data) {
        $res = RedPack::send($data);

        if ($res['error'] !== 0) {
            
        }
    }

    public function sendChannelActRedpack($msg) {
        $ishave = $msg["ishave"];
        $xml = $msg["xml"];

        $red_top_flag = false;
        $user_re_flag = false;
        $get_money = 0;
        //判断发送红包是否达到限额
        $red_top = $this->RChannelAccess->getRedpackageActiveSumById($ishave["id"]);
        if ($red_top >= $ishave["redpackage_money"]) {
            $red_top_flag = true;
        } else {
            //判断当前用户有没有领取过红包
            $re_user = $this->RChannelAccess->isReUser($xml["FromUserName"], $ishave["id"]);
            if (!empty($re_user)) {
                $user_re_flag = true;
            } else {
                $get_money = rand($ishave["redpackage_bottom"], $ishave["redpackage_top"]);
                if (($get_money + $red_top) > $ishave["redpackage_money"]) {
                    $red_top_flag = true;
                    $get_money = 0;
                }
            }
        }
            $redlistinfo["active_id"] = $ishave["id"];
            $redlistinfo["open_id"] = $xml['FromUserName'];
            $redlistinfo["money"] = $get_money;
            $redlistinfo["createtime"] = time();
        //如果红包达到限额，发送抱歉话术
        if ($red_top_flag) {
            $red_top_content = $ishave["no_redpackage_word"];
            $red_top_msg = array(
                'touser' => (string) $xml['FromUserName'],
                'msgtype' => 'text',
                'text' => array('content' => $red_top_content));
            Queue::produce($red_top_msg, 'async', 'ckefu_msg');

            $redlistinfo["is_success"] = 0;
            $redlistinfo["error_log"] = -1;
            $this->WChannelAccess->addRedpackageRecord($redlistinfo);
            $message = '[系统提示：'.$red_top_content.']';
            $this->WChannelChatAccess->doPassiveSaveChatMessage($xml['FromUserName'],$message);
        }
        //如果当前用户已经领过红包，发送提醒话术
        if ($user_re_flag) {
            $user_re_content = "对不起您已领取过红包！请不要重复领取~";
            $user_re_msg = array(
                'touser' => (string) $xml['FromUserName'],
                'msgtype' => 'text',
                'text' => array('content' => $user_re_content));
            Queue::produce($user_re_msg, 'async', 'ckefu_msg');

            $redlistinfo["is_success"] = 0;
            $redlistinfo["error_log"] = -2;
            $this->WChannelAccess->addRedpackageRecord($redlistinfo);

            $message = '[系统提示：'.$user_re_content.']';
            $this->WChannelChatAccess->doPassiveSaveChatMessage($xml['FromUserName'],$message);
        }
        //发红包
        if ($get_money != 0) {
            $redinfo = array(
                'open_id' => $xml['FromUserName'],
                'mch_id' => Yii::$app->params['sales_mch_id'],
                'wxappid' => Yii::$app->params['sales_app_id'],
                'wechat_mch_secret' => Yii::$app->params['sales_mch_secret'],
                'send_name' => empty($ishave["red_wishing"]) ? "大吉大利" : $ishave["red_wishing"],
                'total_amount' => intval($get_money) * 100,
                'total_num' => 1,
                'wishing' => "VIP微课",
                'act_name' => $ishave["active_title"],
                'remark' => '妙克信息科技',
                'scene_id' => 'PRODUCT_5',
                'pem_root' => Yii::$app->params['sales_pem_root'],
                'client_ip' => $msg['client_ip'],
            );
           $result_red = RedPack::send($redinfo);

            if ($result_red['error'] === 0) {
                $redlistinfo["is_success"] = 1;
                $redlistinfo["error_log"] = 0;
            } else {
                //  记录红包发送失败原因
                $redlistinfo["money"] = 0;
                $redlistinfo["is_success"] = 0;
                $redlistinfo["error_log"] = $result_red["error"];
            }
            $this->WChannelAccess->addRedpackageRecord($redlistinfo);
        }
        //发海报
        if ($ishave["image_url"] != "") {
            //获取是否有提前生成的海报
           $is_have_poster = UserPoster::find()
                            ->select('poster')
                            ->where('active_id = :active_id and openid = :openid',[
                                ':active_id'=>$ishave["id"],
                                ':openid'=>$xml['FromUserName']
                            ])
                            ->asArray()
               ->one();
            if(!empty($is_have_poster["poster"]))
            {
                $wechat = Yii::$app->wechat_new;
                $data = [
                    'touser' => $xml['FromUserName'],
                    'msgtype' => 'image',
                    'image' => ['media_id' => $is_have_poster["poster"]]
                ];
                $wechat->sendMessage($data);

            }else{

                //获取用户二维码
                $weicode = $this->RChannelAccess->getUserWeicode($xml["FromUserName"]);
                if(!empty($weicode["weicode_path"]))
                {
                    $poster["open_id"] = $xml["FromUserName"];
                    $poster["img_path"] = Yii::$app->params['vip_static_path'] . $ishave["image_url"];
                    $poster["weicode"] = Yii::$app->params['pnl_static_path'] . $weicode["weicode_path"];
                    Queue::produce($poster, 'async', 'channel_poster');
                }
            }
        }

        if ($ishave["article_one"] != "") {
            $article_one_word = $ishave["article_one"];
            $article_one = array(
                'touser' => (string) $xml['FromUserName'],
                'msgtype' => 'text',
                'text' => array('content' => $article_one_word));
            Queue::produce($article_one, 'async', 'ckefu_msg');

            $message = '[系统提示：'.$article_one_word.']';
            $this->WChannelChatAccess->doPassiveSaveChatMessage($xml['FromUserName'],$message);
        }

        if ($ishave["article_two"] != "") {
            $article_two_word = $ishave["article_two"];
            $article_two = array(
                'touser' => (string) $xml['FromUserName'],
                'msgtype' => 'text',
                'text' => array('content' => $article_two_word));
            Queue::produce($article_two, 'async', 'ckefu_msg');

            
            $message = '[系统提示：'.$article_two_word.']';
            $this->WChannelChatAccess->doPassiveSaveChatMessage($xml['FromUserName'],$message);
        }
    }
    
    /*
     * crate by sjy 2017-04-24
     * 生成用户海报
     */
    public function createPoster($msg)
    {
         $image_url = $msg["image_url"];
         $openid = $msg["openid"];
         $active_id = $msg["id"];
         if ($image_url != "") {
            //获取用户二维码
            $weicode = $this->RChannelAccess->getUserWeicode($openid);
           
            if(!empty($weicode["weicode_path"]))
            {
            $openId = $openid;
            $imgPath = Yii::$app->params['vip_static_path'] . $image_url;
            $weicode = Yii::$app->params['pnl_static_path'] . $weicode["weicode_path"];

            $jpgName = Yii::$app->params['root'] . '/tmp/' . uniqid() . '.jpg';

            if (!empty($weicode))
            {
                $qrcodeImage = imagecreatefromjpeg($weicode);
                $qrcodeImageResized = imagecreate(160, 160);
                imagecopyresampled($qrcodeImageResized, $qrcodeImage, 0, 0, 0, 0, 160, 160, 430, 430);
                $posterImage = imagecreatefromjpeg($imgPath);
                imagecopy($posterImage, $qrcodeImageResized, 460, 860, 0, 0, 160, 160);
                imagejpeg($posterImage, $jpgName, 100);
            }

            //把图片上传到七牛
                $wechat = Yii::$app->wechat_new;
                $result = $wechat->uploadMedia($jpgName, 'image');

//            $bucket = Yii::$app->params['vip_static_bucket'];
//            $filePathTo = 'pushposter/' . md5( microtime() . '_' . rand(10, 99));
//            $filePathFrom = $jpgName;
//            $flag = QiniuService::uploadToQiniu($bucket,$filePathTo,$filePathFrom);
            //如果上传成功，保存到数据库
            if (isset($result['media_id']))
            {
                //判断用户海报是否存在
                $is_have = UserPoster::find()
                        ->where('openid = :openid and active_id = :active_id',[
                            ':openid'=>$openid,
                            ':active_id'=>$active_id
                        ])
                        ->one();
                
                if(empty($is_have))
                {
                    $is_have = new UserPoster();
                    $is_have->openid = $openid;
                    $is_have->active_id = $active_id;
                    $is_have->poster = $result['media_id'];
                    $is_have->save();
                }else{
                    $is_have->poster = $result['media_id'];
                    $is_have->update();
                }
            }
            unlink($jpgName);
            
            }
        }
    }

    /**
     * 用户推荐进来的人，首次的体验课完成：奖励红包
     */
    public function giveChannelRedPack($message)
    {
        $openid = '';
        $user_rs = $this->RStudentAccess->getChannelIdsByStudentId($message['studentid']);
        if($user_rs) {
            $recommenduserid = $this->RStudentAccess->getUserIdByChannelIdSelf($user_rs['channel_id']);
            $RChannelAccess_rs = $this->RChannelAccess->getUserChannelInfoById($user_rs['channel_id']);
            if($recommenduserid) {
                $openid = $this->RStudentAccess->getOpenIdByStudentId($recommenduserid);
            }
        }

        if($RChannelAccess_rs['type'] == 2) {
            $redinfo = array (
                'open_id' => $openid,
                'mch_id' => Yii::$app->params['wechat_mch_id'],
                'wxappid' => Yii::$app->params['wechat_app_id'],
                'wechat_mch_secret' => Yii::$app->params['wechat_mch_secret'],
                'send_name' => 'VIP陪练',
                'total_amount' => intval(18) * 100,
                'total_num' => 1,
                'wishing' => '恭喜，感谢您的支持',
                'act_name' => '推广奖励',
                'remark' => '妙克信息科技',
                'scene_id' => 'PRODUCT_5',
                'pem_root' => Yii::$app->params['pem_root'],
                'client_ip' => $message['client_ip']
            );
            $result_red = RedPack::send($redinfo);

            if ($result_red['error'] === 0) {
                $redlistinfo["is_success"] = 1;
                $redlistinfo["error_log"] = 0;
            } else {
                //  记录红包发送失败原因
                $redlistinfo["money"] = 0;
                $redlistinfo["is_success"] = 0;
                $redlistinfo["error_log"] = $result_red["error"];
            }
            $redlistinfo['classId'] = $message['classId'];
            $redlistinfo['openid'] = $openid;
            $redlistinfo['money'] = 18;
            $redlistinfo['createtime'] = time();
            $this->WChannelAccess->addRedpackChannel($redlistinfo);
            return array('error' => 0, 'data' => array('open_id' => $openid));
        }
    }

}
