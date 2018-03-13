<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/1/6
 * Time: 下午8:39
 */
namespace common\logics\push;


interface IPush {

    /**
     * @param $msg
     * @return mixed
     * @created by Jhu
     * 处理老师推送给学生
     */
    public function dealTeacherPushApp($msg);

    /**
     * @param $msg
     * @return mixed
     * @created by Jhu
     * 处理学生推送给老师
     */
    public function dealStudentPushApp($msg);

    /**
     * @param $msg
     * @return mixed
     * @created by Jhu
     * 处理学生推送给老师(测试)
     */
    public function dealStudentPushAppDev($msg);

    /**
     * @param $msg
     * @return mixed
     * @created by Jhu
     * 处理老师推送给学生(测试)
     */
    public function dealTeacherPushAppDev($msg);


}