<?php

/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/13
 * Time: 上午11:20
 */

namespace common\sources\read\classes;

use Yii;
use yii\db\ActiveRecord;

interface IClassAccess
{

    /**
     * @return mixed
     * created by hujiyu
     * 体验用户
     */
    public function getExUserList($num, $keyword, $time);

    /**
     * @param $classId
     * @param $studentId
     * @return mixed
     * created by hujiyu
     * 获取学生课程网络状态
     */
    public function getStudentClassNet($classId, $studentId);

    /**
     * @param $item
     * @return mixed
     * created by hujiyu
     * 获取老师课程网络状态
     */
    public function getTeacherClassNet($classId, $teacherId);

    /**
     * @param $classId
     * @param $studentId
     * @return mixed
     * created by hujiyu
     * 获取学生课程状态
     */
    public function getStudentClassStatus($classId, $studentId);

    /**
     * @param $classId
     * @param $teacherId
     * @return mixed
     * created by hujiyu
     * 获取老师课程状态
     */
    public function getTeacherClassStatus($classId, $teacherId);

    /**
     * @param $day
     * @param $type
     * @param $teacher
     * @param $timeEnd
     * @param $timeStart
     * @return mixed
     *
     * create by wangke
     * 获取课程信息的课程列表条数
     */
    public function getClassesListCount($day, $type, $teacher, $timeStart, $timeEnd);

    /**
     * @param $firstday
     * @param $lastday
     * @return mixed
     *
     * create by wangke
     * 查询一天的累积课时
     */
    public function getCountDay($firstday, $lastday);

    /**
     * @param $lastweek
     * @param $firstweek
     * @return mixed
     *
     * create by wangke
     * 查询一周的累积课时
     */
    public function getCountWeek($lastweek, $firstweek);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $teacher
     * @param $page
     * @param $type
     * @return mixed
     *
     * create by wangke
     * 得到列表的详细数据
     */
    public function getCourseData($timeStart, $timeEnd, $teacher, $page, $type);

    /**
     * @param $id
     * @return mixed
     *
     * create by wangke
     * 通过id查询课程信息
     */
    public function queryCourseData($id);

    /**
     * @param $id
     * @return mixed
     *
     * create by wangke
     * 通过id查询乐谱图片
     */
    public function queryImageList($id);

    /**
     * @param $classid
     * @return mixed
     * create by wangke
     * 通过课程id查询课程信息
     */
    public function queryViewclassData($classid);

    /**
     * @param $img_id
     * @return mixed
     *  create by wangke
     * 通过课程主键 查询课程图片图片信息
     */
    public function queryViewclassImages($imgId);

    /**
     * @param $id
     * @return mixed
     *
     * create by wangke
     * 通过id查询课程房间信息
     */
    public function queryClassRoomById($id);

    /**
     * @param $id
     * @return mixed
     *
     * create by wangke
     * 通过userid 查询课程活动信息
     */
    public function queryClassLeftinfo($userid);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * create by wangke
     * create by wangke
     * 查询预约课程列表信息
     */
    public function queryExclassList($timeStart, $timeEnd);

    /**
     * @param $class_id
     * @return mixed
     * create by wangke
     * 更具class_id 查询课表的一条信息
     */
    public function getClassByClassid($classId);

    /**
     * @param $class_id
     * @return mixed
     * create by wangke
     * 更具history_id 查询课表的编辑历史信息
     */
    public function getClassEditHistory($historyId);

    /**
     * @param $class_id
     * @return mixed
     * create by wangke
     * 查询class_left 课表活动的编辑历史信息
     */
    public function getClassLeft($leftId);

    /**
     * @param $currentDate
     * @param $timeStart
     * @param $current
     * @param $keyword
     * @param $type
     * @return mixed
     * create by wangke
     * 查询课程监控的条数
     */
    public function monitorClassCount($currentDate, $timeStart, $timeEnd, $current, $keyword, $type);

