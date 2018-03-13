<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 17/1/3
 * Time: 10:32
 */
namespace common\sources\write\complain;


interface IFeedbackAccess {

    /**
     * @param $request
     * @return mixed
     * created by xl
     * 处理老师反馈（修改状态）
     */
    public function updateFeedbackStatus($request);
}