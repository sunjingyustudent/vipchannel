<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/16
 * Time: 下午6:20
 */
namespace common\logics\wechat;

use Yii;
use yii\base\Object;
use yii\db\Exception;

class ChannelWechatLogic extends Object implements IChannelWechat
{

    /** @var  \common\sources\read\channel\ChannelAccess   $RChannelAccess*/
    private $RChannelAccess;
    /** @var  \common\sources\write\channel\ChannelAccess  $WChannelAccess */
    private $WChannelAccess;
    
    public function init()
    {
        $this->RChannelAccess = Yii::$container->get('RChannelAccess');
        $this->WChannelAccess = Yii::$container->get('WChannelAccess');
    }
    
    public function dealPoster($msg)
    {
        switch ($msg['EventKey'])
        {
            case Yii::$app->params['poster_teacher_key'] :
                $this->dealTeacherPoster($msg['FromUserName']);
                break;

            case Yii::$app->params['channel_key_recommend'] :
                $this->dealStudentPoster($msg['FromUserName']);
                break;

            case Yii::$app->params['poster_speech_key'] :
                $this->dealSpeechPoster($msg);
                break;

            case Yii::$app->params['poster_invivation_key'] :
                $this->dealInvitationPoster($msg);
                break;
        }
    }

    private function dealTeacherPoster($openId)
    {
        $weicode = $this->RChannelAccess->getChannelWeicodeByOpenid($openId);

        $poster = Yii::$app->params['root'] . '/tmp/WechatIMG57.jpeg';

        if(!empty($weicode))
        {
            $jpgName = Yii::$app->params['root'] . '/tmp/' . uniqid() . '.jpg';
            $qrcodeImage = imagecreatefromjpeg('http://static.pnlyy.com/' . $weicode);
            $qrcodeImageResized = imagecreate(140, 140);
            imagecopyresampled($qrcodeImageResized, $qrcodeImage, 0, 0, 0, 0, 140, 140, 400, 400);
            $posterImage = imagecreatefromjpeg($poster);
            imagecopy($posterImage, $qrcodeImageResized, 465, 865, 0, 0, 140, 140);
            imagejpeg($posterImage, $jpgName, 100);

            $wechat = Yii::$app->wechat_new;

            $result = $wechat->uploadMedia($jpgName, 'image');

            if (isset($result['media_id'])) 
            {
                $data = [
                    'touser' => $openId,
                    'msgtype' => 'image',
                    'image' => ['media_id' => $result['media_id']]
                ];

                $content = '老师您好，请将此海报分享给您的其他老师朋友，将获得额外50%奖励';

                $da2ta = [
                    'touser' => $openId,
                    'msgtype' => 'text',
                    'text' => ['content' => $content]
                ];

                $wechat->sendMessage($data);
                $wechat->sendMessage($da2ta);
            }

            unlink($jpgName);
        }
    }

    private function dealStudentPoster($openId)
    {
        $weicode = $this->RChannelAccess->getStudentWeicodeByOpenid($openId);

        $posterInfo = $this->RChannelAccess->getLastPictureInfo();

        if (!empty($weicode)) {
            $jpgName = Yii::$app->params['root'] . '/tmp/' . uniqid() . '.jpg';
            $qrcodeImage = imagecreatefromjpeg(Yii::$app->params['pnl_static_path'] . $weicode);
            $qrcodeImageResized = imagecreate(160, 160);
            imagecopyresampled($qrcodeImageResized, $qrcodeImage, 0, 0, 0, 0, 160, 160, 430, 430);
            $posterImage = imagecreatefromjpeg(Yii::$app->params['vip_static_path'] . $posterInfo['path']);
            imagecopy($posterImage, $qrcodeImageResized, 460, 860, 0, 0, 160, 160);
            imagejpeg($posterImage, $jpgName, 100);

            $wechat = Yii::$app->wechat_new;

            $result = $wechat->uploadMedia($jpgName, 'image');

            if (isset($result['media_id']))
            {

                $data = [
                    'touser' => $openId,
                    'msgtype' => 'image',
                    'image' => ['media_id' => $result['media_id']]
                ];

                $da2ta = [
                    'touser' => $openId,
                    'msgtype' => 'text',
                    'text' => ['content' => $posterInfo['content']]
                ];

                $da3ta = [
                    'touser' => $openId,
                    'msgtype' => 'text',
                    'text' => ['content' => $posterInfo['title']]
                ];

                $wechat->sendMessage($data);
                $wechat->sendMessage($da2ta);
                $wechat->sendMessage($da3ta);
            }

            unlink($jpgName);
        }
    }

