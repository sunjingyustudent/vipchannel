<?php

namespace app\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use callmez\wechat\sdk;
use app\models\ErrorLogBean;

class ReportController extends BaseController
{
    /** @var  \common\logics\sale\SaleLogic $saleService */
    private $saleService;
    /** @var  \common\logics\channel\ChannelLogic  $channelService */
    private $channelService;
    /** @var \common\logics\push\MessageLogic   $messageService */
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
     * 体验课列表界面
     * create by wangke
     */
    public function actionExClassReport()
    {
        $data = $this->manageService->getExClassReportKefuInfo();
        return $this->renderPartial('ex-class-report', [
            'kefu_info' => $data
        ]);
    }

    /**
     * 体验课列表条数
     * @param int $type 选项卡  0 每日关注 1 每日体验
     * @param int $date 日期
     * @param int $status  关注或体验状态   关注0 注册未预约  1 注册已预约    体验课 0 取消  1 完成
     * @param int $kefuid  客服id
     * @return string
     * create by wangke
     */
    public function actionExClassReportPage($type = 0, $date = 0, $status = 0, $kefuid = 0)
    {
        $count = $this->channelService->getExClassReportCount($type, $date, $status, $kefuid);
        return $this->renderPartial('ex-class-report-page', [
            'count' => $count
        ]);
    }


    /**
     * 体验课列表列表
     * @param int $type 选项卡  0 每日关注 1 每日体验
     * @param int $date 日期
     * @param int $status  type=1时,0关注未预约,1关注已预约;2时,0体验课待上课,1体验课完成,2体验课取消
     * @param int $kefuid  客服id
     * @param int $num  分页的页数
     * @return string
     * create by wangke
     */
    public function actionExClassReportList($type = 0, $date = 0, $status = 0, $kefuid = 0, $num = 1)
    {
        $data = $this->channelService->getExClassReportList($type, $date, $status, $kefuid, $num);
        return $this->renderPartial('ex-class-report-list', [
            'data' => $data,
            'type' => $type,
            'status' => $status
        ]);
    }
}
