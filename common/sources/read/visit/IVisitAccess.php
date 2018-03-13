<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/4
 * Time: 下午12:32
 */
namespace common\sources\read\visit;

interface IVisitAccess
{
    /**
     * @param $student_id
     * @return mixed
     * created by hujiyu
     * 根据学生ID获取用户回访总次数
     */
//    public function countVisitByStudentId($student_id);


    /**
     * 获取销售渠道的用户最近的记录
     * @param $sale_channel_id
     * @param $num
     * @return  array
     * create by  wangkai
     */
    public function getSaleChannelVisitInfo($saleChannelId, $num);

    /**
     * 获取最近的回访记录
     * @param   $student_id
     * @return  array
     * create by  wangkai
     */
    public function getRecentVisitRecord($studentId);
}
