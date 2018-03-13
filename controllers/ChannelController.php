<?php

namespace app\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use callmez\wechat\sdk;
use app\models\ErrorLogBean;
use common\widgets\Json;
use console\models\channel\WaitStatistics;

class ChannelController extends BaseController
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

    public function actionWaitStatistics()
    {
        return $this->renderPartial('waitstatistics', [
        ]);
    }

    public function actionWaitStatisticsPage($date = 0)
    {
        $startTime = empty($date) ? strtotime(date('Y-m-d 00:00:00', time())) : strtotime($date);
        $endTime = $startTime + 86400;
        list($num, $time) = $this->channelService->getWaitStatisticsPage($startTime, $endTime);

        return $this->renderPartial('waitstatistics_page', [
            'num' => json_encode($num),
            'time' => json_encode($time)
        ]);
    }
}
