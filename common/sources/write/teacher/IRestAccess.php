<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:05
 */

namespace common\sources\write\teacher;

interface IRestAccess {

    /**
     * @param $rest_id
     * @param $reward_record_id
     * @return mixed
     * @author xl
     * 修改临时请假表tag值
     */
    public function updateRestTag($rest_id, $reward_record_id);

    /**
     * @param $teacher_id
     * @param $leaveType
     * @param $timeDay
     * @param $time_start
     * @param $time_end
     * @return mixed
     * @author xl
     * 添加请假记录
     */
    public function addTeacherLeave($teacher_id, $leaveType, $timeDay, $time_start, $time_end);

    /**
     * @param $teacher_id
     * @param $timeDay
     * @return mixed
     * @author xl
     * 删除请假记录
     */
    public function deleteLeave($teacher_id, $timeDay);
}