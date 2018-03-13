<?php
    /**
     * Created by PhpStorm.
     * User: xl
     * Date: 16/12/16
     * Time: 10:00
     */
namespace common\sources\read\teacher;


interface ITeacherAccess
{

    /**
     * 查询课程信息的进入界面的可选老师信列表
     * @author 王可
     */
    public function  getCourseIndex();

    /**
     * 搜索老师输入框中选择老师
     * @author 王可
     */
    public function searchTeacherName($name);

    /**
     * @return mixed
     * create by wangke
     * 查询当日老师课时信息
     */

    public function queryTeacherTodayclass($startTime,$endTime);

    /**
     * @param $id
     * @return mixed
     * create by wangke
     * 根据id 查询老师信息
     */
    public function geTeacherById($id);
    

    /**
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 通过老师ID获取乐器等级
     */
    public function getInstrumentByTeacherId($teacher_id);


    /**
     * @return mixed
     * @author xl
     * 获取可用老师列表
     */
    public function getTeacherInfo($type, $level);

    /**
     * @param $teacher_id
     * @param $week
     * @return mixed
     * @author xl
     * 获取老师指定周的上课时间
     */

    public function getTeacherFixTimeByWeek($teacher_id, $week);


    /**
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 获取指定老师的周上课时间
     */
    public function getTeacherFixTime($teacher_id);

    /**
     * @param $teacher_id
     * @param $time
     * @return mixed
     * @author xl
     * 获取老师指定日的上课时间
     */
    public function getTeacherDayTime($teacher_id, $time);

    /**
     * @param $teacher_id
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取老师工资调整记录最近一条记录的日薪
     */
    public function getTeacherMaxSalaryTime($teacher_id, $timeEnd);

    /**
     * @param $teacher_id
     * @param $salary_time
     * @return mixed
     * @author xl
     * 获取老师的日薪（底薪生效时间小于月开始时间）
     */
    public function getTeacherSalary($teacher_id, $salary_time);

    /**
     * @param $teacher_id
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取当月老师薪资调整记录
     */
    public function getMonthSalaryList($teacher_id, $timeEnd);

    /**
     * @param $filter
     * @param $page_num
     * @return mixed
     * @author xl
     * 通过筛选账号/手机号筛选老师
     */
    public function getTeacherListByName($filter, $page_num);

    /**
     * @param $filter
     * @return mixed
     * @author xl
     * 通过账号/手机号,分页筛选老师
     */
    public function getTeacherPageByName($filter);

    /**
     * @param $teacher_id
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取老师一段时间内请假天数
     */
    public function getRestMount($teacher_id,$timeStart,$timeEnd);

    /**
     * @param $name
     * @param $type
     * @return mixed
     * create by wangke
     * 查询所有的老师的条数
     */
    public function queryOverTimeCount($timeStart,$timeEnd,$name, $type);

    /**
     * @param $name
     * @param $type
     * @param $page_num
     * @return mixed
     * create by wangke
     * 查询当前月份所有加班的老师  加班老师表
     */
    public function queryTeacherList($timeStart,$timeEnd,$name, $type, $page_num);


    /**
     * @param $teacherid
     * @return mixed
     * create by wangke
     * 查询老师的周课表
     */
    public function queryTeacherWeekInfo($teacherid);

    /**
     * @param $teacherid
     * @return mixed
     * create by wangke
     * 查询老师的日课表
     */
    public function queryTeacherDayInfo($teacherid);


    /**
     * @param $day_firsttime
     * @param $day_endtime
     * @return mixed
     * create by wangke
     *查询老师的某一天实际上课时间
     */
    public function queryTeacherClassList($day_firsttime, $day_endtime,$teacherid);


    /**
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * create by wangke
     * 查询所有的老师的加班情况
     */
    public function queryTeacherOvertimeList($timeStart, $timeEnd);

