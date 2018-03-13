<?php

namespace app\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use callmez\wechat\sdk;
use app\models\ErrorLogBean;
use common\widgets\Json;

class ChatController extends BaseController
{
    /** @var  \common\logics\chat\ChatLogic $chatService */
    private $chatService;
    /** @var  \common\logics\classes\ClassesLogic $classesService */
    private $classesService;
    /** @var  \common\logics\channel\ChannelLogic $channelService */
    private $channelService;
    /** @var  \common\logics\chat\ChannelChatLogic $channelChatService */
    private $channelChatService;
    /** @var  \common\logics\push\TemplateLogic $templateService */
    private $templateService;
    /** @var  \common\logics\push\MessageLogic $messageService */
    private $messageService;
    /** @var  \common\logics\push\AccountLogic $accountService */
    private $accountService;



    public function init()
    {
        $this->chatService = Yii::$container->get('chatService');
        $this->classesService = Yii::$container->get('classesService');
        $this->channelService = Yii::$container->get('channelService');
        $this->channelChatService = Yii::$container->get('channelChatService');
        $this->templateService = Yii::$container->get('templateService');
        $this->messageService = Yii::$container->get('messageService');
        $this->accountService = Yii::$container->get('accountService');

        parent::init();
    }


    /**
     * 查询历史接待信息
     */
    public function actionLeftUser($isHistory = 0, $keyword = '')
    {
        $users = $this->chatService->getChannelLeftUserInfo($isHistory, $keyword);

        return $this->renderPartial('left', [
            'users' => $users,
        ]);
    }

    /**
     * 点击左边头像
     */
    public function actionLinkRight($linkId = 0)
    {
        $user = $this->channelChatService->clickChannelLinkRight($linkId);
        return $this->renderPartial('chat', [
            'messages' => $user['data']['messageNew'],
            'user' => $user['data']['user'],
            'userId' => $user['data']['user_id'],
            'mobile' => $user['data']['mobile'],
            'kefu_name' => $user['data']['kefu_name'],
        ]);
    }


    /**
     * 课时记录页面
     * @param  $student_id
     * @return array
     */
    public function actionClassHistoryPage($studentId)
    {
        $count = $this->classesService->getClassHistoryPage($studentId);

        return $this->renderPartial('class-history-page', [
            'totalCount' => $count,
        ]);
    }

    /**
     * 课时记录列表
     */
    public function actionUserInfo($userId)
    {
//        $list = $this->chatService->getClassHistoryList($student_id, $num);

        return $this->renderPartial('user-info', [
//            'list' => $list,
        ]);
    }

    public function actionPosterPage($openId, $userId)
    {
        $introduce_page = $this->chatService->getIntroducePage();
        $posters = $this->chatService->getAllPoster();
        $kefu_id = $this->chatService->getKefuIdByBindOpenid($openId);
        $userAccount = $this->accountService->getUserAccountDetailById($kefu_id);
        $data=[
            'posters' => $posters,
            'open_id' => $openId,
            'user_id' => $userId,
            'kefu_id' => $kefu_id,
            'account' => $userAccount,
            'introduce_page' =>$introduce_page
        ];
        return $this->renderPartial('poster', $data);
    }

    public function actionChannelPosterPage($openId = 0, $userId = 0)
    {
        $openId = Yii::$app->request->post('open_id');
        $userId = intval(Yii::$app->request->post('user_id'));
        $poster_id = intval(Yii::$app->request->post('poster_id'));
        return $this->chatService->getPoster($openId, $userId, $poster_id);
    }

    /*
     * 获取完成课，取消体验课，关注未预约列表总数
     * create by sjy
     */
    public function actionClassRecordList($saleId, $keyword = '', $type = '', $start = 0, $end = 0)
    {
        if ($start != 0 && $end != 0) {
            $start = strtotime($start);
            $end = strtotime($end) + 86400;
        } else {
            $start = strtotime(date('Y-m-d 00:00:00', time())) - 30 * 24 * 60 * 60;
            $end = strtotime(date('Y-m-d 00:00:00', time())) + 86400;
        }
        $count = $this->classesService->getClassTimeBySaleIdCount($saleId, $keyword, $type, $start, $end);
        $count = $count['data'];
        return $this->renderPartial('class-record-list', [
            'saleId' => $saleId,
            'count' => $count
        ]);
    }
    
