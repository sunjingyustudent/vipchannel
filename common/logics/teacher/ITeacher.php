<?php
/**
 * Created by PhpStorm.
 * User: xl
 * Date: 16/12/15
 * Time: 17:47
 */
namespace common\logics\teacher;


interface ITeacher {

    /**
     * @return mixed
     * @author xl
     * 老师奖惩规则首页
     */
    public function rewardIndex();

    /**
     * @return mixed
     * @author xl
     * 添加奖惩规则Modal
     */
    public function addReward();

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 添加奖惩规则操作
     */
    public function doAddReward($request);

    /**
     * @return mixed
     * @author xl
     * 编辑奖惩规则Modal
     */
    public function editReward($reward_id);


    /**
     * @param $reward_id
     * @return mixed
     * create by wangke
     * 获得加班使用的奖励规则
     */
    public function getOvertimeRewardInfoById($reward_id);

    /**
     * @param $request
     * @return mixed
     * create by wangke
     * 添加加班请求规则
     */
    public function doAddOvertimeRewardRest($request);

    /**
     * @param $word_key
     * @param $work_type
     * @param $place_type
     * @return mixed
     * @author xl
     * 通过筛选条件获取老师count
     */
    public function getTeacherByKeyCount($word_key, $work_type, $place_type);

    /**
     * @return mixed
     * @author xl
     * 通过筛选条件获取老师列表
     */
    public function getTeacherByKeyInfo($word_key, $work_type, $place_type, $page_num);

    /**
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 通过ID获取老师信息
     */
    public function getTeacherInfoById($teacher_id);

    /**
     * @param $teacer_id
     * @return mixed
     * @author xl
     * 删除老师
     */
    public function deleteTeacher($teacher_id);


    /**
     * @param $request
     * @return mixed
     * @author xl
     * 编辑老师头像
     */
    public function doEditHead($teacher_id);
    
    /**
     * @param 
     * @return mixed
     * @author sjy
     * 添加老师基本信息
     */
    public function addTeacher($employedtime,$name,$mobile,$password,$gender,$placeId,$workType,$token,$salary_rate,$teacher_type,$school_id);

    /**
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 获取老师的简历
     */
    public function getResumeById($teacher_id);

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
     * @return mixed
     * @author xl
     * 获取老师昵称
     */
    public function getShowNameById($teacher_id);

    /**
     * @param $teacher_id
     * @return mixed
     * @author hll
     * 获取老师负责学校字段
     */
    public function getResponsibleSchoolArray($teacher_id);

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
     * 编辑老师为审核通过
     */
    public function doEditFormal($teacher_id);
    
    /**
     * @param 
     * @return mixed
     * @author sjy
     * 获取乐器种类信息
     */
    public function getInstrument();
    
    /**
     * @param 
     * @return mixed
     * @author sjy
     * 编辑基本老师
     */
    public function editTeacher($kefuId,$teacher_id,$name,$show_name,$mobile,$password,$teacherLevel,$instrumentLevel,$gender,$placeId,$workType,$employedtime,$is_test,$token,$type,$work_new,$type_new,$salary_rate,$teacher_type,$school_id,$style,$sounds,$teacher_experience,$responsible_school);
    
    /**
     * @param 
     * @return mixed
     * @author sjy
     * 添加老师
     */
    public function addTeacherInfo($kefuId,$teacher_id,$name,$show_name,$mobile,$password,$teacherLevel,$instrumentLevel,$gender,$placeId,$workType,$employedtime,$is_test,$token,$type,$work_new,$type_new,$salary_rate,$teacher_type,$school_id,$style,$sounds,$teacher_experience,$responsible_school);

    /**
     * @param $name
     * @param $show_name
     * @param $mobile
     * @param $password
     * @param $instrumentLevel
     * @param $gender
     * @param $placeId
     * @param $workType
     * @param $employedtime
     * @param $is_test
     * @param $token
     * @param $type
     * @return mixed
     */
    public function addTeacherInfoAndInstrumentAndFixTime($instrumentLevel,$name,$show_name,$mobile,$password,$gender,$placeId,$workType,$employedtime,$is_test,$token,$work_new,$type_new,$salary_rate,$teacher_type,$school_id,$openid);

    /**
     * @param 
     * @return mixed
     * @author sjy
     * 添加薪资信息
     */
    public function editSalaryInfo($kefuId,$teacher_id,$allduty_award_rates,$absence_punished_rates,$allduty_time,$absence_time,$salaryAfter,$salaryTime,$salary_25,$salary_45,$salary_50,$hour_time);
        
    /**
     * @param 
     * @return mixed
     * @author sjy
     * 获取老师的乐器
     */
    public function getTeacherInstrument($teacher_id);    

    /**
     * @param 
     * @return mixed
     * @author sjy
     * 查找用户手机号是否存在
     */
    public function checkMobile($mobile,$userId = 0,$role = -1);

    /**
     * @param $time
     * @param $filter
     * @return mixed
     * @author xl
     * 请假删除删选老师count
     */
    public function selectTeacher($filter);

    /**
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 根据老师ID获取薪资信息
     */
    public function getTeacherSalaryInfo($teacher_id);

    /**
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 通过老师ID获取老师OPEN_ID
     */
    public function getTeacherOpenId($teacher_id);
}