<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:05
 */

namespace common\sources\read\teacher;

interface IRestAccess {

    /**
     * @param $rest_id
     * @return mixed
     * @author xl
     * 通过请假ID获取老师请假信息
     */
    public function getRestById($rest_id);

    /**
     * @param $teacher_id
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取老师月请假信息
     */
    public function getTeacherLeaveInfo($teacher_id, $timeStart, $timeEnd);

    /**
     * @param $teacher_id
     * @param $timeDay
     * @return mixed
     * @author xl
     * 获取老师请假信息
     */
    public function getLeaveByTeacherId($teacher_id, $timeDay);

    /**
     * @param $teacher_id
     * @param $timeDay
     * @param $type
     * @return mixed
     * @author xl
     * 获取老师当月请假次数
     */
    public function countTeacherLeaveMonth($teacher_id, $timeStart, $timeEnd, $type);

    /**
     * @param $teacher_id
     * @param $type
     * @return mixed
     * @author xl
     * 获取老师各类总请假次数
     */
    public function countTeacherLeaveAll($teacher_id, $type);

    /**
     * @param $teacher_id
     * @param $time_day
     * @return mixed
     * @author xl
     * 获取当天请假时间段
     */
    public function getLeaveTime($teacher_id, $time_day);

    /**
     * @param $week
     * @param $time_class
     * @return mixed
     * @author xl
     * 获取有指定星期请假的列表
     */
    public function getWeekRest($week, $time_day);
}