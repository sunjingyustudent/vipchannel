<?php

/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/19
 * Time: 下午2:37
 */

namespace common\logics\classes;

use Yii;
use yii\base\Object;

interface IClasses
{

    /**
     * 获得课程信息的进入界面
     * @author 王可
     */
    public function getCourseIndex();

    /*
     * 获得课程的列表信息条数
     * @author 王可
     */

    public function getClassesListCount();

    /**
     * @param $page
     * @param $day
     * @param $type
     * @param $teacher
     * @return mixed
     *
     * create by wangke
     * 得到课程性的列表详细细腻
     */
    public function getClassesListInfo();

    /**
     * @param $name
     * @return mixed
     * create by wangke
     * 搜索老师输入框中选择老师
     */
    public function searchTeacherName($name);

    /**
     * @return mixed
     * create by wangke
     * 获得当日老师课时信息
     */
    public function queryTeacherTodayclass();

    /**
     * @return mixed
     * create by wangke
     * 获得  查看课时  时信息
     */
    public function queryViewclass();

    /**
     * @param $req
     * @param $classid
     * @param $logid
     * @return mixed
     *
     * create by wangke
     * 1，根据$class_id删除错误课表的数据(其实是修改，把状态改为删除)
     * 2，根据$req判断是不是post数据
     * 3，根据logid找到是否存在logid，然后记录
     */
    public function delFailClass($req, $classid, $logid);

    /**
     * @param $classIds
     * @param $logid
     * @return mixed
     *
     * create by wangke
     * 根据多个id批量删除错误课表的数据
     */
    public function delFailClassALL($classIds, $logid);

    /**
     * @param $classIds
     * @param $logid
     * @return mixed
     *
     * create by wangke
     * 错误课表 得到需要更改老师的课程信息
     */
    public function queryNeedChangeFailclass($id, $time);

    /**
     * @return mixed
     *
     * create by wangke
     * 点击顶部菜单‘预约体验课’时需要显示的信息(后台登录 role=0)
     */
    public function queryExclassList();

    //课程安排-乐谱的图片
    public function getCourseImage($courseId);

    /**
     * @param $request
     * @param $logid
     * @return mixed
     *
     *  create by wangke
     * 进入课程监控查询信息条数 在页面右上角
     */
    public function monitorClassCount();

    /**
     * @return mixed
     * create by wangke
     * 进入课程监控查询信息
     */
    public function monitorClassList();

    /**
     * @param $class_id
     * @param $type
     * @return mixed
     * create by wangke
     *
     * 修改课表信息的连接
     */
    public function editClassContact($classId, $type);

    /**
     * @param $req
     * @return mixed
     * create by wangke
     *
     * 在监控中测试 同步学生的信息
     */
    public function synAccount($req);

    public function sendMessageWechat($classid, $openid);

    /**
     * @param $class_id
     * @return mixed
     * created by Jhu
     * 获取课单信息
     */
    public function getClassRecordInfo($classId);

    /**
     * @param $day
     * @return mixed
     * create by wangke
     * 复购的课程名单条数
     */
    public function countPurchaseCourse($day);

    /**
     * @param $day
     * @param $num
     * @return mixed
     * create by wangke
     * 复购的课程名单列表信息
     */
    public function getPurchaseCourseList($day, $num);

    /**
     * @param $student_id
     * @return mixed
     * create by wangke
     * 进入排课信息操作界面
     */
    public function getClassEditInfo($studentId);

    /**
     * 获取全部的老师昵称
     * @param   $keyword  mixed
     * @return  array
     */
    public function getTeacherByName($keyword = '');

    /**
     * 批量删除课程页面
     * @param    $student_id
     * @return   array
     */
    public function unfinishedClass($studentId);

    /**
     * 日历切换
     * @param  $request array
     * @return array
     */
    public function getCalendar($request);

    /**
     * 添加课程页面
     */
    public function addClassPage($request);

    /**
     * 课程时长判断选择
     * @param  $type
     * @return str
     */
    public function getClassLengthByClassType($type);

    /**
     * 修饰时间板式
     * @param  $week
     * @return str
     */
    public function timeClassFormat($week);

    /**
     * 取消课程页面
     * @param $class_id  int
     * @return array
     */
    public function getCancelClassPage($classId);

