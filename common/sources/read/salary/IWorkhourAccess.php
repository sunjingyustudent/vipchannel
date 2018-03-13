<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:05
 */

namespace common\sources\read\salary;

interface IWorkhourAccess {

    /**
     * @param $long
     * @param $time
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 根据老师ID，课时获取课时费
     */
    public function getHourFee($long, $time_class, $tid);

    /**
     * @param $teacher_id
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取老师一个月的课时提成
     */
    public function getClassCommission($teacher_id, $timeStart, $timeEnd);

    /**
     * @param $teacher_id
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取老师一个月的提成列表
     */
    public function getClassCommissionList($teacher_id, $timeStart, $timeEnd);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取老师月课时费总计
     */
    public function getClassCommissionTotal($timeStart, $timeEnd);

    /**
     * @param $class_id
     * @return mixed
     * @author xl
     * 根据class_id获取当节课时费
     */
    public function getHourFeeByClassId($class_id);

    /**
     * @param $teacher_id
     * @param $instrument_id
     * @param $time_class
     * @param $time_long
     * @return mixed
     * @author xl
     * 根据课程获取老师课时费
     */
    public function getClassMoney($teacher_id, $instrument_id, $time_class, $class_long);
}