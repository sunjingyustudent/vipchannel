<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:05
 */

namespace common\sources\read\teacher;

interface IWorktimeAccess {

    /**
     * @param $teacher_id
     * @param $week
     * @return mixed
     * @author xl
     * 获取老师指定周时间
     */
    public function getTeacherFixedTime($teacher_id, $week);

    /**
     * @param $teacher_id
     * @param $week
     * @return mixed
     * @author xl
     * 获取学生固定老师时间
     */
    public function getTeacherClassFixTime($teacher_id, $week);

    /**
     * @param $teacher_id
     * @param $weekDay
     * @param $num
     * @return mixed
     * @author xl
     * 获取固定学生姓名
     */
    public function getFixedTimeStudentName($teacher_id, $weekDay, $num);

    /**
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 获取老师周课表时间
     */
    public function getTeacherFixedTimeAll($teacher_id);

    /**
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 获取老师周课表执行时间
     */
    public function getTeacherFixedTimeExecuteTime($teacher_id, $time_day);

    /**
     * @param $teacher_id
     * @param $timeDay
     * @return mixed
     * @author xl
     * 获取老师日课表bit
     */
    public function getTeacherDayTime($teacher_id, $timeDay);

    /**
     * @param $teacher_id
     * @param $week
     * @return mixed
     * @author xl
     * 获取小于时间的最新的所有星期固定时间
     */
    public function getTeacherFixTimeAll($teacher_id, $time_day);

    /**
     * @param $teacher_id
     * @param $week
     * @return mixed
     * @author xl
     * 获取老师指定星期的最新固定时间
     */
    public function getTeacherFixTimeByWeek($teacher_id, $week, $time_day);

    /**
     * @param $week
     * @param $class_bit
     * @param $time_class
     * @return mixed
     * @author xl
     * 获取周固定生效时间小于课程开始时间列表
     */
    public function getAvailableListByClass($week, $time_class);

    /**
     * @return mixed
     * @author xl
     * 获取所有老师指定星期周固定时间
     */
    public function getTeacherFixTimeList($week);

    /**
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 获取老师周固定时间生效记录
     */
    public function teacherFixTimeRecord($teacher_id);

    /**
     * @param $teacher_id
     * @param $time_execute
     * @return mixed
     * @author xl
     * 获取老师当前执行时间的下一个执行时间
     */
    public function getNextTimeExecute($teacher_id, $time_execute);
}