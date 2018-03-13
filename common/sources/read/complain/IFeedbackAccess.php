<?php
/**
 * Created by PhpStorm.
 * User: xl
 * Date: 17/1/3
 * Time: 10:25
 */
namespace common\sources\read\complain;


interface IFeedbackAccess
{
/**
     * @param $status
     * @return mixed
     * created by sjy
     * 获取老师反馈已处理/未处理条数
     */
    public function getFeedbackCount($status);
    
    
    /**
     * @param $status
     * @param $pagination
     * @return mixed
     * created by xl
     * 获取老师反馈已处理/未处理详细信息
     */
    public function getFeedbackInfo($status,$pagination);
    
    /**
     * @param $id
     * @return mixed
     * created by xl
     * 通过ID获取老师反馈详细信息
     */
    public function getFeedbackById($id);
}