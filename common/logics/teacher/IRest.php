<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午3:17
 */

namespace common\logics\teacher;

interface IRest {

    /**
     * @return mixed
     * @author xl
     */
    public function editTeacherLeave($request);

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 获取请假日历
     */
    public function getCalendar($request);

    /**
     * @param $teacher_id
     * @param $timeDay
     * @return mixed
     * @author xl
     *
     */
    public function getTeacherLeaveByTeacher($teacher_id, $timeDay);

    /**
     * @return mixed
     * @author xl
     * 删除请假记录
     */
    public function deleteTeacherLeave($request);

    /**
     * @param $teacher_id
     * @param $timeDay
     * @return mixed
     * @author xl
     * 获取指定老师请假条数
     */
    public function getLeaveCount($teacher_id, $timeDay);

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 编辑请假（有日课表）
     */
    public function editTeacherLeave1($request);

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 删除请假（有日课表）
     */
    public function deleteLeave1($request);

}