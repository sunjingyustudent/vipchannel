<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;

/**
 * Site controllers
 */
class ApiController extends Controller
{
    /**
     * [$token description]
     * @var string
     */
    private $token;

    /**
     * [$post description]
     * @var string
     */
    private $post;

    /** @var  \common\logics\api\ApiLogic $apiService */
    private $apiService;

    public function init()
    {
        $this->token = '';
        $this->enableCsrfValidation = false;
        $this->apiService = Yii::$container->get('apiService');
        $this->post = Yii::$app->request->getBodyParams();
    }

    /**
     * 二维码使用情况
     * @author Yrxin
     * @DateTime 2017-08-04T13:58:29+0800
     * @return   [type]                   [description]
     */
    public function actionGetQrcodeUse()
    {
        $data['all'] = $this->apiService->getQrCodeNum();
        $data['unuse'] = $this->apiService->getQrCodeNumByType(0);

        return json_encode(['error' => '', 'data' => $data]);
    }

    /**
     * 获取二维码
     * @author Yrxin
     * @DateTime 2017-08-04T10:36:18+0800
     * @return   [type]                   [description]
     */
    public function actionCreateQrcode()
    {
        if (!Yii::$app->request->isPost) {
            return json_encode(['error'=>'Illegal request!']);
        }
        $type = intval(is_array_set($this->post, 'type'));
        $channelId = intval(is_array_set($this->post, 'channel_id'));
        //分配二维码
        $result = $this->apiService->assignQrcode($type, $channelId);

        return json_encode($result);
    }

    /**
     * 释放二维码
     * @author Yrxin
     * @DateTime 2017-08-04T14:00:47+0800
     * @return   [type]                   [description]
     */
    public function actionFreeQrcode()
    {
    }
}
