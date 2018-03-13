<?php

namespace app\controllers;

use common\services\QiniuService;
use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use callmez\wechat\sdk;
use app\models\ErrorLogBean;

use common\models\write\Test;
use crm\models\sales\KefuFixTimeWeike;
use crm\models\sales\KefuTimetableWeike;
use crm\models\sales\UserCustomerArchivers;
use common\services\LogService;

class SaleController extends BaseController
{
    /** @var  \common\logics\sale\SaleLogic $saleService */
    private $saleService;
    /** @var  \common\logics\visit\VisitLogic $visitService */
    private $visitService;
    /** @var  \common\logics\channel\ChannelLogic $channelService */
    private $channelService;
    /**@var \common\logics\account\AccountLogic $accountService */
    private $accountService;

    public function init()
    {
        $this->saleService = Yii::$container->get('saleService');
        $this->visitService = Yii::$container->get('visitService');
        $this->channelService = Yii::$container->get('channelService');
        $this->accountService = Yii::$container->get('accountService');
        parent::init();
    }

    /**
     * 消息列表
     * create by  wangkai
     */
    public function actionNewsList()
    {
        return $this->renderPartial('news-list-index');
    }

    public function actionNewListPage($type)
    {
        $count = 2;

        return $this->renderPartial('news-list-page', [
            'count' => $count
        ]);
    }

    public function actionNewsListInfo($type, $num)
    {
        $news_info = array();

        return $this->renderPartial('news-list-info', [
            'news_info' => $news_info
        ]);
    }


    /**
     * 待跟进名单
     * create by  wangkai
     */
    public function actionTodoIndex()
    {
        return $this->renderPartial('todo-index');
    }

    public function actionTodoList($start, $end)
    {
        $todo_info = $this->saleService->getChannelTodoList($start, $end);

        return $this->renderPartial('todo-list', [
            'list' => $todo_info['data']['list']
        ]);
    }


    /**
     * 奖励提醒
     * create by  wangkai
     */
    public function actionRewardRemind()
    {
        return $this->renderPartial('reward-remind');
    }

    public function actionRewardRemindPage($time)
    {
        $count = 2;

        return $this->renderPartial('reward-remind-page', [
            'count' => $count
        ]);
    }

    public function actionRewardRemindList($time, $num)
    {
        $reward = array();

        return $this->renderPartial('reward-remind-list', [
            'reward' => $reward
        ]);
    }

    /**
     * 奖励名单
     * create by  wangkai
     */
    public function actionRewardList()
    {
        return $this->renderPartial('reward-list');
    }

    public function actionRewardListPage($time, $name)
    {
        $count = 2;

        return $this->renderPartial('reward-list-page', [
            'count' => $count
        ]);
    }

    public function actionRewardListInfo($time, $name, $num)
    {
        $reward_info = array();

        return $this->renderPartial('reward-list-info', [
            'reward' => $reward_info
        ]);
    }

    /**
     * 员工管理
     * create by  wangkai
     */
    public function actionStaffManagement()
    {
        return $this->renderPartial('staff-management');
    }

    /**
     * 渠道数据
     * create by  wangkai
     */
    public function actionChannelData()
    {
        return $this->renderPartial('channel-data');
    }

    public function actionChannelDataPage()
    {
        $count = 2;

        return $this->renderPartial('channel-data-page', [
            'count' => $count
        ]);
    }

    public function actionChannelDataList($num)
    {
        $channel_info = array();

        return $this->renderPartial('channel-data-list', [
            'channel_info' => $channel_info
        ]);
    }


    /**
     * 跟进信息
     * @param $channel_id
     * @return string
     * create by wangke
     */
    public function actionGetVisitPage($channelId)
    {
        $count = $this->visitService->getSaleChannelVisitCount($channelId);
        $channelExClassInfo = $this->channelService->getChannelExClassInfo($channelId);

        return $this->renderPartial('visit-page', [
            'count' => $count['data']['count'],
            'channel_id' => $channelId,
            'exClassInfo' => $channelExClassInfo,
            'nowNeedDoneCount' => $count['data']['nowNeedDoneCount']
        ]);
    }

    public function actionGetVisitList($channelId, $num)
    {
        $list = $this->visitService->getSaleChannelVisitList($channelId, $num);

        return $this->renderPartial('visit-list', [
            'list' => $list['data']['list']
        ]);
    }

    /**
     * 聊天-跟进信息
     * @param $classId
     * create by wangke
     */
    public function actionDoneVisit()
    {
        $request = Yii::$app->request;
        return $this->visitService->doneVisit($request);
    }

    /**
     * 添加跟进记录
     * @return mixed
     * create by wangke
     */
    public function actionAddChannelVisit()
    {
        $request = Yii::$app->request->post();
        $data = $this->visitService->addChannelVisit($request);
        return $data['error'];
    }

    /**
     * create by wangke
     */
    public function actionShowTodolistCount()
    {
        $data = $this->saleService->getShowTodolistCount();
        return $data;
    }

    /**
     * create by wangke
     * 微课公众号的员工管理
     */
    public function actionEmploye()
    {
        return $this->renderPartial('index');
    }

    /**
     * @return string
     * create by wangke
     * 微课公众号 员工管理的条数
     */
    public function actionEmployePage()
    {
        $request = Yii::$app->request;
        $keyword = $request->post('keyword');
        $status = $request->post('status');

        $count = $this->saleService->countEmploye($keyword, $status);

        return $this->renderPartial('employe-page', [
            'count' => $count
        ]);
    }


