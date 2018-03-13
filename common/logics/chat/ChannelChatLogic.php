<?php

/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/3/3
 * Time: 上午10:36
 */

namespace common\logics\chat;

use common\services\LogService;
use common\services\QiniuService;
use common\widgets\RedPack;
use common\widgets\Request;
use Yii;
use yii\base\Exception;
use yii\base\Object;
use callmez\wechat\sdk;
use common\widgets\Json;
use yii\data\Pagination;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Qiniu\Storage\BucketManager;
use yii\helpers\VarDumper;

class ChannelChatLogic extends Object implements IChannelChat
{

    /** @var  \common\sources\read\channel\ChannelAccess  $RChannelAccess */
    private $RChannelAccess;

    /** @var  \common\sources\read\chat\ChannelChatAccess  $RChannelChatAccess */
    private $RChannelChatAccess;

    /** @var  \common\sources\write\chat\ChannelChatAccess  $WChannelChatAccess */
    private $WChannelChatAccess;

    /** @var  \common\sources\read\account\AccountAccess  $RAccountAccess */
    private $RAccountAccess;

    /** @var  \common\sources\read\student\StudentAccess  $RStudentAccess */
    private $RStudentAccess;

    /** @var  \common\sources\write\channel\ChannelAccess  $WChannelAccess */
    private $WChannelAccess;
    private $userId;

    public function init()
    {
        $this->RChannelChatAccess = Yii::$container->get('RChannelChatAccess');
        $this->WChannelChatAccess = Yii::$container->get('WChannelChatAccess');
        $this->RChannelAccess = Yii::$container->get('RChannelAccess');
        $this->RAccountAccess = Yii::$container->get('RAccountAccess');
        $this->RStudentAccess = Yii::$container->get('RStudentAccess');
        $this->WChannelAccess = Yii::$container->get('WChannelAccess');
        $this->userId = Yii::$app->user->identity->id;
        parent::init();
    }

    public function dealChatImage($xml)
    {
        $returnData = array(
            'error' => 0,
            'data' => []
        );

        if ($xml['MediaId'] == '') {
            $returnData['error'] = 'mediaid为空';
            return $returnData;
        }

        $bucket = Yii::$app->params['vip_static_bucket'];

        $img = @file_get_contents($xml['PicUrl']);

        if (!$img) {
            $returnData['error'] = '获取图片失败';
            return $returnData;
        }

        $filePathFrom = '/tmp/' . $xml['MediaId'] . '.jpeg';
        $filePathTo = 'chat/image/' . md5($xml['MsgId'] . '_' . microtime() . '_' . rand(10, 99));
        file_put_contents($filePathFrom, $img);

        if (!QiniuService::uploadToQiniu($bucket, $filePathTo, $filePathFrom)) {
            $returnData['error'] = '上传七牛失败';
            return $returnData;
        }

        $counts = $this->RChannelAccess->countChannelByOpenid($xml['FromUserName']);

        if (!empty($counts)) {
            $this->WChannelChatAccess->addChatMessagePre($xml['FromUserName'], $filePathTo, 2);
        }

        unlink($filePathFrom);
        return $returnData;
        parent::init();
    }

    public function getUserCount($type)
    {
        $count = $this->RChannelChatAccess->getUserCount($type);

        $data = array(
            'count' => $count
        );

        return ['error' => 0, 'data' => $data];
    }

    public function getUserList($type, $num)
    {
        $type = intval($type);
        $num = intval($num);
        $words = '';
        $keywords = $this->RChannelChatAccess->getKeywords();
        if ($keywords) {
            foreach ($keywords as $value) {
                if ($value['word']) {
                    $words .= "'" . addslashes($value['word']) . "',";
                }
            }
            $words = trim($words, ',');
        }
        $list = $this->RChannelChatAccess->getChannelWaitMessageUser($type, $num, $words);
        $data = array(
            'list' => $list
        );

        return ['error' => 0, 'data' => $data];
    }

    public function dealChatVoice($xml)
    {
        $returnData = array(
            'error' => 0,
            'data' => []
        );

        $bucket = Yii::$app->params['vip_video_bucket'];
        $wechat = Yii::$app->wechat_new;

        $token = $wechat->getAccessToken();
        $returnJson = Request::httpPost("https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token=$token");
        $returnJson = json_decode($returnJson, true);

        if (empty($returnJson['ip_list'])) {
            $token = $wechat->getAccessToken(true);
        }

        $voice = file_get_contents("https://api.weixin.qq.com/cgi-bin/media/get?access_token={$token}&media_id={$xml['MediaId']}");
        $filePathFrom = '/tmp/' . $xml['MediaId'] . '.' . '.mp3';
        $filePathTo = 'chat/voice/' . md5($xml['MsgId'] . '_' . microtime() . '_' . rand(10, 99)) . '.mp3';
        file_put_contents($filePathFrom, $voice);

        QiniuService::uploadMp3ToQiniu($bucket, $filePathTo, $filePathFrom);
        $counts = $this->RChannelAccess->countChannelByOpenid($xml['FromUserName']);
        if (!empty($counts)) {
            $this->WChannelChatAccess->addChatMessagePre($xml['FromUserName'], $filePathTo, 3);
        }

        unlink($filePathFrom);
        return $returnData;
    }

