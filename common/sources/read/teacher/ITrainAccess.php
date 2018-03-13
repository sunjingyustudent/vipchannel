<?php
/**
 * Created by PhpStorm.
 * User: xl
 * Date: 16/12/16
 * Time: 10:00
 */
namespace common\sources\read\teacher;


interface ITrainAccess
{
    /**
     * @param $filter string 空|姓名|手机
     * 待沟通教师列表
     */
    public function selectTraceList($filter);

    /**
     * @param $trace_id int
     * 待沟通教师明细
     */
    public function selectTraceDetail($trace_id);

    /**
     * @param $filter string
     * @param $page_num int
     */
    public function getTraceList($filter, $page_num);

    /**
     * @return mixed
     * @author xl
     * 获取需要添加到班级的成员列表
     */
    public function getAddUserList();

    /**
     * @return mixed
     * @author xl
     * 获取班级count
     */
    public function getClassCount($filter);

    /**
     * @return mixed
     * @author xl
     * 获取班级LIST
     */
    public function getClassList($filter, $page_num);

     /* @param $filter string 空|姓名|手机
     * 待培训教师列表
     */
    public function selectTraceTeacherList($filter);

    /**
     * @param $filter string 空|姓名|手机
     * @param $page_num int
     * 待培训教师列表
     */
    public function getTraceTeacherList($filter, $page_num);

    /**
     * @param $filter string 空|姓名|手机
     * 废弃池教师列表
     */
    public function selectTraceQuitList($filter);

    /**
     * @param $filter string 空|姓名|手机
     * @param $page_num int
     * 废弃池教师列表
     */
    public function getTraceQuitList($filter, $page_num);

    /**
     * @param $id
     * @return mixed
     * @author xl
     * 通过班级ID获取信息
     */
    public function getClassInfoById($id);

    /**
     * @param $class_id
     * @return mixed
     * @author xl
     * 通过班级ID获取成员信息
     */
    public function getClassMembersById($class_id);

    /**
     * @param $user_id
     * @return mixed
     */
    public function getSchoolInstrument($user_id);

    /**
     * @param $class_id
     * @return mixed
     * @author xl
     * 通过班级ID获取创建时间
     */
    public function getClassCreatedTime($class_id);

    /**
     * @param $class_id
     * @return mixed
     * @author xl
     * 获取已过期的班级成员（不包含自身）
     */
    public function getEndClassUserList($class_id);

    /**
     * @return mixed
     * @author xl
     * 获取可分配班级
     */
    public function getAllotClass($uid);

    /*
     * @return array
     * @author yuhao
     * 获取学校列表
     */
    public function getSchoolList();

    /*
     * @return array
     * @author yuhao
     * 获取学校
     */
    public function getSchoolById($schoolId);
}