    /**
     * @param $page
     * @param $currentDate
     * @param $timeStart
     * @param $timeEnd
     * @param $current
     * @param $keyword
     * @param $type
     * @return mixed
     * create by wangke
     *
     * 查询课程监控的列表信息
     */
    public function monitorClassList($page, $currentDate, $timeStart, $timeEnd, $current, $keyword, $type);

    /**
     * @param $item
     * @return mixed
     * create by wangke
     *
     * 根据studentid查询课程信息 课程监控
     */
    public function getClassNetBeanByStudentid($item);

    /**
     * @param $item
     * @return mixed
     * create by wangke
     *
     * 根据Teacherid 查询课程信息 课程监控
     */
    public function getClassNetBeanByTeacherid($item);

    /**
     * @param $item
     * @return mixed
     * create by wangke
     * 根据studentid查询学生上课状态 课程监控
     */
    public function getClassStudentStatus($item);

    /**
     * @param $item
     * @return mixed
     * create by wangke
     * 根据Teacherid 查询老师上课状态 课程监控
     */
    public function getClassTecStatus($item);

    /**
     * @param $class_id
     * @return mixed
     * create by wangke
     *
     * 根据class_id查询课程信息
     */
    public function queryClassContactByClassid($classId);

    /**
     * @param $classid
     * @return mixed
     * create by wangke
     *
     * 查询发送消息需要的课程信息
     */
    public function getSendClassInfo($classid);

    /**
     * @param $classid
     * @return mixed
     * create by wangke
     *
     * 查询发送消息需要的课程活动的信息
     */
    public function getSendClassLeftInfo($studentId);

    /**
     * @param $classid
     * @return mixed
     * create by wangke
     *
     * 查询发送消息需要的ClassRecord信息
     */
    public function queryRecordinfo($classid);
    /*
     * @param $class_id
     * @return mixed
     * @created by Jhu
     * 获取课单基本信息
     */

    public function getRecordInfoByClassInfo($classId);

    /**
     * @return mixed
     * @created by Jhu
     * 获取老师标签字典
     */
    public function getTeacherTagList();

    /**
     * @param $recordId
     * @return mixed
     * @created by Jhu
     * 课单录一段录音列表
     */
    public function getRecordAudioList($recordId);

    /**
     * @param $recordId
     * @return mixed
     * @created by Jhu
     * 课单截图列表
     */
    public function getRecordImageList($recordId);

    /**
     * @param $time_start
     * @param $time_end
     * @return mixed
     * @author xl
     * 获取当天已上完课程数量(启蒙，初，中，高)
     */
    public function getClassByTime($timeStart, $type, $level);

    /**
     * @param $recordId
     * @return mixed
     * @created by Jhu
     * 获取课单详细基本信息
     */
    public function getRecordInfoById($recordId);

    /**
     * @param $classId
     * @return mixed
     * @created by Jhu
     * 获取课程详细信息
     */
    public function getClassInfoById($classId);

    /**
     * @param $teacher_id
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 通过老师ID获取每月体验课次数
     */
    public function getExperienceMonthByTeacher($teacherId, $timeStart, $timeEnd);

    /**
     * @param $teacher_id
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 通过老师ID获取每月体验课买单数
     */
    public function getExToBuyMonthByTeacher($teacherId, $timeStart, $timeEnd);

    /**
     * @param $tid
     * @param $timeStart
     * @param $timeEnd
     * @param $type
     * @param $page
     * @return mixed
     * @author xl
     * 通过老师ID获取当月的课程
     */
    public function getCourseByTeacher($tid, $timeStart, $timeEnd, $type, $page);

    /**
     * @param $orderIdOld
     * @return mixed
     * @created by Jhu
     * 获取剩余课时信息
     */
    public function getClassLeftRowByOrder($orderIdOld);

    /**
     * @param $orderIdOld
     * @return mixed
     * @created by Jhu
     * 获取添加课程历史信息
     */
    public function getAddClassHistoryRowByOrderId($orderIdOld);