    public function dealChatEmoji($xml)
    {
        $returnData = array(
            'error' => 0,
            'data' => []
        );

        $search = array(
            '/::)', '/::~', '/::B', '/::|', '/::<', '/::$', '/::X', '/::Z', '/::\'(', '/::-|',
            '/::@', '/::P', '/::D', '/::O', '/::(', '/:--b', '/::Q', '/::T', '/:,@P', '/:,@-D',
            '/::d', '/:,@o', '/::g', '/:|-)', '/::!', '/::L', '/::>', '/::,@', '/:,@f', '/::-S',
            '/:?', '/:,@x', '/:,@@', '/::8', '/:,@!', '/:xx', '/:bye', '/:wipe', '/:dig', '/:&-(',
            '/:B-)', '/:<@', '/:@>', '/::-O', '/:>-|', '/:P-(', '/::\'|', '/:X-)', '/::*',
            '/:@x', '/:8*', '/:hug', '/:moon', '/:sun', '/:bome', '/:!!!', '/:pd', '/:pig', '/:<W>',
            '/:coffee', '/:eat', '/:heart', '/:strong', '/:weak', '/:share', '/:v', '/:@)', '/:jj', '/:ok', '/:no',
            '/:rose', '/:fade', '/:showlove', '/:love', '/:<L>'
        );

        $replace = array(
            '<img class="img-emoji" src="/images/face/1.gif">',
            '<img class="img-emoji" src="/images/face/2.gif">',
            '<img class="img-emoji" src="/images/face/3.gif">',
            '<img class="img-emoji" src="/images/face/4.gif">',
            '<img class="img-emoji" src="/images/face/5.gif">',
            '<img class="img-emoji" src="/images/face/6.gif">',
            '<img class="img-emoji" src="/images/face/7.gif">',
            '<img class="img-emoji" src="/images/face/8.gif">',
            '<img class="img-emoji" src="/images/face/9.gif">',
            '<img class="img-emoji" src="/images/face/10.gif">',
            '<img class="img-emoji" src="/images/face/11.gif">',
            '<img class="img-emoji" src="/images/face/12.gif">',
            '<img class="img-emoji" src="/images/face/13.gif">',
            '<img class="img-emoji" src="/images/face/14.gif">',
            '<img class="img-emoji" src="/images/face/15.gif">',
            '<img class="img-emoji" src="/images/face/16.gif">',
            '<img class="img-emoji" src="/images/face/17.gif">',
            '<img class="img-emoji" src="/images/face/18.gif">',
            '<img class="img-emoji" src="/images/face/19.gif">',
            '<img class="img-emoji" src="/images/face/20.gif">',
            '<img class="img-emoji" src="/images/face/21.gif">',
            '<img class="img-emoji" src="/images/face/22.gif">',
            '<img class="img-emoji" src="/images/face/23.gif">',
            '<img class="img-emoji" src="/images/face/24.gif">',
            '<img class="img-emoji" src="/images/face/25.gif">',
            '<img class="img-emoji" src="/images/face/26.gif">',
            '<img class="img-emoji" src="/images/face/27.gif">',
            '<img class="img-emoji" src="/images/face/28.gif">',
            '<img class="img-emoji" src="/images/face/29.gif">',
            '<img class="img-emoji" src="/images/face/30.gif">',
            '<img class="img-emoji" src="/images/face/31.gif">',
            '<img class="img-emoji" src="/images/face/32.gif">',
            '<img class="img-emoji" src="/images/face/33.gif">',
            '<img class="img-emoji" src="/images/face/34.gif">',
            '<img class="img-emoji" src="/images/face/35.gif">',
            '<img class="img-emoji" src="/images/face/36.gif">',
            '<img class="img-emoji" src="/images/face/37.gif">',
            '<img class="img-emoji" src="/images/face/38.gif">',
            '<img class="img-emoji" src="/images/face/39.gif">',
            '<img class="img-emoji" src="/images/face/40.gif">',
            '<img class="img-emoji" src="/images/face/41.gif">',
            '<img class="img-emoji" src="/images/face/42.gif">',
            '<img class="img-emoji" src="/images/face/43.gif">',
            '<img class="img-emoji" src="/images/face/44.gif">',
            '<img class="img-emoji" src="/images/face/45.gif">',
            '<img class="img-emoji" src="/images/face/46.gif">',
            '<img class="img-emoji" src="/images/face/47.gif">',
            '<img class="img-emoji" src="/images/face/48.gif">',
            '<img class="img-emoji" src="/images/face/49.gif">',
            '<img class="img-emoji" src="/images/face/50.gif">',
            '<img class="img-emoji" src="/images/face/51.gif">',
            '<img class="img-emoji" src="/images/face/52.gif">',
            '<img class="img-emoji" src="/images/face/53.gif">',
            '<img class="img-emoji" src="/images/face/54.gif">',
            '<img class="img-emoji" src="/images/face/55.gif">',
            '<img class="img-emoji" src="/images/face/56.gif">',
            '<img class="img-emoji" src="/images/face/57.gif">',
            '<img class="img-emoji" src="/images/face/58.gif">',
            '<img class="img-emoji" src="/images/face/59.gif">',
            '<img class="img-emoji" src="/images/face/60.gif">',
            '<img class="img-emoji" src="/images/face/61.gif">',
            '<img class="img-emoji" src="/images/face/62.gif">',
            '<img class="img-emoji" src="/images/face/63.gif">',
            '<img class="img-emoji" src="/images/face/64.gif">',
            '<img class="img-emoji" src="/images/face/65.gif">',
            '<img class="img-emoji" src="/images/face/66.gif">',
            '<img class="img-emoji" src="/images/face/67.gif">',
            '<img class="img-emoji" src="/images/face/68.gif">',
            '<img class="img-emoji" src="/images/face/69.gif">',
            '<img class="img-emoji" src="/images/face/70.gif">',
            '<img class="img-emoji" src="/images/face/71.gif">',
            '<img class="img-emoji" src="/images/face/72.gif">',
            '<img class="img-emoji" src="/images/face/73.gif">',
            '<img class="img-emoji" src="/images/face/74.gif">',
            '<img class="img-emoji" src="/images/face/75.gif">'
        );

        $newStr = str_replace($search, $replace, $xml['Content']);

        $counts = $this->RChannelAccess->countChannelByOpenid($xml['FromUserName']);

        if (!empty($counts)) {
            $this->WChannelChatAccess->addChatMessagePre($xml['FromUserName'], $newStr, 1);
        }

        return $returnData;
    }

    public function dealConnectEvent($server, $data, $fd)
    {
        $returnData = array(
            'error' => 0,
            'data' => []
        );
        $pageId = $this->RChannelChatAccess->getPageId($data['kefu_id']);

        if (!empty($pageId)) {
            $dataSend = $this->getCloseData();
            $this->safePush($server, $pageId, $dataSend);
        }

        $this->WChannelChatAccess->addKefuWait($data['kefu_id'], $data['fd']);

        return $returnData;
    }

    public function dealAccessEvent($server, $data, $fd)
    {
        $returnData = array(
            'error' => 0,
            'data' => []
        );

        $dataSend = $this->RChannelChatAccess->getAccessMessage($data['open_id']);

        if (!empty($dataSend)) {
            $messagePreIdList = [];
            $messageList = [];

            foreach ($dataSend as &$message) {
                $message['date'] = date('m/d H:i', $message['time_created']);
                $dataPush = $this->getAccessData($message);
                $isSuccess = $this->safePush($server, $fd, $dataPush);

                if ($isSuccess) {
                    $messagePreIdList[] = $message['id'];
                    $messageList[] = $message;
                }
            }

            $this->WChannelChatAccess->addMessage($messageList, $data);
            $this->WChannelChatAccess->deleteMessagePre($messagePreIdList);

            return $returnData;
        }
    }

