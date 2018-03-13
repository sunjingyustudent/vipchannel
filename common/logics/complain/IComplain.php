<?php
/**
 * Created by PhpStorm.
 * User: xl
 * Date: 17/1/3
 * Time: 10:16
 */
namespace common\logics\complain;


interface IComplain {

    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $status
     * @return mixed
     * @author xl
     * 家长投诉首页
     */
    public function complainPage($timeStart,$timeEnd,$status);

    /**
     * @param $time
     * @param $filter
     * @param $status
     * @param $page_num
     * @return mixed
     * @author xl
     * 家长投诉List
     */
    public function complainList($timeStart,$timeEnd,$status,$page_num);

    /**
     * @param $class_day
     * @param $class_filter
     * @return mixed
     * @author xl
     * 获取学生当天关联的课
     */
    public function getRelateClass($timeStart,$timeEnd,$class_filter,$student_id);

    /**
     * @param $complain_id
     * @param $class_id
     * @return mixed
     * @author xl
     * 关联投诉和课程
     */
    public function relateClass($complain_id,$class_id);

    /**
     * @param $request
     * @return mixed
     * created by xl
     * 处理家长投诉（需要处理）
     */
    public function updateComplainStatus($request);

    /**
     * @param $request
     * @return mixed
     * created by xl
     * 处理家长投诉（无需处理）
     */
    public function noDealComplain($request);

    /**
     * @param $filter
     * @return mixed
     * @author xl
     * 通过学生名搜索学生
     */
    public function getStudentList($filter);

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 添加投诉内容
     */
    public function doAddComplain($request);

    /**
     * @param $complain_id
     * @return mixed
     * @author xl
     * 投诉转接给客服
     */
    public function transfer($complain_id);

    /**
     * @param $student_id
     * @return mixed
     * create by wangke
     * 查询某个学生的投诉列表条数
     */
    public function countComplainPage($student_id);

    /**
     * @param $student_id
     * @param $num
     * @return mixed
     * create by wangke
     * 查询某个学生的投诉列表信息
     */
    public function getComplainList($student_id , $num);

    /**
     * @param $student_id
     * @return mixed
     * create by wangke
     * 复购投诉页面的条数
     */
    public function countPurchaseComplain($keyword);

    /**
     * @param $keyword
     * @param $num
     * @return mixed
     * create by wangke
     * 复购投诉页面的列表信息
     */
    public function getPurchaseComplainList($keyword , $num);

    /**
     * @param $class_id
     * @return mixed
     * @author xl
     * 显示投诉处理的modal页面
     */
    public function computeComplainReward($class_id, $reward_id);

}