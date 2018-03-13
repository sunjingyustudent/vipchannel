<?php
/**
 * Created by phpStorm.
 * User: xl
 * Date: 2017/2/23
 * Time: 18:26
 */
namespace common\sources\write\teacher;

interface ITrainAccess {

    /**
     * @param $name
     * @param $time_start
     * @param $time_end
     * @return mixed
     * @author xl
     * 添加新建班级
     */
    public function addClass($name, $time_start, $time_end);

    /**
     * @param $member_list
     * @param $class_id
     * @return mixed
     * @author xl
     * 更新待培训学生的class_id
     */
    public function updateUserTeacherClassId($user_id, $class_id);

    /**
     * @param $trace_id
     * @param $name
     * @param $mobile
     * @param $school
     * @param $major
     * @param $grade
     * @param $hasPad
     */
    public function editTrace($trace_id, $name, $mobile, $school, $major, $grade, $hasPad);

    /**
     * @param $trace_id
     * @param $name
     * @param $mobile
     * @param $school
     * @param $major
     * @param $grade
     * @param $hasPad
     * @param $opern
     * @param $opern_score
     * @param $command
     * @param $command_score
     * @param $listen_score
     * @param $line_score
     * @param $age_score
     * @param $num_score
     * @param $skill_score
     * @return mixed
     * @author xl
     * 培训老师页面编辑
     */
    public function editTraceNew($trace_id, $name, $mobile, $school, $major, $grade, $hasPad, $type, $opern, $opern_score, $command, $command_score, $listen_score, $line_score, $age_score, $num_score, $skill_score);

    /**
     * @param $class_id
     * @return mixed
     * @author xl
     * 更新班级信息通过ID
     */
    public function editClassById($cid, $name, $time_start, $time_end);

    /**
     * @param $class_id
     * @return mixed
     * @author xl
     * 将原来所属class_id清空
     */
    public function updateAllClassIdByClassId($class_id);

    /**
     * @param $class_id
     * @return mixed
     * @author xl
     * 删除班级
     */
    public function deleteClass($class_id);

    /**
     * @param $user_id
     * @param $instrument_id
     * @param $level
     */
    public function addSchoolInstrument($user_id, $instrument_id, $grade, $level);

    /**
     * @param $trace_id
     * @param $type
     * @param $listen_score
     * @param $age_score
     * @param $num_score
     * @param $line_score
     * @param $skill_score
     * @param $type_score
     * @param $opern_score
     */
    public function updateTraceByScore($trace_id, $status, $listen_score, $line_score, $age_score, $num_score, $skill_score, $type, $opern, $opern_score, $command, $command_score);

    /**
     * @param $uid
     * @return mixed
     * @author xl
     * 更新成员的班级ID
     */
    public function allotClass($uid, $class_id);

    /**
     * @param $name
     * @param $time_created
     * @param $time_updated
     * @return mixed
     * @author hll
     * 添加学校
     */
    public function addSchool($name, $time_created, $time_updated);

    /**
     * @param $cid
     * @param $name
     * @param $time_updated
     * @return mixed
     * @author hll
     * 更新学校的名字
     */
    public function editSchoolById($cid, $name, $time_updated);

}