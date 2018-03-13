<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午3:17
 */

namespace common\logics\teacher;

interface IWorktime {

    /**
     * @param $week
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 获取老师的时间表
     */
    public function teacherTimeDate($week, $teacher_id);

    /**
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 获取老师周课表
     */
    public function getTeacherFixTime($teacher_id);

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 添加老师周课表
     */
    public function addTeacherFixTime($request);

    /**
     * @param $teacher_id
     * @param $timeDay
     * @return mixed
     * @author xl
     * 获取老师日课表
     */
    public function getTeacherTime($teacher_id, $timeDay);

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 添加老师周课表
     */
    public function addFixedTime($request);

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 添加老师日课表
     */
    public function addTeacherTime($request);

    /**
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 获取老师薪资生效记录
     */
    public function teacherFixTimeRecord($teacher_id);

}