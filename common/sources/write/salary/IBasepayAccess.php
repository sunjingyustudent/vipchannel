<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:05
 */

namespace common\sources\write\salary;

interface IBasepayAccess {

    /**
     *  插入reward_record表  插入信息表
     *  @param teacher_id   int
     *  @param reward_id    int
     *  @param month_time   int
     *  @param text         varchar
     *  @param remark       varchar
     *  @param type         str
     *  @param prefix       int
     *  @param money        int
     */
    public function doAddRewardRecord($teacher_id, $reward_id, $month_time, $text, $remark, $type, $prefix, $money,$createtime);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 更新底薪为已发布
     */
    public function updateIsPublish($timeStart, $timeEnd);
    
    /**
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author sjy
     * 添加一条薪资改变记录
     */
    public function addSalaryLog($workType,$kefuId,$teacher_id,$salarybefore,$salaryAfter,$salary_25,$salary_45,$salary_50,$salaryTime,$hour_time,$allduty_award_rates,$absence_punished_rates,$allduty_time,$absence_time);

    /**
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 添加之前先删除老师所有乐器信息
     */
    public function deleteInstrumentSalary($teacher_id);

    /**
     * @param $teacher_id
     * @param $instrument_id
     * @param $grade
     * @param $level
     * @param $hour_hour_first
     * @param $hour_second
     * @param $hour_third
     * @param $salary
     * @return mixed
     * @author xl
     * 添加老师乐器及薪资
     */
    public function addInstrumentSalary($teacher_id, $instrument_id, $grade, $level, $hour_first, $hour_second, $hour_third, $salary);

    /**
     * @param $teacher_id
     * @param $instrument_id
     * @param $grade
     * @param $level
     * @param $hour_hour_first
     * @param $hour_second
     * @param $hour_third
     * @param $salary
     * @return mixed
     * @author xl
     * 添加老师乐器及薪资
     */
    public function addInstrumentSalaryLog($teacher_id, $instrument_id, $grade, $level, $hour_first, $hour_second, $hour_third, $salary);
}