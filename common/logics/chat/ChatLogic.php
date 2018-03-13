<?php

/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 16/12/27
 * Time: 下午5:43
 */

namespace common\logics\chat;

use common\services\LogService;
use common\services\QiniuService;
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
use common\models\music\UserShare;

class ChatLogic extends Object implements IChat
{

    /** @var  \common\sources\read\student\StudentAccess  $RStudentAccess */
    private $RStudentAccess;

    /** @var  \common\sources\read\classes\ClassAccess  $RClassAccess */
    private $RClassAccess;

    /** @var  \common\sources\read\channel\ChannelAccess  $RChannelAccess */
    private $RChannelAccess;

    /** @var  \common\sources\read\chat\ChatAccess  $RChatAccess */
    private $RChatAccess;

    /** @var  \common\sources\write\chat\ChatAccess  $RChatAccess */
    private $WChatAccess;

    /** @var  \common\sources\read\account\AccountAccess  $RAccountAccess */
    private $RAccountAccess;

    /** @var  \common\sources\write\channel\ChannelAccess  $WChannelAccess */
    private $WChannelAccess;

    public function init()
    {
        $this->RChatAccess = Yii::$container->get('RChatAccess');
        $this->RStudentAccess = Yii::$container->get('RStudentAccess');
        $this->WChatAccess = Yii::$container->get('WChatAccess');
        $this->RClassAccess = Yii::$container->get('RClassAccess');
        $this->RChannelAccess = Yii::$container->get('RChannelAccess');
        $this->RAccountAccess = Yii::$container->get('RAccountAccess');
        $this->WChannelAccess = Yii::$container->get('WChannelAccess');
        parent::init();
    }

    public function getLikeCrmKefu()
    {
        return $this->RStudentAccess->getLikeCrmKefu();
    }

    
    public function getChatHistoryCount($request)
    {
        $request['time_start'] = strtotime($request['time_start']);
        $request['time_end'] = strtotime($request['time_end']) + 86400;

        return $this->RChatAccess->getChatHistoryCount($request);
    }

    public function getChatHistoryList($request)
    {
        $request['time_start'] = strtotime($request['time_start']);
        $request['time_end'] = strtotime($request['time_end']) + 86400;

        $chat_list = $this->RChatAccess->getChatHistoryList($request);

        foreach ($chat_list as &$row) {
            if (empty($row['kefu_head'])) {
                $row['kefu_head'] = '/images/head_default.jpg';
            }
        }

        return $chat_list;
    }

    public function getKefu($name)
    {
        $user_accounts = $this->RStudentAccess->getLikeCrmKefuByName($name);
        return json_encode($user_accounts, JSON_UNESCAPED_SLASHES);
    }

    public function getLeftUserInfo($isHistory, $keyword)
    {
        $users = $this->RChatAccess->getLeftUserInfo($isHistory, $keyword);
        foreach ($users as &$row) {
            if (!is_null($row['purchase'])) {
                if ($row['is_high'] == 0) {
                    $row['type'] = empty($row['purchase']) ? 2 : 3;
                } else {
                    $row['type'] = 4;
                }
            } else {
                $row['type'] = 1;
            }
        }
        return $users;
    }

    public function getChannelLeftUserInfo($isHistory, $keyword)
    {
        $isHistory = $isHistory ? 0 : 1;
        $keyword = addslashes($keyword);
        usleep(200000);//读写分离延迟
        $users = $this->RChatAccess->getChannelLeftUserInfo($isHistory, $keyword);
        return $users;
    }

    /**
     * 点击左边头像
     */
    public function clickLinkRight($linkId)
    {
        $link = $this->RChatAccess->getCountLinkByOpenid($linkId);
        $messages = $this->RChatAccess->getChatMessageInfo($link['open_id']);

        $messageNew = [];
        if (!empty($messages)) {
            $counts = count($messages);
            for ($i = 0; $i < $counts; $i++) {
                $messageNew[] = array_pop($messages);
                if (empty($messageNew[$i]['kefu_head'])) {
                    $messageNew[$i]['kefu_head'] = '/images/head_default.jpg';
                }
            }
        }

        $this->WChatAccess->updateAllChatLinkStatus($link['open_id']);

        $this->WChatAccess->updateChatLinkStatus($link['id']);

        $user = $this->RStudentAccess->getUserInitInfoByOpenid($link['open_id']);

        $name = $this->RChannelAccess->getUserChannelName($user['channel_id']);

        $student = $this->RStudentAccess->getWeChatAccInfo($link['open_id']);

        if (!empty($student['channel_name'])) {
            $channelName = $student['channel_name'];
        } elseif (!empty($name)) {
            $channelName = $name;
        } else {
            $channelName = '无';
        }

        $counts = $this->RClassAccess->getClassEditHistoryCountByUid($student['uid']);

        $uid = empty($student) ? '' : $student['uid'];
        $nick = empty($student) ? '' : '(' . $student['nick'] . ')';

        return [$messageNew, $user, $uid, $channelName, $nick, $counts];
    }