    public function dealViewEvent($server, $data, $fd)
    {
        $returnData = array(
            'error' => 0,
            'data' => []
        );

        $isConnect = $this->RChannelChatAccess
                ->checkIsConncet($data['kefu_id'], $data['open_id'], $fd);

        $isConnect = empty($isConnect) ? false : true;

        if ($isConnect) {
            $dataSend = $this->RChannelChatAccess->getAccessMessage($data['open_id']);

            if (!empty($dataSend)) {
                $messagePreIdList = [];
                $messageList = [];

                foreach ($dataSend as &$message) {
                    $message['date'] = date('m/d H:i', $message['time_created']);
                    $dataPush = $this->getViewData($message);
                    $isSuccess = $this->safePush($server, $fd, $dataPush);

                    if ($isSuccess) {
                        $messagePreIdList[] = $message['id'];
                        $messageList[] = $message;
                    }
                }

                $this->WChannelChatAccess->addMessage($messageList, $data);
                $this->WChannelChatAccess->deleteMessagePre($messagePreIdList);
            }
        }

        return $returnData;
    }

    public function dealTimerEvent($server, $data, $fd)
    {
        $returnData = array(
            'error' => 0,
            'data' => []
        );

        /**
         * push head
         */
        $allKefu = $this->RChannelChatAccess->getAllKeFu();

        if (!empty($allKefu)) {
            foreach ($allKefu as $kefu) {
                if (isset($data['head'][$kefu['kefu_id']])) {
                    $headData = $this->getHeadData($data['head'][$kefu['kefu_id']]);

                    $this->safePush($server, $kefu['page_id'], $headData);
                } else {
                    $headData = $this->getHeadData($data['head'][0]);

                    $this->safePush($server, $kefu['page_id'], $headData);
                }
            }
        }

        /**
         * push left
         */
        if (!empty($data['left'])) {
            foreach ($data['left'] as $left) {
                $leftData = $this->getLeftData($left);
                $this->safePush($server, $left['page_id'], $leftData);
            }
        }

        /**
         * push body
         */
        if (!empty($data['body'])) {
            foreach ($data['body'] as $body) {
                $dataSend = $this->RChannelChatAccess->getAccessMessage($body['open_id']);

                if (!empty($dataSend)) {
                    $messagePreIdList = [];
                    $messageList = [];
                    foreach ($dataSend as &$message) {
                        $message['date'] = date('m/d H:i', $message['time_created']);
                        $dataPush = $this->getBodyData($message);
                        $isSuccess = $this->safePush($server, intval($body['page_id']), $dataPush);

                        if ($isSuccess) {
                            $messagePreIdList[] = $message['id'];
                            $messageList[] = $message;
                        }
                    }

                    $this->WChannelChatAccess->addMessage($messageList, $body);
                    $this->WChannelChatAccess->deleteMessagePre($messagePreIdList);
                }
            }
        }

        /**
         * push user
         */
        if (!empty($data['user'])) {
            foreach ($data['user'] as $user) {
                $userData = $this->getUserData($user);
                $this->safePush($server, $user['page_id'], $userData);
            }
        }

        /**
         * push alert
         */
        if (!empty($data['alert']) && !empty($allKefu)) {
            foreach ($allKefu as $alert) {
                $alertData = $this->getAlertData($data['alert']);
                $this->safePush($server, $alert['page_id'], $alertData);
            }
        }

        return $returnData;
    }

    public function dealTansferEvent($server, $data, $fd)
    {
        $returnData = array(
            'error' => 0,
            'data' => []
        );

        $name = $this->RChannelAccess->getChannelNameByOpenid($data['open_id']);
        $message = $this->getTransferData($name);
        $this->safePush($server, $data['page_id'], $message);

        return $returnData;
    }

    public function dealRtransferEvent($server, $data, $fd)
    {
        $returnData = array(
            'error' => 0,
            'data' => []
        );

        $message = $this->getRtransferData($data, $fd);
        $this->safePush($server, $data['page_id'], $message);

        return $returnData;
    }

    public function dealRefuseTransferEvent($server, $data, $fd)
    {
        $returnData = array(
            'error' => 0,
            'data' => []
        );

        $message = $this->getRefuseData();
        $this->safePush($server, $data['page_id'], $message);

        return $returnData;
    }

    public function doClose($pageId)
    {
        $openIds = $this->RChannelChatAccess->getConnectedOpenid($pageId);

        if (!empty($openIds)) {
            foreach ($openIds as $openId) {
                $type = $this->getUserType($openId, '');
                $this->WChannelChatAccess->addChatWait($openId, $type);
            }
        }

        $this->WChannelChatAccess->deleteChatWaitKefuByPage($pageId);
        $this->WChannelChatAccess->disconnectByPageId($pageId);

        return true;
    }

    private function getViewData($message)
    {
        return [
            'event' => 'VIEW',
            'message' => $message,
        ];
    }

    private function getHeadData($head)
    {
        return [
            'event' => 'HEAD',
            'message' => $head,
        ];
    }

    private function getLeftData($left)
    {
        return [
            'event' => 'LEFT',
            'message' => $left,
        ];
    }

   

    private function getUserData($user)
    {
        return [
            'event' => 'USER',
            'message' => $user,
        ];
    }

    private function getAlertData($alert)
    {
        return [
            'event' => 'ALERT',
            'message' => $alert,
        ];
    }

    private function getBodyData($body)
    {
        return [
            'event' => 'BODY',
            'message' => $body,
        ];
    }

    private function getTransferData($name)
    {
        return [
            'event' => 'TRANSFER',
            'message' => $name,
        ];
    }

    private function getRtransferData($data, $pageId)
    {
        return [
            'event' => 'RTRANSFER',
            'message' => array(
                'kefu_id' => $data['kefu_id'],
                'link_id' => $data['link_id'],
                'kefu_name' => $data['kefu_name'],
                'user_nick' => $data['user_nick'],
                'page_id' => $pageId
            )
        ];
    }

    private function getRefuseData()
    {
        return [
            'event' => 'REFUSE_TRANSFER',
            'message' => ''
        ];
    }

    private function getCloseData()
    {
        return [
            'event' => 'CLOSE',
            'message' => '',
        ];
    }

