<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午3:17
 */

namespace common\compute;

interface ISalaryCompute {

    /**
     * @param $teacher_id
     * @param $time
     * @param $long
     * @param $reward_id
     * @return mixed
     * @author xl
     * 根据奖惩下拉规则计算结果
     */
    public function calculateSalary($teacher_id, $time, $long, $class_id, $reward_id);

    /**
     * @param $teacher_id
     * @param $timeStart
     * @param $timeEnd
     * @param $rate
     * @return mixed
     * @author xl
     * 全勤奖/缺勤计算规则
     */
    public function calculateAttendance($teacher_id, $timeStart, $timeEnd, $rate);

    /**
     * @param $teacher_id
     * @param $time_day
     * @return mixed
     * @author xl
     * 计算老师时薪
     */
    public function computeHourFee($teacher_id, $time_day);

    /**
     * @param $teacher_id
     * @param $time_day
     * @return mixed
     * @author xl
     * 获取老师时薪
     */
    public function getHourFee($teacher_id, $time_day);

    /**
     * @param $teacher_id
     * @param $time_day
     * @return mixed
     * @author xl
     * 计算老师昨天的时薪，课时费
     */
    public function computeSalary($teacher_id, $time_day);

    /**
     * @param $teacher_id
     * @param $time_day
     * @return mixed
     * @author xl
     * 获取老师的工作时间段
     */
    public function getTeacherFixTimeAll($teacher_id, $time_day);

    /**
     * @param $teacher_id
     * @param $time_day
     * @return mixed
     * @author xl
     * 获取老师指定日期周固定时间
     */
    public function getTeacherFixTimeByWeek($teacher_id, $time_day);

    /**
     * @param $teacher_id
     * @param $instrument_id
     * @param $time_class
     * @param $time_long
     * @return mixed
     * @author xl
     * 通过课程获取课时费
     */
    public function getTeacherClassMoney($teacher_id, $instrument_id, $time_class, $class_long);

    /**
     * @param $teacher_id
     * @param $time_start
     * @param $time_end
     * @return mixed
     * @author xl
     * 获取不在固定时间内的时长
     */
    public function getOverTime($teacher_id, $time_start, $time_end);

    /**
     * @param $teacher_id
     * @param $time_day
     * @return mixed
     * @author xl
     * 获取老师指定日期的请假时间
     */
    public function getRestTime($teacher_id, $time_day);

    /**
     * @param $time_start  课开始时间
     * @param $time_end     课结束时间
     * @return mixed
     * @author xl
     * 获取这节课不可出勤的老师列表
     */
    public function getTeacherNotAvailableByClass($time_class, $time_end);

    /**
     * @param $week
     * @param $time_class
     * @param $time_end
     * @return mixed
     * @author xl
     * 获取固定时间可利用老师列表
     */
    public function getAvailableFixWeek($week, $time_class, $time_end);

    /**
     * 获取老师时段信息按执行时间排序
     * @param $teacherId
     * @param $week
     * @return array
     */
    public function getTeacherFixedTimeRowOrderByExeTime($teacher_id,$week);
}