    /**
     * @param $teacher_id
     * @return mixed
     * create by wangke
     * 查询已处理的加班信息
     */
    public function getOvertimeRewardInfo($timeStart,$timeEnd,$teacher_id);

    /**
     * @param $base
     * @param $work
     * @param $filter
     * @return mixed
     * @author xl
     * 薪资核算筛选老师列表
     */
    public function getSalaryTeacher($base, $work, $filter);

    /**
     * @param $word_key
     * @param $work_type
     * @param $base_type
     * @return mixed
     * @author xl
     * 通过筛选条件获取老师count
     */
    public function getTeacherByKeyCount($word_key, $work_type, $base_type);

    /**
     * @return mixed
     * @author xl
     * 通过筛选条件获取老师列表
     */
    public function getTeacherByKeyInfo($word_key, $work_type, $place_type, $page_num);

    /**
     * @return mixed
     * @author xl
     * 通过筛选条件获取未推送的老师列表
     */
    public function getUnPushTeacher($timeStart, $timeEnd, $base, $work, $filter);

    /**
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 获取老师的等级
     */
    public function getTeacherInstrument($teacher_id);

    /**
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 通过ID获取老师信息
     */
    public function getTeacherInfoById($teacher_id);

    /**
     * @param $filter
     * @return mixed
     * @author xl
     * 通过姓名筛选老师
     */
    public function getTeacherListByKey($filter);

    /**
     * @param $teacher_id
     * @return mixed
     * @author sjy
     * 获取当前老师薪资生效时间
     */
    public function getSalaryTime($teacher_id);

    /**
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 获取老师奖励
     */
    public function getResumeById($teacher_id);

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
     * 获取老师的负责学校字段
     */
    public function getResponsibleSchoolById($teacher_id);
    
    /**
     * @param $teacher_id
     * @return mixed
     * @author sjy
     * 获取乐器种类信息
     */
    public function getInstrument();
    
     /**
     * @param $teacher_id
     * @return mixed
     * @author sjy
     * 查找手机号是否存在
     */
    public function checkMobile($mobile, $userId, $role);

    /**
     * @param $teacher_id
     * @return array
     * @author 小黑
     * 查找手机号是否存在
     */
    public function getTeacherInstrumentInfo($teacher_id);

    /**
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 获取老师乐器等级以及微调值
     */
    public function getTeacherInstrumentNew($teacher_id);

    /**
     * @param $instrument_id
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 通过乐器获取微调值
     */
    public function getWeiSalaryByInstrument($teacher_id, $instrument_id);

    /**
<<<<<<< HEAD
     * @param $filter
     * @return mixed
     * @author xl
     * 好评数据分析获取老师count
     */
    public function getGoodAnalysisTeacherCount($filter);

    /**
     * @param $filter
     * @return mixed
     * @author xl
     * 好评数据分析获取老师ID列表
     */
    public function getGoodAnalysisTeacherIds($filter, $page_num);
    
    /**
     * @param $teacher_id
     * @return mixed
     * @author yh
     * 通过teacher_id获取老师类型
     */
    public function getTeacherTypeOpenidById($teacher_id);
    
    /**
     * @param $av_list
     * @param $student_teacher_fix_exit
     * @return mixed
     * @author xl
     * 获取老师姓名通过学生固定时间条件
     */
    public function getTeacherNameByCondition($av_list, $student_teacher_fix_exit, $instrument_type, $filter_name);

    /**
     * @param $teacher_id
     * @param $instrument_id
     * @return mixed
     * @author xl
     * 通过老师乐器获取等级
     */
    public function getTeacherGradeByInstrument($teacher_id, $instrument_id);


    /**
     * 判断老师是否工作
     * @param   $teacher_id
     * @param   $week
     * @param   $bit
     * @return  int
     * create by  wangkai
     * create time  2017/5/4
     */
    public function isWorkTeacher($teacher_id, $week, $bit);
}