    /*
     * 获取完成课，取消体验课，关注未预约列表
     * create by sjy
     */
    public function actionClassRecordPage($saleId, $keyword = '', $type = 1, $num = 0, $start = 0, $end = 0)
    {
        if ($start != 0 && $end != 0) {
            $start = strtotime($start);
            $end = strtotime($end) + 86400;
        } else {
            $start = strtotime(date('Y-m-d 00:00:00', time())) - 30 * 24 * 60 * 60;
            $end = strtotime(date('Y-m-d 00:00:00', time())) + 86400;
        }
        
        $data = $this->classesService->getClassTimeBySaleId($saleId, $keyword, $type, $num, $start, $end);
        return $this->renderPartial('class-record-page', [
            'list' => $data['data']['list'],
            'type' => $data['data']['type'],
            'keyword' => $data['data']['keyword']
        ]);
    }
    
    /*
     * 发送取消体验课客服消息
     * create by sjy
     */
    public function actionSendCancelexRecord($classId, $saleId)
    {
        $data = $this->messageService->sendCancelexRecord($classId, $saleId);
        
//        if (empty($data["error"])) {
//            $save_info = $this->channelChatService->doSaveChatMessage($data['data']['user_id'], $data['data']['message']);
//            return json_encode($save_info);
//        } else {
//            return json_encode(['error' => '不能推送客服消息', 'data' => '']);
//        }
        if (empty($data["error"])) {
            return json_encode(['error' => 0, 'data' => $data['data']["message"]]);
        } else {
            return json_encode(['error' => '不能推送客服消息', 'data' => '']);
        }
    }
    /*
     * 发送取消体验课的模板消息
     * create by sjy
     */
    public function actionSendCancelexTemplet($classId, $saleId)
    {
        $data = $this->messageService->sendCancelexTemplet($classId, $saleId);
        if (empty($data["error"])) {
            $save_info = $this->channelChatService->doSaveChatMessage($data['data']['user_id'], $data['data']['message']);
            return json_encode($save_info);
        } else {
            return json_encode(['error' => '发送模板消息失败', 'data' => '']);
        }
    }

    /**
     * 发送课单之发送模板信息
     * create by  wangkai
     */
    public function actionSendClassRecord($classId)
    {
        $data = $this->messageService->sendClassRewardMessage($classId );

        if ($data['error'] == 0) {
            $save_info = $this->channelChatService->doSaveChatMessage($data['data']['user_id'], $data['data']['message']);
            return json_encode($save_info);
        }
    }
    
    /**
     * 发送关注未预约链接
     * create by  wangkai
     */
    public function actionSendNoexRecord($saleId, $keyword, $start, $end)
    {
        $keyword = empty($keyword) ? ' ' : $keyword;
//        $start = str_replace('/', '-', $start);
//        $end = str_replace('/', '-', $end);
        $chaanelurl = Yii::$app->params['channel_frontend_url'];
        $content = '老师您好，这是您推荐后未预约体验课学生的名单<br>'
                . '<a href="' . $chaanelurl . 'noExclass/' . urlencode($keyword) . '/' . urlencode($start) . '/'. urlencode($end) .'">点击查看</a><br>'
                .'麻烦您可以关照练琴不主动，进步较慢的学生来再次预约体验我们的服务';
        return $content;
    }

    /**
     *  发送奖励页面
     * create by  wangkai
     */
    public function actionSendRewardPage($userId)
    {
        return $this->renderPartial('send-reward-page', [
            'user_id' => $userId
        ]);
    }

    // 本次奖励
    public function actionSendRewardContent($userId)
    {
        $data = $this->channelService->getThisReward($userId);

        return $this->renderPartial('send-reward-content', [
            'data' => $data['data'],
            'user_id' => $userId
        ]);
    }

    public function actionOtherRewardRecordPage($userId)
    {
        $data =  $this->channelService->getOtherRewardRecordCount($userId);
        return $this->renderPartial('other-reward-record-page', [
            'count' => $data['data']['count']
        ]);
    }

    public function actionOtherRewardRecordList($userId, $num)
    {
        $data = $this->channelService->getOtherRewardRecordList($userId, $num);

        return $this->renderPartial('reward-detail-list', [
            'data' => $data['data']['list']
        ]);
    }

    //获得听课权限页面title
    public function actionListenClassPower($openId)
    {
        return $this->renderPartial('listen-class-power', [
            'open_id' => $openId
        ]);
    }

    //获得听课权限页面内容
    public function actionListenClassPowerList($openId, $keyword = '', $num)
    {
        $info = $this->channelService->getWechatClassList($openId, $keyword, $num);

        return $this->renderPartial('listen-class-power-table', [
            'list' => $info['data']['data']
        ]);
    }