    public function doEditTransfer($linkId, $kefuId)
    {
        $linkInfo = $this->RChannelChatAccess->getCountLinkByOpenid($linkId);

        $kefuInfo = $this->RChannelChatAccess->getChatWaitByKefu($kefuId);
        if (empty($linkInfo)) {
            return json_encode(['error' => '该用户没被接待中，无法转接']);
        }
        if (empty($kefuInfo)) {
            $kefuInfo['page_id'] = 1;
        }

        $this->WChannelChatAccess->editLinkInfoStatus($linkInfo['id']);

        $this->WChannelChatAccess->editChatLinkByKefuId($kefuId, $linkInfo['open_id']);

        $this->WChannelChatAccess->updateAllCountersByKefu($kefuId);

        $this->WChannelChatAccess->addClassLinkBykefu($kefuId, $linkInfo['open_id'], $kefuInfo['page_id']);

        return json_encode(
                array(
                    'error' => '',
                    'data' => array(
                        'open_id' => $linkInfo['open_id'],
                        'page_id' => $kefuInfo['page_id']
                )));
    }

    public function doSendImage($openId, $file, $uid)
    {
        // $accessKey = Yii::$app->params['qiniuAccessKey'];
        Yii::$app->params['qiniuSecretKey'];

        if ($file['file']["error"] > 0) {
            return 0;
        } else {
            $filePath = $file['file']['tmp_name'];

            $fileKey = md5($openId . '_' . microtime() . '_' . rand(10, 99));

            $bucket = Yii::$app->params['vip_static_bucket'];

            $key = 'chat/image/' . $fileKey;

            if (!QiniuService::uploadToQiniu($bucket, $key, $filePath)) {
                return 0;
            } else {
                $this->WChannelChatAccess->addChatMessageInfo($openId, $key, $uid);

                //发送微信
                $wechat = Yii::$app->wechat_new;

                //file_put_contents('/data/log/pnl_api2/text',print_r(getimagesize($filePath),true));
                if ($file['file']['type'] == 'image/jpeg') {
                    $img = imagecreatefromjpeg($filePath);
                } else {
                    $img = imagecreatefrompng($filePath);
                }

                $imgPath = '/tmp/' . rand(10, 99) . '.jpg';
                imagepng($img, $imgPath);
                $token = $wechat->getAccessToken(true);
                $imgInfo = shell_exec('curl -F media=@' . $imgPath . ' "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=' . $token . '&type=image"');
                $imgInfo = json_decode($imgInfo, true);
                //file_put_contents('/data/log/pnl_api2/text', $imgInfo);
                $data = [
                    'touser' => $openId,
                    'msgtype' => 'image',
                    'image' => array('media_id' => $imgInfo['media_id']),
                ];
                $wechat->sendMessage($data);

                return json_encode(array('url' => Yii::$app->params['vip_static_path'] . $key), JSON_UNESCAPED_SLASHES);
            }
        }
    }

    public function closeSocket($pageId)
    {
        if (!empty($pageId)) {
            $kefu = $this->RChannelChatAccess->getChatWaitKefuByPageId($pageId);

            if (!empty($kefu)) {
                $link = $this->RChannelChatAccess->getChatMessagePreByPageId($pageId);

                $this->WChannelChatAccess->deleteChatWaitKefu($kefu['id']);

                $this->WChannelChatAccess->updateChatConnectByPageId($pageId);

                if (!empty($link)) {
                    foreach ($link as $v) {
                        $type = $this->getUserType($v['openid'], $v['message']);
                        $this->WChannelChatAccess->addChatWait($v['openid'], $type);
                    }
                }
            }
        }
        return true;
    }

    private function getUserType($openId, $message)
    {
        if ($message == '提现') {
            return 3;
        }

        $user = $this->RChannelAccess->getSaleChannelUserInfo($openId);

        if (date('Y-m-d', $user['created_at']) === date('Y-m-d', time())) {
            return 5;
        }

        if (!empty($user)) {
            switch ($user['message_type']) {
                case 0:
                    $type = 1;
                    break;
                case 1:
                    $type = 2;
                    break;
                case 2:
                    $type = 4;
                    break;
                default:
                    $type = 0;
                    break;
            }

            return $type;
        } else {
            return 0;
        }
    }

    public function doAddMessage($content, $openId)
    {
        $link = $this->RChannelChatAccess->countLinkInfoByOpenid($openId);

        if (empty($link) || (!empty($link) && $link['kefu_id'] == $this->userId)) {
            $content = $this->map($content);
            $messages = $this->WChannelChatAccess->addChatMessage($openId, $content, Yii::$app->user->identity->id);
            if (!empty($link)) {
                $kefuInfo = $this->RChannelChatAccess->getChatKefuInfo(Yii::$app->user->identity->id);
                $this->WChannelChatAccess->updateChatLinkIsHide(Yii::$app->user->identity->id);
                $this->WChannelChatAccess->doEditChatLinkStatus($openId, $kefuInfo['page_id'], Yii::$app->user->identity->id);
                $wait = $this->RChannelChatAccess->getChatWaitInfo($openId);
                if (!empty($wait)) {
                    $this->WChannelChatAccess->deleteChatWait($openId);
                }
            }
            return json_encode(array('error' => '', 'data' => array('content' => $content, 'message_id' => $messages, 'kefu_name' => Yii::$app->user->identity->nickname)));
        } else {
            $kefu = $this->RStudentAccess->getUserAccountById($link['kefu_id']);
            $user = $this->RChannelAccess->getSaleChannelByOpenId($openId);
            return json_encode(
                    array(
                        'error' => '该用户正在被客服 (' . $kefu['nickname'] . ') 接待,需要请求转接吗?',
                        'data' => array(
                            'link_id' => $link['id'],
                            'user_nick' => $user['nickname'],
                            'kefu_id' => Yii::$app->user->identity->id,
                            'kefu_name' => $kefu['nickname'],
                            'page_id' => $link['page_id'],
                    )));
        }
    }

