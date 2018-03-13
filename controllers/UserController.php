<?php

namespace app\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use callmez\wechat\sdk;
use app\models\ErrorLogBean;

class UserController extends BaseController
{
    /** @var  \common\logics\sale\SaleLogic $saleService */
    private $saleService;
    /** @var  \common\logics\channel\ChannelLogic $channelService */
    private $channelService;
    /** @var \common\logics\push\MessageLogic $messageService */
    private $messageService;
    /** @var  \common\logics\sale\ManageLogic $manageService */
    private $manageService;

    public function init()
    {
        $this->saleService = Yii::$container->get('saleService');
        $this->channelService = Yii::$container->get('channelService');
        $this->messageService = Yii::$container->get('messageService');
        $this->manageService = Yii::$container->get('manageService');
        parent::init();
    }

    /**
     * 用户名单
     * create by  wangkai
     */
    public function actionUserList()
    {
        return $this->renderPartial('user-list');
    }

    /**
     * 用户列表页面
     * create by  wangkai
     */
    public function actionUserListPage($type, $keyword = '', $time = '', $studentPhone = '')
    {
        $count = $this->channelService->getSaleChannelUserCount($type, $keyword, $time, $studentPhone);

        return $this->renderPartial('user-list-page', [
            'count' => $count['data']['count']
        ]);
    }

    /**
     * 用户里列表详情
     * create by  wangkai
     */
    public function actionUserListInfo($type, $num, $keyword = '', $time = '', $studentPhone = '')
    {
        $user_info = $this->channelService->getSaleChannelUserList($type, $keyword, $num, $time, $studentPhone);

        return $this->renderPartial('user-list-info', [
            'user' => $user_info['data']['list'],
        ]);
    }

    /**
     * 奖励提醒用户页面
     * create by  wangkai
     */
    public function actionRewardUserPage($time = '', $keyword = '', $rewardtype = 0)
    {
        $data = $this->channelService->getRewardUserCount($time, $keyword, $rewardtype);
        return $this->renderPartial('user-list-page', [
            'count' => $data['data']['count']
        ]);
    }

    /**
     * 奖励提醒用户列表
     * create by  wangkai
     */
    public function actionRewardUserList($num, $time = '', $keyword = '', $rewardtype = 0)
    {
        $data = $this->channelService->getRewardUserList($num, $time, $keyword, $rewardtype);

        return $this->renderPartial('user-list-info', [
            'user' => $data['data']['data']
        ]);
    }


    /**
     * 编辑用户
     * create by  wangkai
     */
    public function actionEditUser($openid)
    {
        $info = $this->channelService->getSaleChannelUserInfo($openid);
        return $this->renderPartial('edit-user', [
            'data' => $info['data']['list'],
            'statusList' => $info['data']['statusList'],
            'worthList' => $info['data']['worthList'],
            'kefu' => $info['data']['kefu'],
            'instrument' => $info['data']['instrument'],
            'instrument_ids' => $info['data']['instrument_ids'],
        ]);
    }

    /**
     * 进行编辑操作
     * create by  wangkai
     */
    public function actionDoEditUser()
    {
        $request = Yii::$app->request->post();

        $info = $this->channelService->doEditUser($request);
        return $info['error'];
    }

    /**
     * 删除用户
     * create by  wangkai
     */
    public function actionDoDeleteUser()
    {
        $id = Yii::$app->request->post('id');
        $info = $this->channelService->doDeleteUser($id);
        return $info['error'];
    }


    /**
     * @return string
     * create by wangke
     * VIP微课管理视角 全部用户
     */
    public function actionAllUserList()
    {

        $data = $this->manageService->getAllUserKefuInfo();
        return $this->renderPartial('all-user-list', [
            'list' => $data['kufu_binding'],
            'list_2' => $data['kufu_select']
        ]);
    }

    /**
     * @param $type
     * @param string $keyword
     * @param string $time
     * @return string
     * create by wangke
     * VIP微课管理视角 全部用户的条数
     */
    public function actionAllUserListPage($type = 0, $keyword = '', $time = '', $kefutype = 0, $studentPhone = '')
    {
        $count = $this->channelService->getAllSaleChannelUserCount($type, $keyword, $time, $kefutype, $studentPhone);

        return $this->renderPartial('all-user-list-page', [
            'count' => $count['data']['count']
        ]);
    }


    /**
     * @param $type
     * @param $num
     * @param string $keyword
     * @param string $time
     * @return string
     * create by wangke
     * VIP微课管理视角 全部用户的list
     */
    public function actionAllUserListInfo($type, $num, $keyword = '', $time = '', $kefutype = 0, $studentPhone = '')
    {
        $user_info = $this->channelService->getAllSaleChannelUserList($type, $keyword, $num, $time, $kefutype, $studentPhone);

        return $this->renderPartial('all-user-list-info', [
            'user' => $user_info['data']['list'],
        ]);
    }

    /**
     * @return string
     * create by wangke
     * VIP微课 全部用户分配客服
     */
    public function actionDistribute()
    {
        $request = Yii::$app->request;

        $userId = $request->post('user_id');
        $kefuId = $request->post('kefu_id');

        //$this->manageService->distributePublicUserKefu($this->logid, $userId, $kefuId);
        return $this->manageService->distributeAllUserKefu($userId, $kefuId);
    }