    //获取听课权限页面数量
    public function actionListenClassPowerPage($keyword = '')
    {
        $info = $this->channelService->getWechatClassCount($keyword);

        return $this->renderPartial('listen-class-power-page', [
            'count' => $info['data']['count']
        ]);
    }

    //查询是否用户具有听课权限
    public function actionSearchWechatClass($openId, $classId, $isBackShare)
    {
        $data = $this->channelService->getWechatClassId($openId, $classId, $isBackShare);

        if ($data['error'] == 2) {
            return $data['data']['class_id'];
        } else {
            return $data['error'];
        }
    }

    //添加听课权限
    public function actionAddWechatClassInfo()
    {
        $request = Yii::$app->request->post();

        $data = $this->channelService->doAddUserShareInfo($request['open_id'], $request['class_id'], $request['back_type']);

        return $data['error'];
    }

    /**
     * 获取不同类型的用户列表页面
     * @param  $type
     * @return array
     */
    public function actionChatUserPage($type)
    {
        $data = $this->channelChatService->getUserCount($type);

        return $this->renderPartial('chat-user-page', [
            'count' => $data['data']['count'],
            'type' => $type
        ]);
    }

    /**
     * 获取不同类型的用户列表列表
     * @param  $type
     * @param  $num
     * @return array
     */
    public function actionChatUserList($type, $num)
    {
        $data = $this->channelChatService->getUserList($type, $num);

        return $this->renderPartial('chat-user-list', [
            'list' => $data['data']['list'],
            'type' => $type
        ]);
    }

    /**
     * 转接用户
     */
    public function actionDoTransfer()
    {
        $linkId = Yii::$app->request->post('link_id');
        $kefuId = Yii::$app->request->post('kefu_id');

        return  $this->channelChatService->doEditTransfer($linkId, $kefuId);
    }

    /**
     * 转接客服
     */
    public function actionTransferServer()
    {
        $kefuId = Yii::$app->user->identity->id;

        $list = $this->channelChatService->getTransferServer($kefuId);

        return $this->renderPartial('transfer-server', [
            'list' => $list,
        ]);
    }


    /**
     * 关闭连接
     */
    public function actionCloseSocket()
    {
        $pageId = Yii::$app->request->post('page_id', '');

        return $this->channelChatService->closeSocket($pageId);
    }

    /**
     * 插入聊天
     */
    public function actionAddMessage()
    {
        $content = Yii::$app->request->post('content');
        $openId = Yii::$app->request->post('open_id');
        return $this->channelChatService->doAddMessage($content, $openId);
    }

    /**
     * 发送微信
     */
    public function actionSendWechat()
    {
        $request = Yii::$app->request->post();

        return $this->channelChatService->sendWechat($request);
    }

    /**
     * 发送模板
     */
    public function actionSendTemplate()
    {
        $request = Yii::$app->request->post();

        return $this->templateService->dealSendChannelTemplate($request);
    }

    /**
     * 断开连接
     */
    public function actionOffChat()
    {
        $linkId = Yii::$app->request->post('link_id');

        return $this->channelChatService->offChat($linkId);
    }

    /**
     * 判断是否在连接状态
     * @param  $page_id
     * @return int
     */
    public function actionCheckConnect($pageId)
    {
        return $this->channelChatService->getCheckConnectCount($pageId, Yii::$app->user->identity->id);
    }

    /**
     * 检测是否有在线用户
     * create by  wangkai
     */
    public function actionCheckTalk()
    {
        $openId = Yii::$app->request->get('open_id', '');
        return $this->channelChatService->getCheckTalk($openId);
    }

    /**
     * 加载更多的信息
     */
    public function actionLoadMore()
    {
        $offset = Yii::$app->request->get('offset', 0);

        return $this->channelChatService->getLoadMoreInfo($offset);
    }


    /**
     * 发送图片
     */
    public function actionImage()
    {
        return $this->renderPartial('image');
    }

    /**
     * 插入图片
     */
    public function actionDoSendImage()
    {
        $openId = Yii::$app->request->post('open_id');
        $file = $_FILES;
        $uid = Yii::$app->user->identity->id;

        return $this->channelChatService->doSendImage($openId, $file, $uid);
    }

    /**
     * 发送海报
     */
    public function actionDoSendHaibao()
    {
        $openId = Yii::$app->request->post('openid');
        $path = Yii::$app->request->post('fpath');
        $type = Yii::$app->request->post('type');
        $uid = Yii::$app->user->identity->id;
        if ($type) {//七牛上已经存在了
            return $this->channelChatService->wechatSendPic($openId, $path, $uid);
        }
        return $this->channelChatService->doSendHaibao($openId, $path, $uid);
    }

