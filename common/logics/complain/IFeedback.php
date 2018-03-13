<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午3:17
 */

namespace common\logics\complain;

interface IFeedback {

    /**
     * @param $status
     * @return mixed
     * created by sjy
     * 老师反馈首页
     */
    public function feedbackIndex($status);
    
    /**
     * @param $request
     * @return mixed
     * created by xl
     * 处理老师反馈（需要处理）
     */
    public function updateFeedbackStatus($request);
    
    /**
     * @param $request
     * @return mixed
     * created by xl
     * 处理老师反馈（无需处理）
     */
    public function noDealFeedback($request);
}