    public function doAddMessage($content, $openId)
    {
        $link = $this->RChatAccess->countLinkInfoByOpenid($openId);

        if (empty($link) || $link['kefu_id'] == Yii::$app->user->identity->id) {
            $content = $this->map($content);

            $messages = $this->WChatAccess->addChatMessage($openId, $content, Yii::$app->user->identity->id);

            if (empty($link)) {
                $kefuInfo = $this->RChatAccess->getChatKefuInfo(Yii::$app->user->identity->id);

                $this->WChatAccess->updateChatLinkIsHide(Yii::$app->user->identity->id);

                $this->WChatAccess->doEditChatLinkStatus($openId, $kefuInfo['page_id'], Yii::$app->user->identity->id);

                $wait = $this->RChatAccess->getChatWaitInfo($openId);

                if (!empty($wait)) {
                    $this->WChatAccess->deleteChatWait($openId);
                }
            }

            return json_encode(array('error' => '', 'data' => array('content' => $content, 'message_id' => $messages, 'kefu_name' => Yii::$app->user->identity->nickname)));
        } else {
            $kefu = $this->RStudentAccess->getUserAccountById($link['kefu_id']);

            $user = $this->RStudentAccess->getUserInitByOpenId($openId);

            return json_encode(
                    array(
                        'error' => '该用户正在被客服 (' . $kefu['nickname'] . ') 接待,需要请求转接吗?',
                        'data' => array(
                            'link_id' => $link['id'],
                            'user_nick' => $user['name'],
                            'kefu_id' => Yii::$app->user->identity->id,
                            'kefu_name' => $kefu['nickname'],
                            'page_id' => $link['page_id'],
                    )));
        }
    }