    /**
     * 表情包
     */
    private function map($str)
    {
        $search = array(
            '[微笑]', '[撇嘴]', '[色]', '[发呆]', '[流泪]', '[害羞]', '[闭嘴]', '[睡]', '[大哭]', '[尴尬]',
            '[发怒]', '[调皮]', '[呲牙]', '[惊讶]', '[难过]', '[冷汗]', '[抓狂]', '[吐]', '[偷笑]', '[愉快]',
            '[白眼]', '[傲慢]', '[饥饿]', '[困]', '[惊恐]', '[流汗]', '[憨笑]', '[悠闲]', '[奋斗]', '[咒骂]',
            '[疑问]', '[嘘]', '[晕]', '[疯了]', '[衰]', '[敲打]', '[再见]', '[擦汗]', '[抠鼻]', '[糗大了]',
            '[坏笑]', '[左哼哼]', '[右哼哼]', '[哈欠]', '[鄙视]', '[委屈]', '[快哭了]', '[阴险]', '[亲亲]',
            '[吓]', '[可怜]', '[拥抱]', '[月亮]', '[太阳]', '[炸弹]', '[骷髅]', '[菜刀]', '[猪头]', '[西瓜]',
            '[咖啡]', '[饭]', '[爱心]', '[强]', '[弱]', '[握手]', '[胜利]', '[抱拳]', '[勾引]', '[OK]', '[NO]',
            '[玫瑰]', '[凋谢]', '[嘴唇]', '[爱情]', '[飞吻]'
        );

        $replace = array(
            '<img class="img-emoji" src="/images/face/1.gif">',
            '<img class="img-emoji" src="/images/face/2.gif">',
            '<img class="img-emoji" src="/images/face/3.gif">',
            '<img class="img-emoji" src="/images/face/4.gif">',
            '<img class="img-emoji" src="/images/face/5.gif">',
            '<img class="img-emoji" src="/images/face/6.gif">',
            '<img class="img-emoji" src="/images/face/7.gif">',
            '<img class="img-emoji" src="/images/face/8.gif">',
            '<img class="img-emoji" src="/images/face/9.gif">',
            '<img class="img-emoji" src="/images/face/10.gif">',
            '<img class="img-emoji" src="/images/face/11.gif">',
            '<img class="img-emoji" src="/images/face/12.gif">',
            '<img class="img-emoji" src="/images/face/13.gif">',
            '<img class="img-emoji" src="/images/face/14.gif">',
            '<img class="img-emoji" src="/images/face/15.gif">',
            '<img class="img-emoji" src="/images/face/16.gif">',
            '<img class="img-emoji" src="/images/face/17.gif">',
            '<img class="img-emoji" src="/images/face/18.gif">',
            '<img class="img-emoji" src="/images/face/19.gif">',
            '<img class="img-emoji" src="/images/face/20.gif">',
            '<img class="img-emoji" src="/images/face/21.gif">',
            '<img class="img-emoji" src="/images/face/22.gif">',
            '<img class="img-emoji" src="/images/face/23.gif">',
            '<img class="img-emoji" src="/images/face/24.gif">',
            '<img class="img-emoji" src="/images/face/25.gif">',
            '<img class="img-emoji" src="/images/face/26.gif">',
            '<img class="img-emoji" src="/images/face/27.gif">',
            '<img class="img-emoji" src="/images/face/28.gif">',
            '<img class="img-emoji" src="/images/face/29.gif">',
            '<img class="img-emoji" src="/images/face/30.gif">',
            '<img class="img-emoji" src="/images/face/31.gif">',
            '<img class="img-emoji" src="/images/face/32.gif">',
            '<img class="img-emoji" src="/images/face/33.gif">',
            '<img class="img-emoji" src="/images/face/34.gif">',
            '<img class="img-emoji" src="/images/face/35.gif">',
            '<img class="img-emoji" src="/images/face/36.gif">',
            '<img class="img-emoji" src="/images/face/37.gif">',
            '<img class="img-emoji" src="/images/face/38.gif">',
            '<img class="img-emoji" src="/images/face/39.gif">',
            '<img class="img-emoji" src="/images/face/40.gif">',
            '<img class="img-emoji" src="/images/face/41.gif">',
            '<img class="img-emoji" src="/images/face/42.gif">',
            '<img class="img-emoji" src="/images/face/43.gif">',
            '<img class="img-emoji" src="/images/face/44.gif">',
            '<img class="img-emoji" src="/images/face/45.gif">',
            '<img class="img-emoji" src="/images/face/46.gif">',
            '<img class="img-emoji" src="/images/face/47.gif">',
            '<img class="img-emoji" src="/images/face/48.gif">',
            '<img class="img-emoji" src="/images/face/49.gif">',
            '<img class="img-emoji" src="/images/face/50.gif">',
            '<img class="img-emoji" src="/images/face/51.gif">',
            '<img class="img-emoji" src="/images/face/52.gif">',
            '<img class="img-emoji" src="/images/face/53.gif">',
            '<img class="img-emoji" src="/images/face/54.gif">',
            '<img class="img-emoji" src="/images/face/55.gif">',
            '<img class="img-emoji" src="/images/face/56.gif">',
            '<img class="img-emoji" src="/images/face/57.gif">',
            '<img class="img-emoji" src="/images/face/58.gif">',
            '<img class="img-emoji" src="/images/face/59.gif">',
            '<img class="img-emoji" src="/images/face/60.gif">',
            '<img class="img-emoji" src="/images/face/61.gif">',
            '<img class="img-emoji" src="/images/face/62.gif">',
            '<img class="img-emoji" src="/images/face/63.gif">',
            '<img class="img-emoji" src="/images/face/64.gif">',
            '<img class="img-emoji" src="/images/face/65.gif">',
            '<img class="img-emoji" src="/images/face/66.gif">',
            '<img class="img-emoji" src="/images/face/67.gif">',
            '<img class="img-emoji" src="/images/face/68.gif">',
            '<img class="img-emoji" src="/images/face/69.gif">',
            '<img class="img-emoji" src="/images/face/70.gif">',
            '<img class="img-emoji" src="/images/face/71.gif">',
            '<img class="img-emoji" src="/images/face/72.gif">',
            '<img class="img-emoji" src="/images/face/73.gif">',
            '<img class="img-emoji" src="/images/face/74.gif">',
            '<img class="img-emoji" src="/images/face/75.gif">'
        );

        $newStr = str_replace($search, $replace, $str);

        return $newStr;
    }

    public function sendWechat($request)
    {
        $data = [
            'touser' => $request['open_id'],
            'msgtype' => 'text',
            'text' => array('content' => $request['content']),
        ];

        $wechat = Yii::$app->wechat_new;

        if ($wechat->sendMessage($data)) {
            return json_encode(array('error' => ''));
        } else {
            if ($wechat->sendMessage($data)) {
                return json_encode(array('error' => ''));
            } else {
                $this->WChannelChatAccess->editChatMessageFail($request['message_id']);

                return json_encode(array('error' => '发送失败,用户48小时未与公众号互动或者用户取消关注'));
            }
        }
    }

