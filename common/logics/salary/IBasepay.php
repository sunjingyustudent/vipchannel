<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午3:17
 */

namespace common\logics\salary;

interface IBasepay {

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 根据周固定时间获取老师日薪
     */
    public function getTeacherDaySalaryByWeek($request);

    /**
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 通过老师ID获取等级及课时费等
     */
    public function getTeacherBasePay($teacher_id);

    /**
     * @return mixed
     * @author hll
     * 获取乐器，基础等级及课时费等
     */
    public function getInstrumentBaseSalary();

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 添加老师乐器及薪资
     */
    public function addSalaryByInstrument($request);

    /**
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 获取老师等级调整记录
     */
    public function getTeacherInstrumentLog($teacher_id);

    /**
     * @param $level
     * @param $grade
     * @return mixed
     * @author xl
     * 通过等级获取课时费等
     */
    public function getSalaryByInstrument($teacher_id, $grade, $level);

    /**
     * @param $teacher_type
     * @param $school_id
     * @param $grade
     * @param $level
     * @return mixed
     * @author hll
     * 通过乐器等级老师类型学校获取薪资
     */
    public function getSalaryByInstrumentType($teacher_type, $school_id, $grade, $level);
}