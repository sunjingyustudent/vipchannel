<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/3/23
 * Time: 上午10:18
 */
namespace common\logics\push;

use common\services\ErrorService;
use Yii;
use yii\base\Object;
use common\models\music\StudentUserShare;
use common\models\music\StudentWechatClass;


class PosterLogic extends Object implements IPoster
{
    /** @var  \common\sources\write\channel\ChannelAccess  $WChannelAccess */
    private $WChannelAccess;
     /** @var  \common\sources\read\classes\ClassAccess  $RClassAccess */
    private $RClassAccess;
     /** @var  \common\sources\write\classes\ClassAccess  $WClassAccess */
    private $WClassAccess;

    public function init()
    {
        $this->WChannelAccess = Yii::$container->get('WChannelAccess');
        $this->RClassAccess = Yii::$container->get('RClassAccess');
        $this->WClassAccess = Yii::$container->get('WClassAccess');
    }
    public function sendPosterChannel($msg)
    {
        $openId = $msg['open_id'];
        $imgPath = $msg['img_path'];
        $weicode = $msg['weicode'];

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

        $wechat = Yii::$app->wechat_new;

        $result = $wechat->uploadMedia($jpgName, 'image');

        if (isset($result['media_id'])) {

            $data = [
                'touser' => $openId,
                'msgtype' => 'image',
                'image' => ['media_id' => $result['media_id']]
            ];

            $wechat->sendMessage($data);
        }

        unlink($jpgName);

    }