    public function offChat($linkId)
    {
        $link = $this->RChannelChatAccess->offChatInfo($linkId);

        if (!empty($link)) {
            $this->WChannelChatAccess->editLinkInfoStatus($link['id']);

            $data = array('error' => '');
        } else {
            $data = array('error' => '您跟当前用户没有连接');
        }

        return json_encode($data);
    }

    public function getCheckConnectCount($pageId, $uid)
    {
        $count = $this->RChannelChatAccess->checkConnectCount($pageId, $uid);

        return empty($count) ? 1 : 0;
    }

    public function getLoadMoreInfo($offset)
    {
        $link = $this->RChannelChatAccess->getChatLinkIsCurrentPage();

        $messages = $this->RChannelChatAccess->getChatMessageInfoByOpenId($link['open_id'], $offset);

        foreach ($messages as &$row) {
            if (empty($row['kefu_head'])) {
                $row['kefu_head'] = '/images/head_default.jpg';
            }
        }

        return json_encode($messages, JSON_UNESCAPED_SLASHES);
    }

    public function doSendHaibao($openId, $path, $uid)
    {
        $fileKey = md5($openId . '_' . microtime() . '_' . rand(10, 99));

        $bucket = Yii::$app->params['vip_static_bucket'];

        $key = 'chat/image/' . $fileKey;

        if (!QiniuService::uploadToQiniu($bucket, $key, $path)) {
            return 0;
        } else {
            $this->WChannelChatAccess->addChatMessageInfo($openId, $key, $uid);
            //发送微信
            $wechat = Yii::$app->wechat_new;
            $img = imagecreatefromjpeg($path);
            $imgPath = '/tmp/' . uniqid() . '.jpg';
            imagepng($img, $imgPath);
            $token = $wechat->getAccessToken(true);
            $imgInfo = shell_exec('curl -F media=@' . $imgPath . ' "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=' . $token . '&type=image"');
            $imgInfo = json_decode($imgInfo, true);

            $data = [
                'touser' => $openId,
                'msgtype' => 'image',
                'image' => array('media_id' => $imgInfo['media_id']),
            ];

            $wechat->sendMessage($data);

            unlink($path);

            return json_encode(
                    array(
                'url' => Yii::$app->params['vip_static_path'] . $key,
                'head' => Yii::$app->user->identity->head,
                'date' => date('Y-m-d H:i', time())
                    ), JSON_UNESCAPED_SLASHES);
        }
    }

    public function sendReward($userId, $title, $totalMoney)
    {
        $have_premission = $this->RChannelAccess->getHavePremission($userId);
        if (empty($have_premission)) {
            return ['error' => '用户没有权限，请开启权限之后发送', 'data' => ''];
        }

        $money = $this->RChannelAccess->getThisSaleChannelReward($userId);

        $history_money = $this->RChannelAccess->getHistorySaleChannelReward($userId);

        $money = $money - $history_money;

        if ($totalMoney > $money) {
            return ['error' => '超过了可分配的最大金额额度', 'data' => ''];
        }

        $open_id = $this->RChannelAccess->getChannelBindOpenid($userId);

        $num = ceil($totalMoney / 200);
        $mch_billno = '';
        $ac_amount = 0;

        for ($i = 0; $i < $num; $i++) {
            if ($i == $num - 1) {
                $money = $totalMoney - $i * 200;
                $req = array(
                    'open_id' => $open_id,
                    'mch_id' => Yii::$app->params['sales_mch_id'],
                    'wxappid' => Yii::$app->params['sales_app_id'],
                    'wechat_mch_secret' => Yii::$app->params['sales_mch_secret'],
                    'send_name' => '微课',
                    'total_amount' => intval($money) * 100,
                    'total_num' => 1,
                    'wishing' => $title,
                    'act_name' => '推广奖励',
                    'remark' => '妙克信息科技',
                    'scene_id' => 'PRODUCT_5',
                    'pem_root' => Yii::$app->params['sales_pem_root']
                );
            } else {
                $req = array(
                    'open_id' => $open_id,
                    'mch_id' => Yii::$app->params['sales_mch_id'],
                    'wxappid' => Yii::$app->params['sales_app_id'],
                    'wechat_mch_secret' => Yii::$app->params['sales_mch_secret'],
                    'send_name' => '微课',
                    'total_amount' => intval(200) * 100,
                    'total_num' => 1,
                    'wishing' => $title,
                    'act_name' => '推广奖励',
                    'remark' => '妙克信息科技',
                    'scene_id' => 'PRODUCT_5',
                    'pem_root' => Yii::$app->params['sales_pem_root']
                );
            }

            $res = RedPack::send($req);

            if ($res['error'] === 0) {
                $mch_billno .= ',' . $res['data']['mch_billno'];
                $ac_amount += ($i == $num - 1) ? $money : 200;
            } else {
                break;
            }
        }

        if (!empty($mch_billno)) {
            $mch_billno = substr($mch_billno, 1);
            $transaction = Yii::$app->db->beginTransaction();

            try {
                $this->WChannelAccess->addSalesTradeInfo($userId, $mch_billno, $totalMoney);

                $transaction->commit();

                $data = array(
                    'total_money' => $totalMoney,
                    'history_id' => '暂无',
                    'error_msg' => empty($res['error']) ? '' : $res['error']
                );

                return ['error' => 0, 'data' => $data];
            } catch (\yii\base\Exception $e) {
                $transaction->rollBack();

                return ['error' => '操作失败，请联系管理员', 'data' => ''];
            }
        } else {
            return ['error' => $res['error'], 'data' => ''];
        }
    }

