<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/13
 * Time: 上午11:08
 */
namespace common\sources\write\teacher;


interface ITeacherAccess {

    /**
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 删除老师
     */
    public function deleteTeacher($teacher_id);
    
    /**
     * @param $teacher_id
     * @return mixed
     * @author sjy
     * 修改老师薪资信息
     */
    public function updateTeacherSalaryInfo($teacher_id,$allduty_award_rates,$absence_punished_rates,$allduty_time,$absence_time,$salaryAfter,$salaryTime,$salary_25,$salary_45,$salary_50,$hour_time);
       
    /**
     * @param $teacher_id
     * @return mixed
     * @author sjy
     * 删除老师乐器
     */
    public function deleteTeacherInstrument($teacher_id);

    /**
     * @param $teacher_id
     * @return mixed
     * @author sjy
     * 添加老师乐器
     */
    public function addTeacherInstrument($userId, $instrument, $type, $level);

    /**
     * @param $teacher_id
     * @param $key
     * @return mixed
     * @author xl
     * 编辑老师头像
     */
    public function editTeacherHead($teacher_id, $key);

    /**
     * @param $teacher_id
     * @param $key
     * @return mixed
     * @author xl
     * 更新老师的推送状态
     */
    public function editTeacherPush($timeStart,$timeEnd,$teacher_id);
    
    /**
     * @param $teacher_id
     * @return mixed
     * @author sjy
     * 添加老师基本信息
     */
    public function addTeacher($employedtime,$name,$show_name,$mobile,$password,$gender,$placeId,$workType,$token,$is_test,$work_new,$type_new,$salary_rate,$teacher_type,$school_id,$openid,$style,$sounds,$teacher_experience,$responsible_school);

    
    /**
     * @param $teacher_id
     * @return mixed
     * @author sjy
     * 更新老师基本信息
     */
    public function updateTeacher($employedtime,$teacher_id,$name,$show_name,$mobile,$password,$gender,$placeId,$workType,$token,$is_test,$work_new,$type_new,$salary_rate,$teacher_type,$school_id,$style,$sounds,$teacher_experience,$responsible_school);

    /**
     * @param $teacher_id
     * @param $resume
     * @return mixed
     * @author xl
     * 编辑老师简历
     */
    public function doEditResume($teacher_id, $resume);

    /**
     * @param $teacher_id
     * @param $show_name
     * @return mixed
     * @author xl
     * 编辑老师昵称
     */
    public function doEditShowName($teacher_id, $show_name);

    /**
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 审核老师通过
     */
    public function doEditFormal($teacher_id);
}