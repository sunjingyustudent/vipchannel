<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:05
 */

namespace common\sources\write\teacher;

interface IWorktimeAccess {

    /**
     * @param $teacherId
     * @param $week
     * @param $timeBit
     * @param $timeExecute
     * @return mixed
     * @author xl
     * 添加老师周课表
     */
    public function addTeacherFixedTime($teacherId, $week, $timeBit, $timeExecute);

    /**
     * @param $teacher_id
     * @param $timeDay
     * @param $timeBit
     * @return mixed
     * @author xl
     * 添加老师日课表
     */
    public function addTeacherDayTime($teacher_id, $timeDay, $timeBit);

    /**
     * @param $teacherId
     * @param $week
     * @param $timeBit
     * @param $timeExecute
     * @return mixed
     * @author xl
     * 添加老师周课表
     */
    public function addTeacherFixedTimeLog($teacher_id, $week, $timeBit, $timeExecute);

    /**
     * @param $teacher_id
     * @param $timeExecute
     * @return mixed
     * @author xl
     * 添加校招老师周课表
     */
    public function addNewTeacherFixedTime($teacherId, $timeExecute);

    /**
     * @param $teacherId
     * @param $week
     * @param $timeBit
     * @param $timeExecute
     * @return mixed
     * @author xl
     * 添加校招老师周课表log
     */
    public function addNewTeacherFixedTimeLog($teacher_id, $timeExecute);
}