    /**
     * @param $student_id
     * @return mixed
     * create by wangke
     * 购买信息的classroom信息
     */
    public function getBuyClassRoomInfo($studentId);

    /**
     * @param $student_id
     * @return mixed
     * create by wangke
     * 购买信息的ClassEditHistory信息
     */
    public function getBuyClassEditHistoryInfo($studentId);

    /**
     * @param $student_id
     * @return mixed
     * create by wangke
     * 购买信息的ClassLeft信息
     */
    public function getBuyClassLeftInfo($studentId);

    /**
     * @param $day
     * @return mixed
     * create by wangke
     * 查询复购的课程名单条数
     */
    public function countPurchaseCourse($timeStart, $timeEnd);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $num
     * @return mixed
     * create by wangke
     * 查询复购的课程名单列表信息
     */
    public function getPurchaseCourseList($timeStart, $timeEnd, $num);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $class_filter
     * @return mixed
     * @author xl
     * 获取学生关联的课
     */
    public function getRelateClass($timeStart, $timeEnd, $classFilter, $studentId);

    /**
     * @param $classId
     * @return mixed
     * @created by Jhu
     * 获取课程开始结束时间
     */
    public function getClassTimeById($classId);

    /**
     * @param $leftId
     * @return mixed
     * create by wangke
     * 获取剩余课程的信息通过
     */
    public function getClassLeftByLeftId($leftId);

    /**
     * 获取错误课单信息
     * @param $class_id
     * @return array
     */
    public function getClassFailBaseInfo($classId);

    /**
     * 客单信息
     * @param   $id
     * @return  array
     */
    public function getClassRoomInfo($id);

    /**
     * 统计课程月记录数量
     * @param  $studentId
     * @param  $timeStart
     * @param  $timeEnd
     * @return int
     */
    public function getClassRoomByMounth($studentId, $timeStart, $timeEnd);

    /**
     * 查找课程的任课老师
     * @param  $id
     * @return array
     */
    public function getClassRoomByteacherId($id);

    /**
     * 查询课程数量等相关信息
     * @param  $id
     * @return array
     */
    public function getClassLeftTermId($id);

    /**
     * @param $classId
     * @return mixed
     * @created by Jhu
     * 根据课程ID获取课程基本信息
     */
    public function getRowByClassId($classId);

    /**
     * @return mixed
     * @created by Jhu
     * 获取所有体验课数量
     */
    public function getTotalExclass();

    /**
     * @param $leftId
     * @return mixed
     * @created by Jhu
     * 根据ID获取剩余课时信息
     */
    public function getClassLeftRowById($leftId);

    /**
     * @param $userId
     * @return mixed
     * @created by Jhu
     * 获取用户是否购买用户
     */
    public function getStudentIsBuy($userId);

    /**
     * @param
     * @return mixed
     * create by sjy
     * 获取课程count
     */
    public function getCourseCount($type, $timeStart, $timeEnd, $passId, $tag, $filter);

    /**
     * @param
     * @return mixed
     * create by sjy
     * 获取课程信息list
     */
    public function getCourseInfo($type, $timeStart, $timeEnd, $passId, $pageNum, $tag, $filter);

    /**
     * @param
     * @return mixed
     * create by sjy
     * 获取课程信息
     */
    public function getClassInfo($classId);

    /**
     * @param $teacher_id
     * @param $timeStart
     * @param $timeEnd
     * @param $classType
     * @param $isEx
     * @return mixed
     * @author xl
     * 获取老师月课时
     */
    public function getClassCountByTeacher($teacherId, $timeStart, $timeEnd, $classType, $isEx);

    /**
     * @param $teacher_id
     * @param $time_start
     * @param $time_end
     * @return mixed
     * @author xl
     * 获取有问题课程
     */
    public function getClassProblemByTeacher($teacherId, $timeStart, $timeEnd);

