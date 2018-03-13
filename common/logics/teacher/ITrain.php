<?php
/**
 * Created by phpStorm.
 * User: xl
 * Date: 2017/2/23
 * Time: 18:19
 */
namespace common\logics\teacher;

interface ITrain {

    /**
     * @return mixed
     * @author xl
     * 显示需要添加到班级的成员列表
     */
    public function getAddUserList();

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 新建班级
     */
    public function addClass($request);

    /**
     * @return mixed
     * @author xl
     * 获取班级count
     */
    public function getClassCount($filter);

    /**
     * @return mixed
     * @author xl
     * 获取班级列表
     */
    public function getClassList($filter, $page_num);

    /**
     * @param $id
     * @return mixed
     * @author xl
     * 通过班级ID获取班级信息
     */
    public function getClassInfoById($id);

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 编辑班级
     */
    public function editClass($request);

    /**
     * @param $class_id
     * @return mixed
     * @author xl
     * 删除班级
     */
    public function deleteClass($class_id);

    /**
     * @param $class_id
     * @return mixed
     * @author xl
     * 获取本班级成员及未分班级成员
     */
    public function showMembersEdit($class_id);

    /**
     * @return mixed
     * @author xl
     * 获取可分配的班级
     */
    public function getAllotClass($uid);

    /**
     * @param $uid
     * @return mixed
     * @author xl
     * 将成员分配到班级
     */
    public function allotClass($uid, $class_id);

    /**
     * @param $cid
     * @return mixed
     * @author xl
     * 显示班级成员信息
     */
    public function showMemberDetail($cid);

}