    public function addMessageApi($content, $openId, $uid)
    {
        $returnData = array(
            'error' => 0,
            'data' => []
        );

        $link = $this->RChatAccess->countLinkInfoByOpenid($openId);

        if (empty($link) || $link['kefu_id'] == $uid) {
            $content = $this->map($content);
            $messages = $this->WChatAccess->addChatMessage($openId, $content, $uid);
            if (empty($link)) {
                $kefuInfo = $this->RChatAccess->getChatKefuInfo($uid);

                $this->WChatAccess->updateChatLinkIsHide($uid);

                $this->WChatAccess->addChatLink($openId, $kefuInfo['page_id'], $uid);

                $wait = $this->RChatAccess->getChatWaitInfo($openId);

                if (!empty($wait)) {
                    $this->WChatAccess->deleteChatWait($openId);
                }
            }

            $returnData['data'] = array(
                'message_id' => $messages
            );

            return $returnData;
        } else {
            $kefu = $this->RStudentAccess->getUserAccountById($link['kefu_id']);

            $this->RStudentAccess->getUserInitByOpenId($openId);

            $returnData['error'] = '该用户正在被客服 (' . $kefu['nickname'] . ') 接待';

            return $returnData;
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

    public function getCheckConnectCount($pageId, $uid)
    {
        $count = $this->RChatAccess->checkConnectCount($pageId, $uid);

        return empty($count) ? 1 : 0;
    }

    public function getGiveClassPage()
    {
        return $this->RClassAccess->getAllInstrumentList();
    }

    public function accessRight($waitId)
    {
        $wait = $this->RChatAccess->getChatWaitById($waitId);

        if (!empty($wait)) {
            $count = $this->RChatAccess->countLinkByOpenid($wait['open_id']);

            if (empty($count)) {
                $messages = $this->RChatAccess->getChatMessageAndInitInfo($wait['open_id']);

                $messageNew = [];
                if (!empty($messages)) {
                    $counts = count($messages);
                    for ($i = 0; $i < $counts; $i++) {
                        $messageNew[] = array_pop($messages);
                    }
                }

                $user = $this->RStudentAccess->getUserInitInfoByOpenid($wait['open_id']);

                $student = $this->RStudentAccess->getWeChatAccInfo($wait['open_id']);

                $this->WChatAccess->deleteChatWait($wait['open_id']);

                $kefu = $this->RChatAccess->getChatKefuInfo(Yii::$app->user->identity->id);

                $this->WChatAccess->editChatLinkSignHide($wait['open_id']);

                $this->WChatAccess->editChatLinkByKefu();

                $this->WChatAccess->updateChatLinkIsHide(Yii::$app->user->identity->id);

                $this->WChatAccess->addChatLink($wait['open_id'], $kefu['page_id'], Yii::$app->user->identity->id);

                $counts = $this->RClassAccess->getClassEditHistoryCountByUid($student['uid']);

                $uid = empty($student) ? '' : $student['uid'];

                $channel_name = empty($student) ? '' : $student['channel_name'];

                $nick = empty($student) ? '' : '(' . $student['nick'] . ')';

                return [$messageNew, $user, $uid, $channel_name, $nick, $counts];
            }
        }
    }

    public function checkAccess($waitId, $page)
    {
        switch ($page) {
            case 'new':
                $view = '/chat/new-user';
                break;
            case 'buy':
                $view = '/chat/other-user?type=3';
                break;
            case 'experience':
                $view = '/chat/other-user?type=2';
                break;
            case 'danger':
                $view = '/chat/other-user?type=4';
                break;
        }

        $wait = $this->RChatAccess->getChatWaitById($waitId);

        if (!empty($wait)) {
            $count = $this->RChatAccess->findNoHideClassLinkCount($wait['open_id']);

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

    public function getLink($openId)
    {
        $link = $this->RChatAccess->findClassLinkIdByOpenId($openId);

        return json_encode(array('error' => '', 'data' => array('link_id' => $link['id'])));
    }

    public function getTransferServer($kefuId)
    {
        return $this->RChatAccess->getWaitKefuList($kefuId);
    }

    public function doEditTransfer($linkId, $kefuId, $logid)
    {
        $linkInfo = $this->RChatAccess->getCountLinkByOpenid($linkId);

        $kefuInfo = $this->RChatAccess->getChatWaitByKefu($kefuId);

        $this->WChatAccess->editLinkInfoStatus($linkInfo['id']);

        $this->WChatAccess->editChatLinkByKefuId($kefuId, $linkInfo['open_id']);

        $this->WChatAccess->updateAllCountersByKefu($kefuId);

        $this->WChatAccess->addClassLinkBykefu($kefuId, $linkInfo['open_id'], $kefuInfo['page_id']);

        LogService::OutputLog($logid, 'update', '', '转接用户');

        return json_encode(
                array(
                    'error' => '',
                    'data' => array(
                        'open_id' => $linkInfo['open_id'],
                        'page_id' => $kefuInfo['page_id']
                )));
    }

    public function doEditTransferApi($linkId, $kefuId)
    {
        $returnData = array(
            'error' => 0,
            'data' => []
        );

        $linkInfo = $this->RChatAccess->getCountLinkByOpenid($linkId);

        $kefuInfo = $this->RChatAccess->getChatWaitByKefu($kefuId);

        $this->WChatAccess->editLinkInfoStatus($linkInfo['id']);

        $this->WChatAccess->editChatLinkByKefuId($kefuId, $linkInfo['open_id']);

        $this->WChatAccess->updateAllCountersByKefu($kefuId);

        $this->WChatAccess->addClassLinkBykefu($kefuId, $linkInfo['open_id'], $kefuInfo['page_id']);

        return $returnData;
    }

    public function sendWechat($request)
    {
        $data = [
            'touser' => $request['open_id'],
            'msgtype' => 'text',
            'text' => array('content' => $request['content']),
        ];

        $wechat = Yii::$app->wechat;

        if ($wechat->sendMessage($data)) {
            return json_encode(array('error' => ''));
        } else {
            if ($wechat->sendMessage($data)) {
                return json_encode(array('error' => ''));
            } else {
                $this->WChatAccess->editChatMessageFail($request['message_id']);

                return json_encode(array('error' => '发送失败,用户48小时未与公众号互动或者用户取消关注'));
            }
        }
    }

    public function sendWechatApi($openId, $message, $messageId)
    {
        $returnData = array(
            'error' => 0,
            'data' => []
        );

        $data = [
            'touser' => (string) $openId,
            'msgtype' => 'text',
            'text' => array('content' => $message),
        ];

        $wechat = Yii::$app->wechat;

        if ($wechat->sendMessage($data)) {
            return $returnData;
        } else {
            if ($wechat->sendMessage($data)) {
                return $returnData;
            } else {
                $this->WChatAccess->editChatMessageFail($messageId);

                $returnData['error'] = '发送失败,用户48小时未与公众号互动或者用户取消关注';

                return $returnData;
            }
        }
    }

    public function offChat($linkId, $logid)
    {
        $link = $this->RChatAccess->offChatInfo($linkId);

        if (!empty($link)) {
            $this->WChatAccess->editLinkInfoStatus($link['id']);

            LogService::OutputLog($logid, 'off', serialize($linkId), '断开指定用户连接');

            $data = array('error' => '');
        } else {
            $data = array('error' => '您跟当前用户没有连接');
        }

        return json_encode($data);
    }

    // public function sendTemplate($request)
    // {
    //     $user = $this->RStudentAccess->getUserInitInfoByOpenid($request['open_id']);
    //     $templateId = Yii::$app->params['student_template_kefu_message'];
    //     $firstValue = "您有一条新消息";
    //     $key1word = array('value' => $user['name']);
    //     $key2word = array('value' => 'VIP陪练客服');
    //     $key3word = array('value' => $request['content']);
    //     $key4word = array('value' => date('Y-m-d H:i', time()));
    //     $remark = "回复本条消息我们的客服才能联系到您哦";
    //     $message = $this->build2Message($request['open_id'], $templateId, $firstValue, $key1word, $key2word, $key3word, $key4word, $remark);
    //     $wechat = Yii::$app->wechat;
    //     $wechat->sendTemplateMessage($message);
    //     $content = $this->map($request['content']);
    //     $message = new ChatMessage();
    //     $message->open_id = $request['open_id'];
    //     $message->kefu_id = Yii::$app->user->identity->id;
    //     $message->message = $content . ' (模版消息)';
    //     $message->type = 1;
    //     $message->tag = 1;
    //     $message->time_created = time();
    //     $message->save();
    //     LogService::OutputLog($this->logid,'send',serialize($request),'发送模板消息');
    //     return true;
    // }


//    private function getStudentFixTimeBit($timeHead, $timeFoot, $classType)
//    {
//        $index = 2 * $timeHead + ($timeFoot === '00' ? 0 : 1);
//        $num = pow(2, $index);
//        $num += ($classType == 1 ? 0 : pow(2, $index + 1));
//        return $num;
//    }

//    private function build2Message($openid, $templateId, $firstValue, $key1word, $key2word, $key3word, $key4word, $remark)
//    {
//        $data = array(
//            'first' => array('value' => $firstValue),
//            'keyword1' => $key1word,
//            'keyword2' => $key2word,
//            'keyword3' => $key3word,
//            'keyword4' => $key4word,
//            'remark' => array('value' => $remark)
//        );
//
//        $message = array(
//            'touser' => $openid,
//            'template_id' => $templateId,
//            'url' => '',
//            'data' => $data
//        );
//        return $message;
//    }

    public function closeSocket($pageId)
    {

        if (!empty($pageId)) {
            $kefu = $this->RChatAccess->getChatWaitKefuByPageId($pageId);

            if (!empty($kefu)) {
                $link = $this->RChatAccess->getChatMessagePreByPageId($pageId);

                $this->WChatAccess->deleteChatWaitKefu($kefu['id']);

                $this->WChatAccess->updateChatConnectByPageId($pageId);

                if (!empty($link)) {
                    foreach ($link as $openId) {
                        $type = $this->getUserType($openId);
                        $this->WChatAccess->addChatWait($openId, $type);
                    }
                }
            }
        }
        return true;
    }

    public function doAlertLog($request)
    {
//        $response = new ResponseLog();

        foreach ($request['message'] as $row) {
            $this->WChatAccess->addResponseLog($row['openid'], $row['name']);
        }

        return true;
    }

    /**
     * 生成链接
     */
    public function addSendUrl($openId)
    {
        return $this->WChatAccess->addSendUrl($openId);
    }

    /**
     * 查询金蛋数据
     */
    public function getGoldEge($openId)
    {
        return $this->RChatAccess->getGoldEge($openId);
    }

    /**
     * 快捷回复
     */
    public function getQuickAnswerList($type)
    {
        return $this->RChatAccess->getQuickAnswerList($type);
    }

    /**
     * 添加快捷回复
     */
    public function addQuickAnswer($content, $type, $logid)
    {
        LogService::OutputLog($logid, 'insert', '', '添加快捷回复');

        $id = $this->WChatAccess->addQuickAnswer($type, $content);
        if ($id > 0) {
            $data = array('id' => $id);
            return ['error' => 0, 'data' => $data];
//            return Json::dieJson(true);
        }

        return ['error' => '操作失败', 'data' => ''];
    }

    public function doEditQucikAnswer($qid, $content)
    {
        $kefu_id = Yii::$app->user->identity->id;
        $kefu_role = $this->RAccountAccess->getKefuRoleByKefuid($kefu_id);

        if ($kefu_role != 2) {
            return ['error' => '权限不足', 'data' => ''];
        }

        if ($this->WChatAccess->doEditQucikAnswer($qid, $content)) {
            return ['error' => 0, 'data' => ''];
        } else {
            return ['error' => '编辑失败', 'data' => ''];
        }
    }

    public function doDeleteQucikAnswer($qid)
    {
        $kefu_id = Yii::$app->user->identity->id;
        $kefu_role = $this->RAccountAccess->getKefuRoleByKefuid($kefu_id);

        if ($kefu_role != 2) {
            return ['error' => '权限不足', 'data' => ''];
        }

        if ($this->WChatAccess->doDeleteQucikAnswer($qid)) {
            return ['error' => 0, 'data' => ''];
        } else {
            return ['error' => '删除失败', 'data' => ''];
        }
    }

    public function getChatIndex()
    {
        $count = $this->RChatAccess->getNewChatWaitCount();
        $pagination = new Pagination([
            'defaultPageSize' => 5,
            'totalCount' => $count,
        ]);

        $users = $this->RChatAccess->getNewChatWaitInfo($pagination->offset, $pagination->limit);

        $left = $this->RChatAccess->getChatLinkNoHideInfo();

        $page = 'new';

        return [$left, $users, $pagination, $page];
    }

    public function getNewUser($from, $offset, $limit)
    {
//        $count = $this->RChatAccess->getNewChatWaitCount();
//
//        $pagination = new Pagination([
//            'defaultPageSize' => 3,
//            'totalCount' => $count,
//        ]);

        $users = $this->RChatAccess->getNewChatWaitInfo($offset, $limit);
        foreach ($users as &$v) {
            $v['kefu_nick'] = $this->RAccountAccess->getKefuNickByOpenId($v['openid']);
        }
        $data = array(
            'users' => $users,
        );

        return ['error' => 0, 'data' => $data];
    }

    public function getNewUserCount()
    {
        $count = $this->RChatAccess->getNewChatWaitCount();
        $data = array(
            'count' => $count,
        );

        return ['error' => 0, 'data' => $data];
    }

    public function getOtherUserCount($type)
    {
        $count = $this->RChatAccess->getOtherChatWaitCount($type);

        switch ($type) {
            case 2:
                $view = 'experience-page';
                break;
            case 3:
                $view = 'buy-page';
                break;
            case 4:
                $view = 'danger-page';
                break;
        }

        $data = array(
            'count' => $count,
            'view' => $view
        );

        return ['error' => 0, 'data' => $data];
    }

    public function getOtherUser($type, $offset, $limit)
    {
//        $count = $this->RChatAccess->getOtherChatWaitCount($type);
//
//        $pagination = new Pagination([
//            'defaultPageSize' => 5,
//            'totalCount' => $count,
//        ]);

        $users = $this->RChatAccess->getOtherChatWaitInfo($offset, $limit, $type);

        foreach ($users as &$row) {
            $row['instrument_level'] = $this->RStudentAccess->getInstrumentLevelByStudentId($row['student_id']);

            $row['kefu_nick'] = $this->RAccountAccess->getKefuNickByOpenId($row['openid']);

            if ($type != 2) {
                $re_kefu = $this->RAccountAccess->getReKefuNickByOpenId($row['openid']);
                if (!empty($re_kefu)) {
                    $row['kefu_nick'] = $re_kefu;
                }
            }
        }

        switch ($type) {
            case 2:
                $view = 'experience';
                break;
            case 3:
                $view = 'buy';
                break;
            case 4:
                $view = 'danger';
                break;
        }

        $data = array(
            'users' => $users,
            'page' => $view
        );

        return ['error' => 0, 'data' => $data];
    }

    public function getLoadMoreInfo($offset)
    {
        $link = $this->RChatAccess->getChatLinkIsCurrentPage();

        $messages = $this->RChatAccess->getChatMessageInfoByOpenId($link['open_id'], $offset);

        foreach ($messages as &$row) {
            if (empty($row['kefu_head'])) {
                $row['kefu_head'] = '/images/head_default.jpg';
            }
        }

        return json_encode($messages, JSON_UNESCAPED_SLASHES);
    }

    /*
      public function getSendTemplate($request,$logid)
      {

      $user = $this->RStudentAccess->getUserInitByOpenId($request['open_id']);

      $templateId = Yii::$app->params['student_template_kefu_message'];

      $firstValue = "您有一条新消息";
      $key1word = array('value' => $user['name']);
      $key2word = array('value' => 'VIP陪练客服');
      $key3word = array('value' => $request['content']);
      $key4word = array('value' => date('Y-m-d H:i', time()));
      $remark = "回复本条消息我们的客服才能联系到您哦";

      $message = $this->build2Message($request['open_id'], $templateId, $firstValue, $key1word, $key2word, $key3word, $key4word, $remark);

      $wechat = Yii::$app->wechat;

      $wechat->sendTemplateMessage($message);

      $content = $this->map($request['content']);

      $this->WChatAccess->addChatMessageMould($request['open_id'], $content);

      LogService::OutputLog($logid,'send',serialize($request),'发送模板消息');

      return true;
      }
     */

    public function getCheckTalk($openId)
    {
        $link = $this->RChatAccess->getChatLinkByOpenId($openId);
        if (empty($link)) {
            $data = array('error' => '');
        } else {
            $kefu = $this->RStudentAccess->getUserAccountById($link['kefu_id']);

            $user = $this->RStudentAccess->getUserInitByOpenId($openId);

            $data = array(
                'error' => '该用户正在被客服 (' . $kefu['nickname'] . ') 接待,需要请求转接吗?',
                'data' => array(
                    'link_id' => $link['id'],
                    'user_nick' => $user['name'],
                    'kefu_id' => Yii::$app->user->identity->id,
                    'kefu_name' => $kefu['nickname'],
                    'page_id' => $link['page_id'],
            ));
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public function getAccessTalk($openId)
    {
        $count = $this->RChatAccess->countLinkByOpenid($openId);
        if (empty($count)) {
            $messages = $this->RChatAccess->getChatMessageAndInitInfo($openId);
            $messageNew = [];
            if (!empty($messages)) {
                $counts = count($messages);
                for ($i = 0; $i < $counts; $i++) {
                    $messageNew[] = array_pop($messages);
                }
            }
            $user = $this->RStudentAccess->getUserInitInfoByOpenid($openId);
            $student = $this->RStudentAccess->getWeChatAccInfo($openId);
            $kefu = $this->RChatAccess->getChatKefuInfo(Yii::$app->user->identity->id);

            $this->WChatAccess->editChatLinkSignHide($openId);
            $this->WChatAccess->editChatLinkByKefu();
            $this->WChatAccess->updateChatLinkIsHide(Yii::$app->user->identity->id);

            $this->WChatAccess->addChatLink($openId, $kefu['page_id'], Yii::$app->user->identity->id);

            $counts = $this->RClassAccess->getClassEditHistoryCountByUid($student['uid']);

            $uid = empty($student) ? '' : $student['uid'];

            $channel_name = empty($student) ? '' : $student['channel_name'];

            $nick = empty($student) ? '' : '(' . $student['nick'] . ')';

            return [$messageNew, $user, $uid, $channel_name, $nick, $counts];
        }

        return 0;
    }

    public function dealChatImage($xml)
    {
        if ($xml['MediaId'] == '') {
            return true;
        }

        try {
            $bucket = Yii::$app->params['vip_static_bucket'];

            $img = @file_get_contents($xml['PicUrl']);

            if (!$img) {
                return true;
            }

            $filePathFrom = '/tmp/' . $xml['MediaId'] . '.jpeg';
            $filePathTo = 'chat/image/' . md5($xml['MsgId'] . '_' . microtime() . '_' . rand(10, 99));
            file_put_contents($filePathFrom, $img);

            if (!QiniuService::uploadToQiniu($bucket, $filePathTo, $filePathFrom)) {
                return;
            }

            $counts = $this->RStudentAccess->countUserInitByOpenid($xml['FromUserName']);

            if (!empty($counts)) {
                $this->WChatAccess->addChatMessagePre($xml['FromUserName'], $filePathTo, 2);
            }

            unlink($filePathFrom);

            return true;
        } catch (Exception $e) {
            return true;
        }
    }

    public function dealChatVoice($xml)
    {
        $bucket = Yii::$app->params['vip_video_bucket'];
        $wechat = Yii::$app->wechat;

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

        $counts = $this->RStudentAccess->countUserInitByOpenid($xml['FromUserName']);

        if (!empty($counts)) {
            $this->WChatAccess->addChatMessagePre($xml['FromUserName'], $filePathTo, 3);
        }

        unlink($filePathFrom);

        return true;
    }

    public function dealChatEmoji($xml)
    {

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

        $counts = $this->RStudentAccess->countUserInitByOpenid($xml['FromUserName']);

        if (!empty($counts)) {
            $this->WChatAccess->addChatMessagePre($xml['FromUserName'], $newStr, 1);
        }

        return true;
    }

    public function dealConnectEvent($server, $data, $fd)
    {
        $pageId = $this->RChatAccess->getPageId($data['kefu_id']);

        if (!empty($pageId)) {
            $dataSend = $this->getCloseData();
            $this->safePush($server, $pageId, $dataSend);
        }

        $this->WChatAccess->addKefuWait($data['kefu_id'], $fd);
    }

    public function dealAccessEvent($server, $data, $fd)
    {
        $dataSend = $this->RChatAccess->getAccessMessage($data['open_id']);

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

            $this->WChatAccess->addMessage($messageList, $data);
            $this->WChatAccess->deleteMessagePre($messagePreIdList);
        }
    }

    public function dealViewEvent($server, $data, $fd)
    {
        $isConnect = $this->RChatAccess
                ->checkIsConncet($data['kefu_id'], $data['open_id'], $fd);

        $isConnect = empty($isConnect) ? false : true;

        if ($isConnect) {
            $dataSend = $this->RChatAccess->getAccessMessage($data['open_id']);

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
                $this->WChatAccess->addMessage($messageList, $data);
                $this->WChatAccess->deleteMessagePre($messagePreIdList);
            }
        }
    }

    public function dealTimerEvent($server, $data, $fd)
    {
        /**
         * push head
         */
        $allKefu = $this->RChatAccess->getAllKeFu();

        if (!empty($allKefu)) {
            foreach ($allKefu as $kefu) {
                $headData = $this->getHeadData($data['head']);

                $this->safePush($server, $kefu['page_id'], $headData);
            }
        }

        /**
         * push sales
         */
        if (!empty($data['sales'])) {
            foreach ($data['sales'] as $sales) {
                $salesData = $this->getSalesData($sales);
                $this->safePush($server, $sales['page_id'], $salesData);
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
                $dataSend = $this->RChatAccess->getAccessMessage($body['open_id']);
                if (!empty($dataSend)) {
                    $messagePreIdList = [];
                    $messageList = [];
                    foreach ($dataSend as &$message) {
                        $message['date'] = date('m/d H:i', $message['time_created']);
                        $dataPush = $this->getBodyData($message);
                        $isSuccess = $this->safePush($server, $body['page_id'], $dataPush);

                        if ($isSuccess) {
                            $messagePreIdList[] = $message['id'];
                            $messageList[] = $message;
                        }
                    }
                    $this->WChatAccess->addMessage($messageList, $body);
                    $this->WChatAccess->deleteMessagePre($messagePreIdList);
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
            $onlineKefu = $this->RChatAccess->getOnlineKefu();

            foreach ($onlineKefu as $alert) {
                if ($alert['role'] == 4) {
                    if (!empty($data['alert']['purchase'])) {
                        $alertData = $this->getAlertData($data['alert']['purchase']);
                        $this->safePush($server, $alert['page_id'], $alertData);
                    }
                } elseif ($alert['role'] == 1) {
                    if (!empty($data['alert']['new'])) {
                        $alertData = $this->getAlertData($data['alert']['new']);
                        $this->safePush($server, $alert['page_id'], $alertData);
                    }
                } else {
                    if (!empty($data['alert']['all'])) {
                        $alertData = $this->getAlertData($data['alert']['all']);
                        $this->safePush($server, $alert['page_id'], $alertData);
                    }
                }
            }
            LogService::addAlertLog($data['alert']['all']);
        }
    }

    public function dealTansferEvent($server, $data, $fd)
    {
        $name = $this->RStudentAccess->getUserName($data['open_id']);
        $message = $this->getTransferData($name);
        $this->safePush($server, $data['page_id'], $message);
        return true;
    }

    public function dealRtransferEvent($server, $data, $fd)
    {
        $message = $this->getRtransferData($data, $fd);
        $this->safePush($server, $data['page_id'], $message);

        return true;
    }

    public function dealRefuseTransferEvent($server, $data, $fd)
    {
        $message = $this->getRefuseData();
        $this->safePush($server, $data['page_id'], $message);

        return true;
    }

    public function doClose($pageId)
    {
        $openIds = $this->RChatAccess->getConnectedOpenid($pageId);

        if (!empty($openIds)) {
            foreach ($openIds as $openId) {
                $type = $this->getUserType($openId);
                $this->WChatAccess->addChatWait($openId, $type);
            }
        }

        $this->WChatAccess->deleteChatWaitKefuByPage($pageId);
        $this->WChatAccess->disconnectByPageId($pageId);

        return true;
    }

    public function getUserType($openId)
    {
        $userId = $this->RStudentAccess->getUidByOpenid($openId);

        if (!empty($userId)) {
            $isDanger = $this->RStudentAccess->getStudentIsDanger($userId);

            if (empty($isDanger)) {
                $isBuy = $this->RClassAccess->getStudentIsBuy($userId);

                return empty($isBuy) ? 2 : 3;
            } else {
                return 4;
            }
        } else {
            return 1;
        }
    }

    private function getAccessData($message)
    {
        return [
            'event' => 'ACCESS',
            'message' => $message,
        ];
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

    private function getSalesData($sales)
    {
        return [
            'event' => 'SALES',
            'message' => $sales,
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

    public function doSendImage($openId, $file, $logid)
    {
        $accessKey = Yii::$app->params['qiniuAccessKey'];
        $secretKey = Yii::$app->params['qiniuSecretKey'];

        if ($file['file']["error"] > 0) {
            return 0;
        } else {
            $fileKey = md5($openId . '_' . microtime() . '_' . rand(10, 99));

            // 构建鉴权对象
            $auth = new Auth($accessKey, $secretKey);

            // 要上传的空间
            $bucket = Yii::$app->params['vip_static_bucket'];

            // 生成上传 Token
            $token = $auth->uploadToken($bucket);

            //$filePath = $file["tmp_name"];
            $filePath = $file['file']['tmp_name'];

            // 上传到七牛后保存的文件名
            $key = 'chat/image/' . $fileKey;

            // 构建 UploadManager 对象
            $uploadMgr = new UploadManager();

            // 调用 UploadManager 的 putFile 方法进行文件的上传
            list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
            if (empty($ret)) {
                //为了代码检测
            }
            if ($err !== null) {
                return 0;
            } else {
                $this->WChatAccess->addChatMessageInfo($openId, $key);
                //发送微信
                $wechat = Yii::$app->wechat;

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

                //unlink($imgPath);

                LogService::OutputLog($logid, 'send', serialize($openId), '发送图片');

                return json_encode(array('url' => Yii::$app->params['vip_static_path'] . $key), JSON_UNESCAPED_SLASHES);
            }
        }
    }

    public function doSendHaibao($openId, $fath, $logid)
    {
        $accessKey = Yii::$app->params['qiniuAccessKey'];
        $secretKey = Yii::$app->params['qiniuSecretKey'];

        $fileKey = md5($openId . '_' . microtime() . '_' . rand(10, 99));

        // 构建鉴权对象
        $auth = new Auth($accessKey, $secretKey);

        // 要上传的空间
        $bucket = Yii::$app->params['vip_static_bucket'];

        // 生成上传 Token
        $token = $auth->uploadToken($bucket);

        $filePath = $fath;
        //var_dump($filePath);die();
        // 上传到七牛后保存的文件名
        $key = 'chat/image/' . $fileKey;

        // 构建 UploadManager 对象
        $uploadMgr = new UploadManager();

        // 调用 UploadManager 的 putFile 方法进行文件的上传
        list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
        if (empty($ret)) {
            //为了代码检测
        }

        if ($err !== null) {
            return 0;
        } else {
            $this->WChatAccess->addChatMessageInfo($openId, $key);
            //发送微信
            $wechat = Yii::$app->wechat;

            $img = imagecreatefromjpeg($filePath);
            $imgPath = '/tmp/' . rand(10, 99) . '.jpg';
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
            unlink($fath);

            LogService::OutputLog($logid, 'send', serialize($openId), '发送海报');

            return json_encode(
                    array(
                'url' => Yii::$app->params['vip_static_path'] . $key,
                'head' => Yii::$app->user->identity->head,
                'date' => date('Y-m-d H:i', time())
                    ), JSON_UNESCAPED_SLASHES);
        }
    }

    public function getChattingUserById($uid, $keyword)
    {
        $returnData = array(
            'error' => 0,
            'data' => []
        );

        $data = $this->RChatAccess->getChattingUserById($uid, $keyword);

        foreach ($data as &$row) {
            $row['channel_name'] = $this->getChannelNameById($row['sales_id'], $row['channel_id']);
            $row['user_type'] = $this->getUserType($row['open_id']);
        }

        $returnData['data'] = $data;

        return $returnData;
    }

    public function getWaitingUser($kefuId, $keyword, $offset, $limit)
    {
        $returnData = array(
            'error' => 0,
            'data' => []
        );

        if (!empty($kefuId)) {
            $role = $this->RAccountAccess->getKefuRoleByKefuid($kefuId);

            if ($role == 1) {
                $data = $this->RChatAccess->getWaitingUserBySaleId($kefuId, $keyword, $offset, $limit);
            } elseif ($role == 4) {
                $data = $this->RChatAccess->getWaitingUserByPurchaseId($kefuId, $keyword, $offset, $limit);
            } else {
                $returnData['error'] = '销售类型错误,请勿选择管理者';
                return $returnData;
            }
        } else {
            $data = $this->RChatAccess->getWaitingUser($keyword, $offset, $limit);
        }

        foreach ($data as &$row) {
            $row['channel_name'] = $this->getChannelNameById($row['sales_id'], $row['channel_id']);
            $row['kefu_name'] = empty($row['kefu_name']) ? '无' : $row['kefu_name'];
            $row['kefu_name_re'] = empty($row['kefu_name_re']) ? '无' : $row['kefu_name_re'];
        }

        $returnData['data'] = $data;

        return $returnData;
    }

    public function getHistoryMessageByOpenId($openId, $offset, $limit)
    {
        $returnData = array(
            'error' => 0,
            'data' => []
        );

        $data = $this->RChatAccess->getHistoryMessageByOpenId($openId, $offset, $limit);

        foreach ($data as &$row) {
            if ($row['type'] == 2) {
                $row['message'] = Yii::$app->params['vip_static_path'] . $row['message'];
            } elseif ($row['type'] == 3) {
                $row['message'] = Yii::$app->params['vip_video_path'] . $row['message'];
            }
        }

        $returnData['data'] = $data;

        return $returnData;
    }

    public function getPoster($openId, $cid, $posterId = 0)
    {
        $poster = $this->RChannelAccess->getChannelPoster($openId);
        if (empty($poster)) {
            return json_encode([
                'error' => '用户没有二维码',
                'data' => ''
            ]);
        }
        $posterInfo = $this->RChannelAccess->getAllPoster($posterId);

        $imgUrl = Yii::$app->params['pnl_static_path'] . $poster;

        $jpgName = 'tmp/' . uniqid('poster_') . '.jpg';
        $qrcodeImage = imagecreatefromjpeg($imgUrl);
        $qrcodeImageResized = imagecreate(150, 150);
        imagecopyresampled($qrcodeImageResized, $qrcodeImage, 0, 0, 0, 0, 150, 150, 430, 430);

        $posterImage = imagecreatefromjpeg(Yii::$app->params['vip_static_path'] . $posterInfo['path']);

        imagecopy($posterImage, $qrcodeImageResized, 467, 867, 0, 0, 150, 150);

        imagejpeg($posterImage, $jpgName, 100);
        //unlink($jpgName);
        return json_encode([
            'error' => '',
            'data' => $jpgName
        ]);
    }

    private function getChannelNameById($salesId, $channelId)
    {
        if (!empty($salesId)) {
            $name = $this->RChannelAccess->getSalesChannelNameById($salesId);
        } elseif (!empty($channelId)) {
            $channelInfo = $this->RChannelAccess->getUserChannelInfoById($channelId);

            if ($channelInfo['type'] == 5) {
                $name = '[活动] ' . $channelInfo['name'];
            } elseif ($channelInfo['type'] == 2) {
                $this->RStudentAccess->getUserIdByChannelIdSelf($channelInfo['id']);
                $name = '[家长] ' . $channelInfo['name'];
            } else {
                $name = '[其他] ' . $channelInfo['name'];
            }
        } else {
            $name = '无';
        }

        return $name;
    }

    public function setPage($linkId)
    {
        $returnData = array(
            'error' => 0,
            'data' => []
        );

        if (!$this->WChatAccess->updateCurrentPage($linkId)) {
            $returnData['error'] = '设置当前页失败';
        }

        return $returnData;
    }

    public function outPage($linkId)
    {
        $returnData = array(
            'error' => 0,
            'data' => []
        );

        if (!$this->WChatAccess->updateCurrentPageOut($linkId)) {
            $returnData['error'] = '设置当前页失败';
        }

        return $returnData;
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

    public function getAllPoster()
    {
        return $this->RChannelAccess->getAllPoster();
    }

    public function getWelfareCard($openId)
    {
        $name = '';
        $poster = $this->RChannelAccess->getChannelPoster($openId);
        if (empty($poster)) {
            return json_encode([
                'error' => '用户没有二维码',
                'data' => ''
            ]);
        }
        $userinfo = $this->RChannelAccess->getChannelUserByOpenid($openId);
        if ($userinfo) {
            $name = $userinfo['nickname'] ? $userinfo['nickname'] : '';
            $name = $name ? $name : $userinfo['wechat_name'];
        }
        $path = 'images/welfare-card.jpg';
        $imgUrl = Yii::$app->params['pnl_static_path'] . $poster;
        $jpgName = 'tmp/' . uniqid('welf_') . '.jpg';
        $qrcodeImage = imagecreatefromjpeg($imgUrl);
        $qrcodeImageResized = imagecreate(300, 300);
        imagecopyresampled($qrcodeImageResized, $qrcodeImage, 0, 0, 8, 0, 300, 300, 400, 400);

        $posterImage = imagecreatefromjpeg($path);

        ImageTTFText($posterImage, 48, 0, 190, 100, '000000', 'fonts/PingFang-Bold.ttf', $name);
        imagecopy($posterImage, $qrcodeImageResized, 642, 145, 0, 0, 300, 300);

        imagejpeg($posterImage, $jpgName, 100);
        //unlink($jpgName);
        return json_encode([
            'error' => '',
            'data' => $jpgName
        ]);
    }

    public function getIntroducePage($where = array())
    {
        return $this->RChannelAccess->getIntroducePage($where);
    }

    public function getKefuIdByBindOpenid($openId)
    {
        $userinfo = $this->RChannelAccess->getChannelUserByOpenid($openId);
        return $userinfo ? $userinfo['kefu_id'] : '';
    }

    public function createTemporaryQrcode($userid)
    {
        $num = 10000000;
        $senseid = $num + $userid;
        $wechat = Yii::$app->wechat_new;
        $qrcode = $wechat->createQrCode([
            'expire_seconds' => 2592000,
            'action_name' => 'QR_SCENE',
            'action_info' => ['scene' => ['scene_id' => $senseid]]
        ]);
        return $wechat->getQrCode($qrcode['ticket']);
    }

    public function getTemporaryPoster($picurl)
    {
        if (empty($picurl)) {
            return json_encode([
                'error' => '微信生成二维码失败',
                'data' => ''
            ]);
        }
        $poserUrl = 'images/temporary.jpg';
        $qrcode = $picurl;
        $jpgName = 'tmp/' . uniqid('tmpqr_') . '.jpg';
        $qrcodeImage = imagecreatefromjpeg($qrcode);
        $qrcodeImageResized = imagecreate(150, 150);
        imagecopyresampled($qrcodeImageResized, $qrcodeImage, 0, 0, 0, 0, 150, 150, 430, 430);

        $posterImage = imagecreatefromjpeg($poserUrl);
        imagecopy($posterImage, $qrcodeImageResized, 467, 867, 0, 0, 150, 150);

        imagejpeg($posterImage, $jpgName, 100);

        return json_encode([
            'error' => '',
            'data' => $jpgName
        ]);
    }

    public function openSuperClass($openid)
    {
        //获取全部课程
        $classinfo = $this->RChannelAccess->getAllClass();
        $usershare = $this->RChannelAccess->getUserShare($openid);
        $user = $this->RChannelAccess->getChannelUserByOpenid($openid);

        $insertData = [];

        for ($i = 0; $i < count($classinfo); $i++) {
            if (!in_array($classinfo[$i]["id"], $usershare)) {
                $insertData[] = [$classinfo[$i]["id"], $openid, time(), 1, $classinfo[$i]["is_back"], $user["id"]];
            }
        }
        if (empty($insertData)) {
            $returnData = [
                'error' => 0,
                'data' => 0
            ];
            return json_encode($returnData);
        }

        //一键开启所有课程
        $result = $this->WChannelAccess->saveSuperClass($insertData);

        if (!empty($result)) {
            $returnData = [
                'error' => 0,
                'data' => $result
            ];
            $power = $this->WChannelAccess->getChannelAuth($openid);
            if (empty($power)) {
                $returnData["error"] = "授权失败";
            }
            return json_encode($returnData);
        } else {
            $returnData = [
                'error' => "开启权限失败",
                'data' => $result
            ];
            return json_encode($returnData);
        }
    }
}
