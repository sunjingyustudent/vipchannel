<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午3:17
 */

namespace common\logics\salary;

interface ISalary {

    /**
     * @param $time
     * @return mixed
     * @author xl
     * 获取薪资核算列表
     */
    public function getTeacherWagesList($month_time, $base, $work, $filter);

    /**
     * @param $teacher_id
     * @param $month
     * @param $type
     * @return mixed
     * @author xl
     * 查新老师薪资详情
     */
    public function showDetail($teacher_id, $month, $type);

    /**
     * @param $month
     * @return mixed
     * @author xl
     * 获取每个月各项指标金额
     */
    public function getMonthTotalMoney($month);

    /**
     * @param $month
     * @return mixed
     * @author xl
     * 确认发布薪资
     */
    public function confirmSalary($month);

    /**
     * @param $month
     * @return mixed
     * @author xl
     * 查询当月薪资是否发布过
     */
    public function isPublish($month);

    /**
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 获取老师薪资调整记录
     */
    public function getSalaryLogByTeacherId($teacher_id);

    /**
     * @param $month_time
     * @param $base
     * @param $work
     * @param filter $
     * @return mixed
     * @author xl
     * 导出工资表
     */
    public function exportSalary($month_time, $base, $work, $filter);

    /**
     * @param $time
     * @return mixed
     * @author 小黑
     * 获取薪资核算列表（New）
     */
    public function getTeacherWagesListNew($month_time, $base, $work, $filter);
}