    /**
     * @param $teacher_id
     * @param $course_filter
     * @param $status_filter
     * @return mixed
     * @author xl
     * 获取老师上课记录count
     */
    public function teacherClassRecordCount($teacherId, $courseFilter, $statusFilter);

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
     * @param $teacherId
     * @param $week
     * @param $timeExecute
     * @return mixed
     * @author xl
     * 获取老师当天课程
     */
    public function getWeekClassByTeacherId($teacherId, $week, $timeExecute, $nextExecute);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取当天的课程
     */
    public function getNextDayClassTeacher($timeStart, $timeEnd);

    /**
     * @param $teacher_id
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取老师当天课程（发送课程表）
     */
    public function getClassDayByTeacherId($teacherId, $timeStart, $timeEnd);

    /**
     * @param $teacher_id
     * @param $timeDay
     * @return mixed
     * @author xl
     * 获取老师当天课程（日课表）
     */
    public function getDayClassByTeacherId($teacherId, $timeDay);

    /**
     * @param $param
     * @return mixed
     * @created by Jhu
     * 获取学生赠送课
     */
    public function countStudentGiftClass($param);

    /**
     * 获取课程图片
     * @param  $class_id
     * @return array
     */
    public function getClassImage($classId);

    /**
     * 获取最后一节课的ID
     * @param  $class_id
     * @return str
     */
    public function getClassId($classId);

    /**
     * 获取学生最后一节课的course_info
     * @param   $class_id
     * @return  str
     */
    public function getLastCourse($classId);

    /**
     * 获取课后记录列表
     * @param  $student_id
     * @param  $num
     * @return array
     */
    public function kefuGetClassRecordList($studentId, $num);

    /**
     * 获取课后记录数量
     * @param  $student_id  int
     * @param  $num         int
     * @return int
     */
    public function getClassRecordPage($studentId);

    /**
     * 获取课时记录列表
     * @param  $student_id  int
     * @param  $num         int
     * @return int
     */
    public function getClassHistoryList($studentId, $num);

    /**
     * 获取课时记录数量
     * @param  $student_id  int
     * @return int
     */
    public function getClassHistoryPage($studentId);

    /**
     * 获取自主上传的乐谱信息
     * @param   $imageId
     * @return  array
     */
    public function getClassImageInfo($imageId);

    /**
     * 判断符合条件的课程是否存在
     * @param  $class_info
     * @param  $long
     * @return str
     */
    public function getClassRoomId($classInfo, $long);

    /**
     * 获取学生id和乐器id 以及课程类型
     * @param  $class_id
     * @return array
     */
    public function getClassInfoByClassId($classId);

    /**
     * 获取所有乐器
     * @return  array
     */
    public function getAllInstrumentList();

    /**
     * 获取修改课程的已经处理的并且没有被删除的
     * @param  $uid
     * @return int
     */
    public function getClassEditHistoryCountByUid($uid);

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
     * @param $teacher_id
     * @param $time
     * @return mixed
     * @author xl
     * 获取老师当天上课的课程数
     */
    public function getHaveClassByTeacher($teacherId, $time);

    /**
     * 获取无老师列表条数
     * @param  $day
     * @param  $name
     * 2017-2-10 sjy
     * @return array
     */
    public function getNoTeacherCount($timeStart, $timeEnd, $name, $type);

    /**
     * 获取无老师列表list
     * @param  $day
     * @param  $name
     * 2017-2-10 sjy
     * @return array
     */
    public function getNoTeacherList($page, $timeStart, $timeEnd, $name, $type);

    /**
     * 获取课程的开课时间和学生家长的昵称
     * @param   $class_id  课程ID
     * @return  array
     * create by  wangkai
     */
    public function getClassTimeAndStudentName($classId);

    /**
     *  获取该课程的用户OpenId
     * @param   $class_id
     * @return  str
     * create by  wangkai
     */
    public function getMessageOpenId($classId);

    /**
     *  获取距离现在最近的课程
     * @param   $student_id
     * @return  array
     * create by  wangkai
     */
    public function getRecentCourseTime($studentId);