    public function checkAccess($waitId, $page)
    {
        switch ($page) {
            case 1:
                $view = '/chat/chat-user-page?type=1';
                break;
            case 2:
                $view = '/chat/chat-user-page?type=2';
                break;
            case 3:
                $view = '/chat/chat-user-page?type=3';
                break;
            case 4:
                $view = '/chat/chat-user-page?type=4';
                break;
            default:
                $view = '/chat/chat-user-page?type=0';
                break;
        }

        $wait = $this->RChannelChatAccess->getChatWaitById($waitId);

        if (!empty($wait)) {
            $count = $this->RChannelChatAccess->findNoHideClassLinkCount($wait['open_id']);

            if (empty($count)) {
                $data = array('data' => 1);
            } else {
                $data = array('data' => 0, 'page' => $view);
            }
        } else {
            $data = array('data' => 0, 'page' => $view);
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public function accessRight($waitId)
    {
        $wait = $this->RChannelChatAccess->getChatWaitById($waitId);

        if (!empty($wait)) {
            $count = $this->RChannelChatAccess->countLinkByOpenid($wait['open_id']);

            if (empty($count)) {
                $messages = $this->RChannelChatAccess->getChatMessageAndInitInfo($wait['open_id']);

                $user = $this->RChannelAccess->getChannelUserByOpenid($wait['open_id']);
                $messageNew = [];
                if (!empty($messages)) {
                    $counts = count($messages);
                    for ($i = 0; $i < $counts; $i++) {
                        $messageNew[$i] = array_pop($messages);
                        $messageNew[$i]['head'] = $user['head'];
                        $messageNew[$i]['student_name'] = $user['nickname'];
                    }
                }

                //顾问
                $adviser = $this->RAccountAccess->getUserAccountOne($user['kefu_id']);
//                $student = $this->RStudentAccess->getWeChatAccInfo($wait['open_id']);

                $kefu = $this->RChannelChatAccess->getChatKefuInfo(Yii::$app->user->identity->id);

                $this->WChannelChatAccess->deleteChatWait($wait['open_id']);
                // 标记高位用户
                $this->WChannelChatAccess->editChatLinkSignHide($wait['open_id']);

                $this->WChannelChatAccess->editChatLinkByKefu();
                $this->WChannelChatAccess->updateChatLinkIsHide(Yii::$app->user->identity->id);

                $this->WChannelChatAccess->addChatLink($wait['open_id'], $kefu['page_id'], Yii::$app->user->identity->id);
                //更新未读消息为已读
                $this->WChannelChatAccess->readMessageByOpenId($wait['open_id']);

                $counts = '';

                $userId = empty($user) ? '' : $user['id'];

                $channel_name = '';

                //获取乐器
                if (!empty($user['instrument'])) {
                    $ids = trim($user['instrument'], ',');
                    $instrument = $this->RChannelAccess->getInstrumentByIds($ids);
                    $user['instrument'] = implode(' ', $instrument);
                }
                $nick = empty($user) ? '' : '(' . $user['nickname'] . ')';
                $adviser_name = $adviser ? $adviser['nickname'] : '';
                return [$messageNew, $user, $userId, $channel_name, $nick, $counts, $adviser_name];
            }
        }
    }

    public function getLink($openId)
    {
        $link = $this->RChannelChatAccess->getLeftUserByOpenId($openId);

        if ($link) {
            switch ($link['message_type']) {
                case '1':
                    $type = '新用户';
                    break;
                case '2':
                    $type = '有推广价值用户';
                    break;
                case '3':
                    $type = '无推广价值用户';
                    break;
                default:
                    $type = '无定义';
                    break;
            }
            $link['message_type'] = $type;
        }
        return json_encode(['error' => '', 'link' => $link]);
    }

    public function getAccessTalk($openId)
    {
        $count = $this->RChannelChatAccess->countLinkByOpenid($openId);

        $messages = $this->RChannelChatAccess->getChatMessageAndInitInfo($openId);

        $user = $this->RChannelAccess->getChannelUserByOpenid($openId);
        $messageNew = [];
        if (!empty($messages)) {
            $counts = count($messages);
            for ($i = 0; $i < $counts; $i++) {
                $messageNew[$i] = array_pop($messages);
                $messageNew[$i]['head'] = $user['head'];
                $messageNew[$i]['student_name'] = $user['nickname'];
            }
        }

        //顾问
        $adviser = $this->RAccountAccess->getUserAccountOne($user['kefu_id']);

        $kefu = $this->RChannelChatAccess->getChatKefuInfo(Yii::$app->user->identity->id);

        //删除该用户的未读消息提示
        $this->WChannelChatAccess->deleteChatWait($openId);
        //没有进行接待时
        if (empty($count)) {
            // 标记高位用户
            $this->WChannelChatAccess->editChatLinkSignHide($openId);
            $this->WChannelChatAccess->editChatLinkByKefu();
            $this->WChannelChatAccess->updateChatLinkIsHide($this->userId);
            $this->WChannelChatAccess->addChatLink($openId, $kefu['page_id'], $this->userId);
        } else {
            $this->WChannelChatAccess->updateChatLinkIsHide($this->userId);
            $this->WChannelChatAccess->doEditChatLinkStatus($openId, $kefu['page_id'], $this->userId);
        }
        //更新未读消息为已读
        $this->WChannelChatAccess->readMessageByOpenId($openId);
        $counts = '';

        $uid = empty($user) ? '' : $user['id'];

        //获取乐器
        if (!empty($user['instrument'])) {
            $ids = trim($user['instrument'], ',');
            $instrument = $this->RChannelAccess->getInstrumentByIds($ids);
            $user['instrument'] = implode(' ', $instrument);
        }
        $channel_name = '';

        $nick = empty($user) ? '' : '(' . $user['nickname'] . ')';

        $adviser_name = $adviser ? $adviser['nickname'] : '';
        return [$messageNew, $user, $uid, $channel_name, $nick, $counts, $adviser_name];
    }

    public function getTransferServer($kefuId)
    {
        return $this->RChannelChatAccess->getWaitKefuList($kefuId);
    }

    public function clickChannelLinkRight($linkId)
    {
        $link = $this->RChannelChatAccess->getCountChannelChatLinkById($linkId);

        $user = $this->RChannelAccess->getChannelUserByOpenid($link['open_id']);

        $messages = $this->RChannelChatAccess->getChatMessageAndInitInfo($link['open_id']);
        $messageNew = [];
        if (!empty($messages)) {
            $counts = count($messages);
            for ($i = 0; $i < $counts; $i++) {
                $messageNew[$i] = array_pop($messages);
                $messageNew[$i]['head'] = $user['head'];
                $messageNew[$i]['student_name'] = $user['nickname'];
            }
        }

        $this->WChannelChatAccess->updateAllChannelChatLinkStatus($link['open_id']);

        $this->WChannelChatAccess->updateChannelChatLinkStatus($link['id']);


        if (!empty($user->kefu_id)) {
            $kefu = $this->RAccountAccess->getNewSignKefuNick($user->kefu_id);
        }
        //获取乐器
        if (!empty($user['instrument'])) {
            $ids = trim($user['instrument'], ',');
            $instrument = $this->RChannelAccess->getInstrumentByIds($ids);
            $user['instrument'] = $instrument ? implode(' ', $instrument) : '';
        }
        $kefu_name = empty($kefu) ? '暂无' : $kefu;

        $mobile = empty($user->username) ? '' : '(' . $user->username . ')';
        $user['auth_time'] = empty($user['auth_time']) ? 0: date("Y-m-d H:i:s", $user['auth_time']);
        $data = array(
            'messageNew' => $messageNew,
            'user' => $user,
            'user_id' => $user['id'],
            'mobile' => $mobile,
            'kefu_name' => $kefu_name
        );

        return ['error' => 0, 'data' => $data];
    }

    public function getCheckTalk($openId)
    {
        $link = $this->RChannelChatAccess->getChatLinkByOpenId($openId);

        if (empty($link) || $link['kefu_id'] == $this->userId) {
            $data = array('error' => '');
        } else {
            $kefu = $this->RAccountAccess->getUserAccountById($link['kefu_id']);

            $user = $this->RChannelAccess->getUserInitByOpenId($openId);

            $data = array(
                'error' => '该用户正在被客服 (' . $kefu['nickname'] . ') 接待,需要请求转接吗?',
                'data' => array(
                    'link_id' => $link['id'],
                    'user_nick' => $user['nickname'],
                    'kefu_id' => Yii::$app->user->identity->id,
                    'kefu_name' => $kefu['nickname'],
                    'page_id' => $link['page_id'],
            ));
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public function addSubscribeMessage($openId)
    {
        $countLink = $this->RChannelChatAccess->countLinkByOpenid($openId);
        if (empty($countLink)) {
            $countWait = $this->RChannelChatAccess->countChannelWaitByOpenid($openId);
            if (empty($countWait)) {
                $this->WChannelChatAccess->addChatWait($openId, 1);
            }
        }

        $this->WChannelChatAccess->addChatMessagePre($openId, '你好,我刚刚关注了公众号!', 1);
        return true;
    }

    private function safePush($server, $fd, $data)
    {
        $time = time() - 300;
        $status = $server->connection_info($fd);

        if (!$status) {
            $this->doClose($fd);
            return false;
        } elseif ($status['last_time'] < $time) {
            $this->doClose($fd);
            return false;
        }

        if ($server->push($fd, json_encode($data, JSON_UNESCAPED_SLASHES))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 快捷回复
     */
    public function getQuickAnswerList($type)
    {
        return $this->RChannelChatAccess->getQuickAnswerList($type);
    }

    public function addQuickAnswer($content, $type)
    {
        $id = $this->WChannelChatAccess->addQuickAnswer($type, $content);

        if ($id > 0) {
            $data = array('id' => $id);
            return ['error' => 0, 'data' => $data];
        }

        return ['error' => '操作失败', 'data' => ''];
    }

    public function doEditQucikAnswer($qid, $content)
    {
        $kefu_id = Yii::$app->user->identity->id;
        $this->RAccountAccess->getKefuRoleByKefuid($kefu_id);

//        if ($kefu_role != 2)
//        {
//            return ['error' => '权限不足', 'data' => ''];
//        }

        if ($this->WChannelChatAccess->doEditQucikAnswer($qid, $content)) {
            return ['error' => 0, 'data' => ''];
        } else {
            return ['error' => '编辑失败', 'data' => ''];
        }
    }

    public function doDeleteQucikAnswer($qid)
    {
        $kefu_id = Yii::$app->user->identity->id;
        $this->RAccountAccess->getKefuRoleByKefuid($kefu_id);

//        if ($kefu_role != 2)
//        {
//            return ['error' => '权限不足', 'data' => ''];
//        }

        if ($this->WChannelChatAccess->doDeleteQucikAnswer($qid)) {
            return ['error' => 0, 'data' => ''];
        } else {
            return ['error' => '删除失败', 'data' => ''];
        }
    }

    public function doSaveChatMessage($id, $message = '', $money = 0)
    {
        if (is_numeric($id)) {
            $openid = $this->RChannelAccess->getChannelBindOpenid($id);
        } else {
            $openid = $id;
        }
            
        $kefu_id = Yii::$app->user->identity->id;
        $kefu_name = $this->RAccountAccess->getUserAccountOne($kefu_id);

        if (empty($money)) {
            $message = '[相关话术：' . $message . ']';
        } else {
            $message = '[本次已发送红包：' . $money . '元。^_^]';
        }


        if ($this->WChannelChatAccess->doSaveChatMessage($kefu_id, $openid, $message)) {
            $data = array(
                'message' => $message,
                'kefu_name' => $kefu_name['nickname'],
                'time' => date('m/d H:i', time()),
                'head' => $kefu_name['head']
            );

            return ['error' => 0, 'data' => $data];
        } else {
            return ['error' => '添加相关话术失败', 'data' => ''];
        }
    }

    public function doPassiveSaveChatMessage($data)
    {
        $message = '[系统提示：' . $data['message'] . ']';

        if ($this->WChannelChatAccess->doPassiveSaveChatMessage($data['open_id'], $message)) {
            return ['error' => 0, 'data' => $data];
        } else {
            return ['error' => '添加相关话术失败', 'data' => ''];
        }
    }

    public function wechatSendPic($openId, $path, $uid)
    {
        $key = str_replace(Yii::$app->params['vip_static_path'], '', $path);
        $this->WChannelChatAccess->addChatMessageInfo($openId, $key, $uid);

        //处理图片
        $new_path = '/tmp/' . uniqid() . '.jpg';
        ob_start();                 //打开输出
        readfile($path);            //输出图片文件
        $img = ob_get_contents();   //得到浏览器输出
        ob_end_clean();             //清除输出并关闭
        $fp = @fopen($new_path, "a");
        fwrite($fp, $img);           //向当前目录写入图片文件，并重新命名
        fclose($fp);

        //发送微信
        $wechat = Yii::$app->wechat_new;
        $token = $wechat->getAccessToken(true);
        $imgInfo = shell_exec('curl -F media=@' . $new_path . ' "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=' . $token . '&type=image"');
        $imgInfo = json_decode($imgInfo, true);

        $data = [
            'touser' => $openId,
            'msgtype' => 'image',
            'image' => array('media_id' => $imgInfo['media_id']),
        ];

        $wechat->sendMessage($data);

        return json_encode(
                array(
            'url' => $path,
            'head' => Yii::$app->user->identity->head,
            'date' => date('Y-m-d H:i', time())
                ), JSON_UNESCAPED_SLASHES);
    }
}