    public function sendPosterStudent($msg)
    {
        $openId = $msg['open_id'];
        //非外网地址，使用tmp目录文件
        $imgPath = strpos($msg['img_path'], 'http') === false ? Yii::$app->params['root'] .'/tmp/'. $msg['img_path'] : $msg['img_path'];
        $weicode = $msg['weicode'];

        $jpgName = Yii::$app->params['root'] . '/tmp/' . uniqid() . '.jpg';

        if (!empty($weicode))
        {
            $qrcodeImage = imagecreatefromjpeg($weicode);
            $qrcodeImageResized = imagecreate(190, 190);
            imagecopyresampled($qrcodeImageResized, $qrcodeImage, 0, 0, 0, 0, 190, 190, 430, 430);
            $posterImage = imagecreatefromjpeg($imgPath);
            imagecopy($posterImage, $qrcodeImageResized, 423, 833, 0, 0, 190, 190);
            imagejpeg($posterImage, $jpgName, 100);
        }

        $wechat = Yii::$app->wechat;

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

    public function sendPosterChannelWeek($msg)
    {
        //将图片储存在本地
        $jpgName = Yii::$app->params['root'] . '/tmp/' . uniqid() . '.jpg';
        //客户二维码路径，并将二维码与图片拼接
        $qrcodeImage = imagecreatefromjpeg($msg['weicode_path']);
        $qrcodeImageResized = imagecreate(160, 160);
        imagecopyresampled($qrcodeImageResized, $qrcodeImage, 0, 0, 0, 0, 160, 160, 430, 430);
        $posterImage = imagecreatefromjpeg($msg['poster_path']);
        imagecopy($posterImage, $qrcodeImageResized, 460, 860, 0, 0, 160, 160);
        imagejpeg($posterImage, $jpgName, 100);
        //将图片上传到微信（每天最多5000个多媒体文件），返回media_id
        $wechat = Yii::$app->wechat_new;
        $result = $wechat->uploadMedia($jpgName, 'image');

        if (isset($result['media_id']))
        {
            //海报消息 同一个客户收到3个
            $data  = [
                'touser' => $msg['touser'],
                'msgtype' => 'image',
                'image' => ['media_id' => $result['media_id']]
            ];
            //文本消息
            $da2ta = [
                'touser' => $msg['touser'],
                'msgtype' => 'text',
                'text' => ['content' => $msg['content']]
            ];
            //文本消息
            $da3ta = [
                'touser' => $msg['touser'],
                'msgtype' => 'text',
                'text' => ['content' => $msg['title']]
            ];

            $flag_1 = $wechat->sendMessage($data);
            $flag_2 = $wechat->sendMessage($da2ta);
            $flag_3 = $wechat->sendMessage($da3ta);
        }

        $this->WChannelAccess->savePosterPushStatistic($msg);
        unlink($jpgName);
        usleep(rand(0,10)*1000);//微秒数  一百万分之一秒

    }
    
    public function sendPosterToStudent($msg)
    {
        $wechat = Yii::$app->wechat;
        $class_id = $msg["class_id"];
        $openid =$msg["openid"];
        //$openid = (string)$openid;
        //获取课程信息
         $data =$this-> RClassAccess->getStudentWechatClass($class_id);
//         $this->WClassAccess->addStudentUserShare($class_id,$openid,$data["is_back"],$data["is_free"]);
         $ishave = $this-> RClassAccess->getShareRecord($data['id'],$data['is_free'],$openid,$data['is_back']);

        //发送文案和海报
        $base = 2147483648;
        $scene_id = $base + $ishave["id"];

        //生成二维码
        $qrcode = [
            'expire_seconds' => 604800,
            'action_name' => 'QR_SCENE',
            'action_info' => [
                'scene' => [
                    'scene_id' => $scene_id
                ],
            ],
        ];
        $tickect = $wechat->createQrCode($qrcode);
        $imgUrl = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . $tickect['ticket'];
        
        if(!$tickect)
        {
            return true;
        }
//        if(empty($ishave['head']))
//        {
//            return true;
//        }
        if(empty($ishave["name"]))
        {
            return true;
        }

        $jpgName = Yii::$app->params['root'] . '/tmp/' . uniqid() . '.jpg';
        $template = new \Imagick(Yii::$app->params['vip_static_path'] . $data["poster_path"]);

        //客户二维码路径，并将二维码与图片拼接
        /*
        $qrcodeImage = imagecreatefromjpeg($imgUrl);
        $qrcodeImageResized = imagecreate(180, 180);
        imagecopyresampled($qrcodeImageResized, $qrcodeImage, 0, 0, 0, 0, 180, 180, 430, 430);
        $posterImage = imagecreatefromjpeg($template);
        imagecopy($posterImage, $qrcodeImageResized, 430, 788, 0, 0, 180, 180);

        $headIcon = imagecreatefromjpeg($ishave['head']);
        $qrcodeImageResized = imagecreate(70, 70);
        imagecopy($posterImage, $qrcodeImageResized, 30, 114, 0, 0, 70, 70);

        imagefttext($posterImage, 33, 0, 120, 860, '#fff', 'simhei.ttf', $ishave['name']);
        imagejpeg($posterImage, $jpgName, 100);
*/


        //把获取到的临时二维码创建成一张jpeg格式的图片
        $qrcodeImage = new \Imagick($imgUrl);
        $qrcodeImage->thumbnailImage(180, 180, false, true);
        $template->compositeImage($qrcodeImage, \Imagick::COMPOSITE_OVER, 430, 788);

        //合成头像到指定位置
//        $headIcon = new \Imagick('vip_logo.jpg');
//        $headIcon->thumbnailImage(70, 70, false, true);
//        $template->compositeImage($headIcon, \Imagick::COMPOSITE_OVER, 30, 114);

        //合成用户的微信名
        $draw = new \ImagickDraw();
        $draw->setgravity(\imagick::GRAVITY_SOUTHWEST);
        $draw->setFont('simhei.ttf');
        $draw->setFontSize(33);  //字体大小
        $draw->setFillColor("#fff"); // 字体颜色
        $draw->setTextEncoding('UTF-8');
        $template->annotateImage($draw, 120, 860, 0, $ishave["name"]);
        $draw->setFontSize(20);
        $template->annotateImage($draw, 120, 825, 0, '邀请您来听课啦~');
        $template->writeImage($jpgName);

        $result = $wechat->uploadMedia($jpgName, 'image');
        if (isset($result['media_id'])) {
            $dataposter = [
                'touser' => (string)$openid,
                'msgtype' => 'image',
                'image' => ['media_id' => $result['media_id']]
            ];
            $message1text ="您预约的大师微课《".$data["title"]."》，将于".date('Y-m-d H:i:s',$data["class_time"])."开始，课程开始前十五分钟我们会将地址推送给您";
            $message1 = [
                'touser' =>(string) $openid,
                'msgtype' => 'text',
                'text' => ['content' =>$message1text]
            ];
            $message2text = "Hi~这是您的邀请卡，保存后分享到朋友圈，欢迎邀请更多的小伙伴来听课呦~";
            $message2 = [
                'touser' => (string)$openid,
                'msgtype' => 'text',
                'text' => ['content' =>$message2text]
            ];
            $wechat->sendMessage($dataposter);
            $wechat->sendMessage($message1);
            $wechat->sendMessage($message2);
        }

         unlink($jpgName);
    }
    
}