    /**
     * 统计本周课程月取消列表
     * @param  $studentId
     * @param  $timeStart
     * @param  $timeEnd
     * @return int
     */
    public function getCancelClassInfo($studentId, $timeStart, $timeEnd);

    /**
     * @param $class_id
     * @return mixed
     * create by wangke
     * 得到课程信息
     */
    public function getClassRoomInfoByClassId($classId);
    
    /*
     * 根据$saleId，$keyword，$type查询他的课单
     * create by sjy
     */
    public function getClassTimeBySaleIdCount($saleId, $keyword, $type, $start = 0, $end = 0, $useridHaveEx);

    /**
     * 根据sale_id 查询她的完成课程成员
     * @param  $sale_id
     * @param  $keyword
     * @return  array
     * create by  wangkai
     */
    public function getClassTimeBySaleId($saleId, $keyword, $type, $num, $start = 0, $end = 0, $useridHaveEx);

    /**
     * 判断用户是否分享并且预约体验课
     * @param   $bind_openid
     * @return  str
     * create by  wangkai
     */
    public function getWechatClassName($bindOpenid);

    /**
     * 正在上课名单页面
     * @param   $keyword    (string or int)
     * @retrun  int
     *  create by  wangkai
     */
    public function getClassCheckPage($keyword);

    /**
     * 正在上课名单
     * @param   $start    int
     * @param   $end      int
     * @param   $keyword    (string or int)
     * @retrun  array
     * create by  wangkai
     */
    public function getClassCheckList($keyword, $num);

    /**
     * 查看老师当前状态
     * @param  $type int
     * @return string
     * create by  wangkai
     */
    public function getClassQuitDicTeacherName($teacherId, $classId);

    /**
     * 查看学生当前状态
     * @param  $type int
     * @return string
     * create by  wangkai
     */
    public function getClassQuitDicUserName($userId, $classId);

    /**
     * 查询7天内有无投诉信息
     * @param  $open_id  str
     * @return string
     * create by  wangkai
     */
    public function getComplainContent($openId);

    /**
     * 获取学生修改时间
     * @param   $student_id  int
     * @return  array
     * create by  wangkai
     */
    public function getStudentFixTimeInfo($studentId);

    /**
     * 学生修改时间表中获取time_bit
     * @param   $teacher_id  int
     * @param   $week  int
     * @param   $student_id  int
     * @return  array
     * create by  wangkai
     */
    public function studentTimeExit($teacherId, $week, $studentId);

    /**
     * 获取老师的fixedTime
     * @param   $teacher_id  int
     * @param   $week  int
     * @return  array
     * create by  wangkai
     */
    public function getTeacherFixedTime($teacherId, $week);

    /**
     * 批量删除课程页面
     * @param $student_id
     * @return  array
     * create by  wangkai
     */
    public function unfinishedClass($studentId);

    /**
     * 获取用户修改课程历史记录数量
     * @param  $studentId
     * @return array
     */
    public function getClassEditHistoryCount($studentId);

    /**
     * 获取用户购买数量
     * @param  $left_id
     * @return str
     */
    public function getClassLeftAmountCount($leftId);

    /**
     * 上课过程监控的信息条数
     * @param  $kefu
     * @param  @current
     * @param  $timeStart
     * @param  $timeEnd
     * @param  $keyword
     * @param  $type
     * @return array
     * created by wangkai
     */
    public function getClassMonitorCount($kefu, $current, $timeStart, $timeEnd, $keyword, $type, $monitorCourseType);

    /**
     * 获取课程监控列表
     * @param  $kefu
     * @param  @current
     * @param  $timeStart
     * @param  $timeEnd
     * @param  $keyword
     * @param  $type
     * @param  $page
     * @return array
     * created by wangkai
     */
    public function getClassMonitorList($kefu, $current, $timeStart, $timeEnd, $keyword, $type, $page, $monitorCourseType);