    private function dealSpeechPoster($xml)
    {
        $wechat = Yii::$app->wechat_new;

        $content = "快来和我一起参加VIP陪练的音乐大师讲座吧！讲座时间及内容将会在VIP陪练推广大使中进行通知历次讲座邀请冯页、宋思衡、唐瑾、崔岚等著名钢琴家进行讲座请大家关注公众号以免遗漏精彩讲座";

        $data = [
            'touser' => $xml['FromUserName'],
            'msgtype' => 'text',
            'text' => ['content' => $content]
        ];

        $wechat->sendMessage($data);

        $da2ta = array(
            'touser' => $xml['FromUserName'],
            'msgtype' => 'image',
            'image' => ['media_id' => Yii::$app->params['speech_poster_id']]
        );
        
        $wechat->sendMessage($da2ta);
    }

    private function dealInvitationPoster($xml)
    {
        $weicode = $this->RChannelAccess->getChannelWeicodeByOpenid($openId);
        
        $poster = Yii::$app->params['root'] . '/tmp/yellow.jpg';

        if (!empty($weicode))
        {
            $jpgName = Yii::$app->params['root'] . '/tmp/' . uniqid() . '.jpg';
            $qrcodeImage = imagecreatefromjpeg('http://static.pnlyy.com/' . $weicode);
            $qrcodeImageResized = imagecreate(163, 163);
            imagecopyresampled($qrcodeImageResized, $qrcodeImage, 0, 0, 0, 0, 163, 163, 430, 430);
            $posterImage = imagecreatefromjpeg($poster);
            imagecopy($posterImage, $qrcodeImageResized, 87, 766, 0, 0, 163, 163);
            imagejpeg($posterImage, $jpgName, 100);

            $wechat = Yii::$app->wechat_new;

            $result = $wechat->uploadMedia($jpgName, 'image');

            if (isset($result['media_id']))
            {

                $data = [
                    'touser' => $openId,
                    'msgtype' => 'image',
                    'image' => ['media_id' => $result['media_id']]
                ];
                
                $wechat->sendMessage($data);
               
            }

            unlink($jpgName);
        }
    }

   public function dealPosterPush($arr){
       //将图片储存在本地
       $jpgName = Yii::$app->params['root'] . '/tmp/' . uniqid() . '.jpg';
       //客户二维码路径，并将二维码与图片拼接
       $qrcodeImage = imagecreatefromjpeg(Yii::$app->params['pnl_static_path'] . $arr['weicode_path']);
       $qrcodeImageResized = imagecreate(160, 160);
       imagecopyresampled($qrcodeImageResized, $qrcodeImage, 0, 0, 0, 0, 160, 160, 430, 430);
       $posterImage = imagecreatefromjpeg(Yii::$app->params['vip_static_path'].$arr['poster_path']);
       imagecopy($posterImage, $qrcodeImageResized, 460, 860, 0, 0, 160, 160);
       imagejpeg($posterImage, $jpgName, 100);
       //将图片上传到微信（每天最多5000个多媒体文件），返回media_id
       $wechat = Yii::$app->wechat_new;
       $result = $wechat->uploadMedia($jpgName, 'image');

       if (isset($result['media_id']))
       {
           //海报消息 同一个客户收到3个
           $data  = [
               'touser' => $arr['bind_openid'],
               'msgtype' => 'image',
               'image' => ['media_id' => $result['media_id']]
           ];
           //文本消息
           $da2ta = [
               'touser' => $arr['bind_openid'],
               'msgtype' => 'text',
               'text' => ['content' => $arr['content']]
           ];
           //文本消息
           $da3ta = [
               'touser' => $arr['bind_openid'],
               'msgtype' => 'text',
               'text' => ['content' => $arr['title']]
           ];

           $wechat->sendMessage($data);
           $wechat->sendMessage($da2ta);
           $wechat->sendMessage($da3ta);
       }

       $this->WChannelAccess->savePosterPushStatistic($arr);
       unlink($jpgName);
       sleep(rand(0,100)*1000);//微秒数  一百万分之一秒
   }

   public function getTemporaryQrcode($params=array())
   { 
       $wechat = Yii::$app->wechat_new;
       $result = $wechat->createQrCode($params);
       if(isset($result['ticket'])){
          return $wechat->getQrCodeUrl($result['ticket']);
       }
       return false;
   }
}