    /**
     * create by wangke
     * 员工列表 VIP微课
     */
    public function actionEmployeList()
    {
        $request = Yii::$app->request;

        $keyword = $request->post('keyword');
        $status = $request->post('status');
        $num = $request->post('page_num');

        $list = $this->saleService->getEmployeList($keyword, $status, $num);

        return $this->renderPartial('employe-list', [
            'list' => $list
        ]);
    }

    /**
     * @return string
     * create by wangke
     * 添加员工
     */
    public function actionAddEmploye()
    {
        return $this->renderPartial('add-employe');
    }


    /**
     * @return string
     * create by wangke
     * 员工管理 添加操作
     */
    public function actionDoAddCourseKefu()
    {
        $request = Yii::$app->request->post();
        return $this->saleService->addEmployeManagement($request);
    }

    /**
     * @param $kefu_id
     * @return string
     * create by wangke
     * 修改员工资料
     */
    public function actionUpdateEmploye($kefuid)
    {
        $kefuinfo = $this->saleService->getAccountInfoByKefuId($kefuid);

        $kefuinfo['card'] = empty($kefuinfo['card']) ? '' : Yii::$app->params['vip_static_path'] . $kefuinfo['card'];
        $kefuinfo['poster'] = empty($kefuinfo['poster']) ? '' : Yii::$app->params['vip_static_path'] . $kefuinfo['poster'];
        $kefuinfo['qrcode'] = empty($kefuinfo['qrcode']) ? '' : Yii::$app->params['vip_static_path'] . $kefuinfo['qrcode'];

        $kefuinfo['banner'] = empty($kefuinfo['banner']) ? '' : Yii::$app->params['vip_static_path'] . $kefuinfo['banner'];

        return $this->renderPartial('update-employe', [
            'kefu_info' => $kefuinfo
        ]);
    }

    /**
     * @return mixed|string
     * create by wangke
     * 员工编辑操作
     */
    public function actionDoUpdateEmploye()
    {
        $request = Yii::$app->request->post();
        return $this->saleService->updateCourseKefuInKefuManagement($request);
    }

    /**
     * @return mixed|string
     * create by wangke
     * 员工禁用操作
     */
    public function actionDeleteKefu()
    {
        $kefuid = Yii::$app->request->post('kefu_id');
        $deltype = Yii::$app->request->post('del_type');
        return $this->saleService->deleteKefu($this->logid, $kefuid, $deltype);
    }


    /**
     * @return mixed|string
     * create by wangke
     * 启用员工
     */
    public function actionOpenEmploye()
    {
        $kefuid = Yii::$app->request->post('kefu_id');
        return $this->saleService->openEmploye($kefuid);
    }


    public function actionWorkTime()
    {
        return $this->renderPartial('work-time');
    }

    /**
     * @param $kefu_id
     * @param $time
     * @param int $type
     * @return string
     * create by wangke
     * 员工日上班表显示
     */
    public function actionGetWorkTime($kefuid, $time, $type = 1)
    {
        $timeList = $this->accountService->getEmployeWorkTime($kefuid, $time, $type);

        if ($type == 1) {
            return $this->renderPartial('work-time-day', [
                'time_list' => $timeList
            ]);
        } else {
            return json_encode($timeList, JSON_UNESCAPED_SLASHES);
        }
    }

    /**
     * @param $kefu_id
     * @return string
     * create by wangke
     * 员工周上班表显示
     */
    public function actionGetKefuFixedTime($kefuid)
    {
        $data = $this->accountService->getEmployeWeekTable($kefuid);

//        print_r($data);exit;
        return $this->renderPartial('work-time-week', [
            'data' => $data
        ]);
    }


    /**
     * @return int|mixed
     * create by wangke
     *  员工上班日设置
     */
    public function actionAddWorkTime()
    {
        $request = Yii::$app->request->post();
//        $userId = Yii::$app->user->id;
//        $role = 2;
        return $this->accountService->addEmployeWorkTime($request);
    }

    /**
     * @return int|mixed
     * create by wangke
     *  销售上班周设置
     */
    public function actionAddKefuFixedTime()
    {
        $request = Yii::$app->request->post();

        return $this->accountService->addEmployeWeekTable($request);
    }

    /**
     * 推广价值主页面
     * @return  array
     * create by  wangkai
     * create time  2017/4/11
     */
    public function actionPromotionEffect()
    {
        return $this->renderPartial('promotion-effect-index');
    }

    public function actionPromotionEffectPage($start, $end)
    {
        $uid = Yii::$app->user->identity->id;
        $data = $this->saleService->getPromotionEffectPage($start, $end, $uid);

        return $this->renderPartial('promotion-effect-page', [
            'count' => $data['data']['count']
        ]);
    }

    public function actionPromotionEffectList($start, $end, $num)
    {
        $uid = Yii::$app->user->identity->id;
        $data = $this->saleService->getPromotionEffectList($start, $end, $num, $uid);

        return $this->renderPartial('promotion-effect-list', [
            'list' => $data['data']['list'],
            'sum' => $data['data']['sum']
        ]);
    }

    /**
     * create by wangke
     * 将图上传到七牛 员工管理
     */
    public function actionImgUpload()
    {
        $bucket = Yii::$app->params['vip_static_bucket'];
        $qiniu_imgserver_path = Yii::$app->params['vip_static_path'];
        $filePathTo = 'vipemployemanage/' . md5(microtime() . '_' . rand(10, 99));
        $filePathFrom = $_FILES['icon']['tmp_name'];
        $flag = QiniuService::uploadToQiniu($bucket, $filePathTo, $filePathFrom);

        if ($flag) {
            return $qiniu_imgserver_path . $filePathTo;
        } else {
            return 0;
        }
    }
}