    /**
     * @param $student_id
     * @param $week
     * @param $time
     * @param $class_type
     * @return mixed
     * @author xl
     * 获取同一周同时间学生固定时间已存在的老师
     */
    public function getStudentTeacherFixIsExit($studentId, $week, $classBit);

    /**
     * @param $student_id
     * @return mixed
     * @author xl
     * 获取该学生剩余套餐信息
     */
    public function getBuyRemainAmount($studentId);

    /**
     * @param $left_id
     * @return mixed
     * @author xl
     * 根据课程ID判断剩余套餐数
     */
    public function getRemainAmountByLeftId($leftId);

    /**
     * 获取购买套餐之后第一次上课时间
     * @param $student_id
     * @return  array
     * create by  wangkai
     */
    public function getFirstPayClass($studentId);

    /*
     * 获取学生微课的信息by classid
     * create by sjy
     */

    public function getStudentWechatClass($classId);

    /*
     * 获取分享记录信息
     * create by sjy
     */

    public function getShareRecord($classid, $isFree, $openid, $isBack);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取老师课时以及课时评价
     */
    public function getClassRecordList($timeStart, $timeEnd, $filter);

    /**
     * @param $student_id
     * @return mixed
     * @author xl
     * 获取购买套餐课时类型
     */
    public function getLeftClassType($studentId);

    /**
     * @param $student_uid
     * @param $time_type
     * @return mixed
     * @author xl
     * 获取剩余套餐乐器类型通过课时类型
     */
//    public function getLeftInstrumentByTimeType($student_uid, $time_type);

    /**
     * @param $student_id
     * @return mixed
     * @author xl
     * 获取购买套餐乐器类型
     */
    public function getLeftInstrument($studentUid);

    /**
     * @param $teacher_id
     * @param $student_id
     * @param $timeStart
     * @param $timeEnd
     * @param $classId
     * @return mixed
     * @author xl
     * 学生固定时间排课查询冲突课程
     */
    public function getClassFailByStudentFix($teacherId, $studentId, $timeStart, $timeEnd, $classId);

    /**
     * @param $teacher_id
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author yh
     * 学生固定时间排课查询冲突课程
     */
    public function getClassWithinIntervalById($teacherId, $timeStart, $timeEnd);

    /**
     * 获取不能安排体验课的校招老师
     * @param   $time_start
     * @return  array
     * create by  wangkai
     * create time  2017/5/2
     */
    public function getschoolTeacherNotAvil($timeStart);

    //根据乐器的id去查询名称
    public function getInstrumentById($id);
    
    /*
     * 获取没有上过体验课的同学by uid
     * create by sjy 2017-08-03
     */
    public function getUserHaveEx($uid);
    
    /*
     * 根据salesid 获取注册学生
     * create by sjy
     */
    public function getUserBySalesid($saleId, $keyword, $start, $end);
    
    /*
     * 获取关注未注册的学生
     * create by sjy
     */
    public function getUserInitBySalesid($saleId, $keyword, $start, $end);

    /**
     * 得到有课程的ids(user)
     * @author wangke
     * @DateTime 2017/10/30  19:29
     * @return: [type]  [description]
     */
    public function getNotExclassInUser($salesid);

    /**
     * 关注（或注册） 未体验学生名单条数
     * @author wangke
     * @DateTime 2017/11/3  15:50
     * @return: [type]  [description]
     */
    public function getStudentNotExperienceCount($uiids, $keyword, $start, $end);

    /**
     * 渠道下关注但没有注册的学生
     * @author wangke
     * @DateTime 2017/10/30  17:11
     * @return: [type]  [description]
     */
    public function getNotInUserButInUserInit($saleId);

    /**
     * 关注（或注册） 未体验学生名单(分页用)
     * @author wangke
     * @DateTime 2017/10/30  20:27
     * @return: [type]  [description]
     */
    public function getStudentNotExperienceList($uiids, $page, $size, $keyword, $start, $end);

    /*
     * 根据salesid获取他旗下有体验课的studentid
     * create by sjy
     */
    public function getUserHaveExByClass($saleId);
}