    /**
     * 全部用户 删除用户
     * create by  wangkai
     */
    public function actionDoDeleteAllUser()
    {
        $id = Yii::$app->request->post('id');

        $info = $this->channelService->doDeleteAllUser($id);

        return $info['error'];
    }

    /**
     * 微信聊天界面点亮用户
     * create by wangke
     */
    public function actionLightenUser()
    {
        $request = Yii::$app->request;
        return $this->channelService->lightenUser($request);
    }


    /*
     * 生成用户二维码
     * create by sjy
     */
    public function actionChannelCode($userid)
    {
        return $this->channelService->channelCode($userid);
    }

    /*
     * 显示专属拉新二维码
     * create by sjy
     */
    public function actionGetChannelCode($userid)
    {
        $data = $this->channelService->getChannelCode($userid);
        if (empty($data['error'])) {
            $img = $data["data"]["channel_code"];
        }
        return $this->renderPartial('getchannelcode', [
            'img' => $img
        ]);
    }

    /**
     * [转渠道列表初始页]
     * @author Yrxin
     * @DateTime 2017-05-31T18:01:02+0800
     * @return   [type]                   [description]
     */
    public function actionTransferIndex()
    {
        $channels = $this->channelService->getAllChannel();

        return $this->renderPartial('channel-transfer', [
            'channels' => $channels,
        ]);
    }

    /**
     * [转渠道列表页码]
     * @author Yrxin
     * @DateTime 2017-05-31T18:01:06+0800
     * @return   [type]                   [description]
     */
    public function actionTransferPage()
    {
        $params = Yii::$app->request->get();
        $count = $this->channelService->getTransferCount($params);
        return $this->renderPartial('channel-transfer-page', [
            'count' => $count,
        ]);
    }

    /**
     * [转渠道列表]
     * @author Yrxin
     * @DateTime 2017-05-31T18:01:09+0800
     * @param    integer $num [description]
     * @return   [type]                        [description]
     */
    public function actionTransferList($num = 0)
    {
        $params = Yii::$app->request->get();
        $data = $this->channelService->getTransferList($num, $params);
        return $this->renderPartial('channel-transfer-list', [
            'item' => $data,
            'params' => $params
        ]);
    }

    /**
     * [查看操作详情]
     * @author Yrxin
     * @DateTime 2017-05-31T21:33:31+0800
     * @param    [type]                   $id [description]
     * @return   [type]                       [description]
     */
    public function actionShowTransferReward($id)
    {
        $reward_type = [
            12 => '转渠道奖励'
        ];
        $data = $this->channelService->getTransferNewChannelInfo($id);
        return $this->renderPartial('channel-reward-detail', [
            'item' => $data,
            'reward_type' => $reward_type
        ]);
    }

    /**
     * [奖励入库]
     * @author Yrxin
     * @DateTime 2017-06-05T11:52:30+0800
     * @return   [type]                   [description]
     */
    public function actionDoTransferReward()
    {
        $params = Yii::$app->request->post();
        return $this->channelService->insertTransferReward($params);
    }

    /**
     * [用户买单记录]
     * @author Yrxin
     * @DateTime 2017-06-05T14:19:33+0800
     * @return   [type]                   [description]
     */
    public function actionGetOrderPage()
    {
        $sid = Yii::$app->request->get('sid');
        $count = $this->channelService->getBuyOrderCount($sid);
        return $this->renderPartial('channel-order-page', [
            'count' => $count,
            'sid' => $sid,
        ]);
    }

    public function actionGetOrderList($num = 0)
    {
        $sid = Yii::$app->request->get('sid');
        $data = $this->channelService->getBuyOrderList($sid, $num);
        return $this->renderPartial('channel-order-list', [
            'item' => $data,
        ]);
    }

    /**
     * 羊毛党列表界面
     * @return string
     * create by wangke
     */
    public function actionWoolParty()
    {
        $data = $this->manageService->getExClassReportKefuInfo();
        return $this->renderPartial('wool-party', [
            'kefuList' => $data,
        ]);
    }

    /**
     * 羊毛党列表条数
     * @param int $kefuId
     * @return string
     * create by wangke
     */
    public function actionWoolPartyPage($kefuId = 0)
    {
        //$count = 2;
        $count = $this->channelService->getWoolPartyCount($kefuId);
        return $this->renderPartial('wool-party-page', [
            'count' => $count,
        ]);
    }

    /**
     * 羊毛党列表详细
     * @param $num
     * @param int $kefuId
     * @return string
     * create by wangke
     */
    public function actionWoolPartyList($num, $kefuId = 0)
    {
        $data = $this->channelService->getWoolPartyList($num, $kefuId);
        return $this->renderPartial('wool-party-list', [
            'data' => $data
        ]);
    }

    public function actionWoolSetType()
    {
        $id = Yii::$app->request->post('id');
        return $this->channelService->setTypeWoolParty($id);
    }

    public function actionUpdateQrcode()
    {
        $params = Yii::$app->request->post();
        return $this->channelService->updateQrcode($params);
    }
}