    /**
     * 是否可以接入
     * @return array
     */
    public function actionCheckAccess()
    {
        $waitId = Yii::$app->request->get('wait_id', 0);
        $page = Yii::$app->request->get('page', '');

        return $this->channelChatService->checkAccess($waitId, $page);
    }

    /**
     * 获取accessTalk
     */
    public function actionAccessTalk($openId = 0)
    {
        $data = $this->channelChatService->getAccessTalk($openId);

        if (!empty($data)) {
            return $this->renderPartial('chat', [
                'messages' => $data[0],
                'user' => $data[1],
                'userId' => $data[2],
                'channelName' => $data[3],
                'nick' => $data[4],
                'counts' => $data[5],
                'mobile' => empty($data[1]['username']) ? '' : '('.$data[1]['username'].')',
                'kefu_name' => $data[6]
            ]);
        }
    }

    /**
     * 点击接入
     * @param   $wait_id
     * @return  array
     */
    public function actionAccessRight($waitId = 0)
    {
        list($messageNew, $user, $userId, $channel_name, $nick, $counts,$kefu_name) = $this->channelChatService->accessRight($waitId);
        return $this->renderPartial('chat', [
            'messages' => $messageNew,
            'user' => $user,
            'userId' => $userId,
            'channelName' => $channel_name,
            'nick' => $nick,
            'counts' => $counts,
            'mobile'=>empty($user['username'])?'':'('.$user['username'].')',
            'kefu_name'=>$kefu_name
        ]);
    }

    /**
     * 根据open_id 找到当前聊天中的ID
     * @param  $openID
     * @return array
     */
    public function actionGetLink()
    {
        $openId = Yii::$app->request->get('open_id');

        return $this->channelChatService->getLink($openId);
    }

    /**
     * 发送红包
     * @param
     * @return  array
     * create by  wangkai
     */
    public function actionDoSendReward()
    {
        $user_id = Yii::$app->request->post('user_id', '');
        $message = Yii::$app->request->post('message', '');
        $title = Yii::$app->request->post('title');
        $money = Yii::$app->request->post('money');

        $data = $this->channelChatService->sendReward($user_id, $title, $money);

        if ($data['error'] === 0) {
            $info = $this->messageService->sendChannelRewardMessage($user_id, $message, $data['data']['history_id']);

            if ($info['error'] == 0) {
                $save_info = $this->channelChatService->doSaveChatMessage($user_id, $message);
                $save_info_2 = $this->channelChatService->doSaveChatMessage($user_id, '', $money);

                if ($save_info['error'] == 0 && $save_info_2['error'] == 0) {
                    $save_info['data']['message_1'] = $save_info_2['data']['message'];
                }
                return json_encode($save_info);
            } else {
                return json_encode($info);
            }
        } else {
            return json_encode($data);
        }
    }

    /**
     * 发送历史奖励
     * @param
     * @return  array
     * create by  wangkai
     */
    public function actionSendHisotryChannelRewardMessage($uid, $historyid)
    {
        if ($this->messageService->sendHisotryChannelRewardMessage($uid, $historyid)) {
            return 0;
        } else {
            return '发送失败，请联系管理员。';
        }
    }

    /**
     * 开启权限
     * create by  wangkai
     */
    public function actionDoOpenPremission($uid)
    {
        $data = $this->channelService->doOpenPremission($uid);

        return $data['error'];
    }

    /**
     * 快捷回复
     * create by wangkai
     */
    public function actionQuickAnswerIndex()
    {
        return $this->renderPartial('quick-answer-index');
    }

    /**
     * 快捷回复
     * @param $type
     * @return array
     * create by wangkai
     */
    public function actionQuickAnswer($type = 0)
    {
        $list = $this->channelChatService->getQuickAnswerList($type);

        return $this->renderPartial('quick-answer-content', [
            'list' => $list,
        ]);
    }

    /**
     * 添加快捷回复
     * create by wangkai
     */
    public function actionAddQuickAnswer()
    {
        $requset = Yii::$app->request;

        $content = $requset->post('content', '');
        $type = $requset->post('type', 0);

        $quick = $this->channelChatService->addQuickAnswer($content, $type);

        if ($quick['error'] == 0) {
            return Json::dieJson($quick);
        } else {
            return $quick['error'];
        }
    }

    /**
     * 快捷回复编辑
     * create by  wangkai
     */
    public function actionEditQucikAnswer($id, $content)
    {
        $data = $this->channelChatService->doEditQucikAnswer($id, $content);

        return $data['error'];
    }

