<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午3:18
 */
namespace common\logics\complain;

use Yii;
use yii\base\Object;
use yii\data\Pagination;
use common\widgets\BinaryDecimal;

class FeedbackLogic extends Object implements IFeedback
{
    /** @var  \common\sources\read\complain\FeedbackAccess  $RFeedbackAccess */
    private $RFeedbackAccess;
    /** @var  \common\sources\write\complain\FeedbackAccess  $WFeedbackAccess */
    private $WFeedbackAccess;
   


    public function init()
    {
        $this->RFeedbackAccess = Yii::$container->get('RFeedbackAccess');
        $this->WFeedbackAccess = Yii::$container->get('WFeedbackAccess');
       
        parent::init();
    }
    
    /**
     * @param $status
     * @return mixed
     * created by sjy
     * 老师反馈首页
     */
    public function feedbackIndex($status)
    {
        $count = $this->RFeedbackAccess->getFeedbackCount($status);

        $pagination = new pagination([
            'defaultPageSize'=>5,
            'totalCount' => $count,
        ]);

        $info = $this->RFeedbackAccess->getFeedbackInfo($status,$pagination);

        $data = array('info' => $info, 'count' => $count, 'pagination' => $pagination);

        return $data;
    }
    
    
     public function updateFeedbackStatus($request)
    {
        $re = $this->WFeedbackAccess->updateFeedbackStatus($request);

        if($re == 1){

            $info = $this->RFeedbackAccess->getFeedbackById($request['id']);

            if(empty($info['openID']))
            {
                $info['openID'] = $info['bind_openid'];
            }

            return array(
                'error' => '',
                'feedback_info' => $info
            );
        }else{
            return array(
                'error' => '处理失败',
                'feedback_info' => ''
            );
        }
    }
    
    public function noDealFeedback($request)
    {
        $request['context'] = '无';

        $re = $this->WFeedbackAccess->updateFeedbackStatus($request);

        if($re)
        {
            return 1;
        }else{
            return 0;
        }
    }
}