    /**
     * @param
     * @return mixed
     * create by sjy
     * 获取课表信息数量
     */
    public function getCourseCount($type, $timeStart, $timeEnd, $passId, $tag, $filte);

    /**
     * @param
     * @return mixed
     * create by sjy
     * 获取课表信息list
     */
    public function getCourseInfo($type, $timeStart, $timeEnd, $passId, $pageNum, $tag, $filter);

    /**
     * @param  $class_id
     * @return mixed
     * create by sjy
     * 获取课程信息
     */
    public function getClassInfo($classId);

    /**
     * @param $teacher_id
     * @param $course_filter
     * @param $status_filter
     * @return mixed
     * @author xl
     * 获取老师上课记录page
     */
    public function teacherClassRecordPage($teacherId, $courseFilter, $statusFilter);

    /**
     * @param $teacher_id
     * @param $course_filter
     * @param $status_filter
     * @param $page_num
     * @return mixed
     * @author xl
     * 获取老师上课记录list
     */
    public function teacherClassRecordList($teacherId, $courseFilter, $statusFilter, $pageNum);

    /**
     * 发送课程留言
     * @param  $classId
     * @param  $logid
     * @return array
     */
    //public function getSendClassMessage($classId, $logid);

    /**
     * 获取正在上课的数量
     * @param  $keyword
     * @return int
     */
    public function getClassCheckPage($keyword);

    /**
     * 获取正在上课的列表信息
     * @param  $keyword
     * @param  $num
     * @return array
     */
    public function getClassCheckList($keyword = '', $num = 1);

    /**
     * 获取老师时间表
     * @param  $class_id
     * @return array
     */
    public function timetableEditClass($classId);

    /**
     * 课后记录列表
     * @param $student_id  int
     * @param $num         int
     */
    public function getClassRecordList($studentId, $num);

    /**
     * 课后记录页面
     * @param  $student_id  int
     * @return int
     */
    public function getClassRecordPage($studentId);

    /**
     * 课时记录列表
     * @param  $student_id  int
     * @param  $num         int
     * @return int
     */
    public function getClassHistoryList($studentId, $num);

    /**
     * 课时记录页面数量
     * @param  $student_id  int
     * @return int
     */
    public function getClassHistoryPage($studentId);

    /**
     * 获取错误课表的数量
     * @param  $start
     * @param  $end
     * @param  $teacherinfo
     * @param  $studentinfo
     * @return int
     */
    public function getCancelClassCount($start, $end, $teacherinfo, $studentinfo, $cancel = 4);

    /**
     * 获取错误课表的列表
     * @param  $start
     * @param  $end
     * @param  $teacherinfo
     * @param  $studentinfo
     * @param  $num
     * @return array
     */
    public function getCancelClassList($start, $end, $teacherinfo, $studentinfo, $num, $cancel = 4);

    /**
     * 获取无老师列表条数
     * @param  $day
     * @param  $name
     * @return array
     */
    public function getNoTeacherCount($day, $name, $type);

    /**
     * 获取无老师列表list
     * @param  $day
     * @param  $name
     * @return array
     */
    public function getNoTeacherList($page, $day, $name, $type);

    /**
     * @param $class_id
     * @return mixed
     * create by wangke
     * 得到课程信息
     */
    public function getClassRoomInfoByClassId($classId);

    /**
     * @param $class_id
     * @param $ahead
     * @param $defer
     * @return mixed
     * create by wangke
     * 回访组合弹窗 排课信息 调整时间
     */
    public function doChangeClassTime($classId, $ahead, $defer);

    /**
     * @param $class_id
     * @return mixed
     * @author xl
     * 通过class_id获取相关信息
     */
    public function getClassTimeAndStudentName($classId);

    /**
     * @param $open_id
     * @return mixed
     * @author xl
     * 获取剩余套餐课时类型
     */
    public function getLeftClassType($openId);
    
    /*
     * 查询发送课单的个数
     * create by sjy
     * 2017-08-2
     */
    public function getClassTimeBySaleIdCount($saleId, $keyword, $type, $start = 0, $end = 0);
    
    /*
     * 查询发送课单list
     * create by sjy
     * 2017-08-2
     */
    public function getClassTimeBySaleId($saleId, $keyword, $type, $num, $start = 0, $end = 0);
}
