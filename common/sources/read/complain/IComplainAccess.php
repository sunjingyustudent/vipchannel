<?php
/**
 * Created by PhpStorm.
 * User: xl
 * Date: 17/1/3
 * Time: 10:25
 */
namespace common\sources\read\complain;


interface IComplainAccess
{
    /**
     * @param $status
     * @return mixed
     * created by xl
     * 通过处理状态筛选家长投诉条数
     */
    public function getComplainCount($timeStart,$timeEnd,$status);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $status
     * @param $page_num
     * @return mixed
     * @author xl
     * 通过处理状态筛选家长投诉信息
     */
    public function getComplainInfo($timeStart,$timeEnd,$status,$page_num);

    /**
     * @param $id
     * @return mixed
     * created by xl
     * 通过ID获取家长投诉详细信息
     */
    public function getComplainById($id);

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
     * 处理投诉
     * @param $id
     * @return array
     */
    public function dealComplaintsInfo($id);

    /**
     * 家长投诉列表
     * @param  $status int 是否处理
     * @param  $page
     * @return array
     */
    public function getParentComplaintsList($status,$page);

    /**
     * 家长投诉页面
     * @param  $status int 是否处理
     * @return array
     */
    public function getParentComplaintsPage($status);
}