    /**
     * 快捷回复删除
     * create by  wangkai
     */
    public function actionDeleteQucikAnswer($id)
    {
        $data = $this->channelChatService->doDeleteQucikAnswer($id);

        return $data['error'];
    }

    /**
     * 推广详情
     * create by  wangkai
     */
    public function actionChannelInfoIndex($id)
    {
        $data = $this->channelService->getChannelInfo($id);

        return $this->renderPartial('channel-info-index', [
            'data' => $data['data']['list'],
            'user_id' => $id
        ]);
    }

    /**
     * 推广内容展示
     * create by  wangkai
     */
    public function actionChannelInfoPage($id, $type)
    {
        $data = $this->channelService->getChannelInfoPage($id, $type);

        return $this->renderPartial('channel-info-page', [
            'count' => $data['data']['count']
        ]);
    }

    /**
     * 推广内容展示
     * create by  wangkai
     */
    public function actionChannelInfoList($id, $type, $num)
    {
        $data = $this->channelService->getChannelInfoList($id, $type, $num);

        return $this->renderPartial('channel-info-list', [
            'data' => $data['data']['data'],
            'type' => $type
        ]);
    }

    /**
     * 创建福利卡
     * @author   Yrxin
     * @DateTime 2017-04-23
     * @return   [string]     [json]
     */
    public function actionCreateWelfareCard()
    {
        $openid = Yii::$app->request->post('openid');
        
        return $this->chatService->getWelfareCard($openid);
    }

    /**
     * 生成临时二维码拉老师海报
     * @author Yrxin
     * @DateTime 2017-05-05T10:20:50+0800
     * @return   [type]                   [description]
     */
    public function actionCreateTemporaryPoster()
    {
        //4294967295
        $userid = Yii::$app->request->post('userid');
        //生成场景值ID，临时二维码时为32位非0整型
        $qrcodeUrl = $this->chatService->createTemporaryQrcode($userid);
        
        return $this->chatService->getTemporaryPoster($qrcodeUrl);
    }

    /**
     * 获取未回复消息
     * @author Yrxin
     * @DateTime 2017-05-11T19:43:33+0800
     * @return   [type]                   [description]
     */
    public function actionGetNoRepayInfo()
    {
        return $this->channelService->getNoRepayInfo();
    }

    /**
     * 查询点击专属服务的界面
     * @return string
     * create by wangke
     */
    public function actionPersonalServer()
    {
        return $this->renderPartial('personal-server');
    }

    /**
     * 查询点击专属服务的条数
     * @return string
     * create by wangke
     */
    public function actionPersonalServerPage($start = 0, $end = 0)
    {
        $count = $this->channelService->getPersonalServerPage($start, $end);

        return $this->renderPartial('personal-server-page', [
            'count' => $count
        ]);
    }

    /**
     * 查询点击专属服务的列表
     * @param int $num
     * @return string
     * create by wangke
     */
    public function actionPersonalServerList($num = 1, $start, $end)
    {
        $data = $this->channelService->getPersonalServerList($num, $start, $end);

        return $this->renderPartial('personal-server-list', [
            'data' =>$data
        ]);
    }


    /**
     * 月月活动奖励明细
     * @return string
     * create by wangke
     */
    public function actionMonthGift()
    {
        $data = $this->channelService->getAllChannelKefuInfo();
        return $this->renderPartial('month-gift', [
            'kefuInfo' => $data
        ]);
    }

    /**
     * 月月活动奖励明细条数
     * @param int $start
     * @param int $end
     * @param int $user_type
     * @return string
     * create by wangke
     */
    public function actionMonthGiftPage($start = 0, $end = 0, $usertype = 1, $kefuId = 0)
    {
        $count = $this->channelService->getMonthGiftPage($start, $end, $usertype, $kefuId);
        return $this->renderPartial('month-gift-page', [
            'count' => $count
        ]);
    }

    /**
     * 月月活动奖励明细列表
     * @param int $num
     * @param int $start
     * @param int $end
     * @param int $user_type
     * @return string
     * create by wangke
     */
    public function actionMonthGiftList($num = 1, $start = 0, $end = 0, $usertype = 1, $kefuId = 0)
    {
        //测试一天如果有同一个人，领取88元的情况
        $data = $this->channelService->getMonthGiftList($num, $start, $end, $usertype, $kefuId);
        return $this->renderPartial('month-gift-list', [
            'data' =>$data
        ]);
    }
    
    /**
     * 开启全部课程权限
     * create by sjy 2017-06-23
     */
    public function actionOpenSuperClass($openid)
    {
        $super = $this->chatService->openSuperClass($openid);
        return $super;
    }
}
