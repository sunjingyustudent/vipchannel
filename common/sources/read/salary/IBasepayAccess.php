<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:05
 */

namespace common\sources\read\salary;

interface IBasepayAccess {

    /**
     * @param $teacher_id
     * @param $time
     * @return mixed
     * @author xl
     * 获取老师的日薪
     */
    public function getTeacherDaySalary($teacher_id, $timeDay);

    /**
     * @param $teacher
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取老师月薪
     */
    public function getTeacherMonthSalary($teacher_id, $timeStart, $timeEnd);

    /**
     * @param $teacher_id
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取老师一个月底薪详情列
     */
    public function getTeacherMonthSalaryList($teacher_id, $timeStart, $timeEnd);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取月老师底薪总计
     */
    public function getSalaryTotal($timeStart, $timeEnd);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 查询这个月是否发布过
     */
    public function isPublish($timeStart, $timeEnd);

    /**
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 查询老师薪资调整记录
     */
    public function getSalaryLogByTeacherId($teacher_id);

    /**
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 获取老师最新时薪
     */
    public function getTeacherLastHourSalary($teacher_id);

    /**
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 通过老师ID获取老师等级及薪资信息
     */
    public function getTeacherBasePay($teacher_id);

    /**
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 获取老师等级调整记录
     */
    public function getTeacherInstrumentLog($teacher_id);

    /**
     * @param $grade
     * @param $level
     * @return mixed
     * @author xl
     * 通过等级获取基础薪资
     */
    public function getBasicSalaryByGrade($teacher_type, $school_id, $grade, $level, $time_day);

    /**
     * @param $teacher_id
     * @param $time_day
     * @return mixed
     * @author xl
     * 获取老师时薪
     */
    public function getHourFee($teacher_id, $time_day);
}