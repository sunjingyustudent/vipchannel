<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:05
 */

namespace common\sources\read\salary;

interface IRewardAccess {

    /**
     * @param $teacher_id
     * @param $month_time
     * @param $type
     * @return mixed
     * @author xl
     * 查询奖惩记录表是否存在记录
     */
    public function rewardRecordIsExit($teacher_id, $month_time, $type);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $filter
     * @param $type
     * @return mixed
     * @author xl
     * 获取老师月爽约count
     */
    public function getTeacherCancelCount($timeStart, $timeEnd, $filter, $type);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取老师月爽约列表
     */
    public function getTeacherCancelList($timeStart, $timeEnd, $filter, $type, $page_num);

    /**
     * @param $teacher_id
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取老师一个月的奖励
     */
    public function getTeacherRewardTotal($teacher_id, $timeStart, $timeEnd);

    /**
     * @param $teacher_id
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取老师临时的奖励奖励
     */
    public function getTeacherRewardPunishment($teacher_id, $timeStart, $timeEnd);

    /**
     * @param $teacher_id
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取老师一个月奖励详情
     */
    public function getTeacherRewardList($teacher_id, $timeStart, $timeEnd);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取月奖励总计
     */
    public function getRewardTotal($timeStart, $timeEnd);

    /**
     * @param $filter
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取月体验成单count
     */
    public function getExToBuyCount($filter, $timeStart, $timeEnd, $type);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取月体验成单列表
     */
    public function getExToBuyList($filter, $timeStart, $timeEnd, $type, $page_num);

    /**
     * @param $filter
     * @param $timeStart
     * @param $timeEnd
     * @param $type
     * @return mixed
     * @author hll
     * 导出月体验成单列表为excel
     */
    public function exportExToBuyList($filter, $timeStart, $timeEnd, $type);

    /**
     * @param $filter
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取月加班(已处理)count
     */
    public function getOvertimeDealCount($filter, $timeStart, $timeEnd);

    /**
     * @param $filter
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取月加班(未处理)count
     */
    public function getOvertimeNoDealCount($filter, $timeStart, $timeEnd);

    /**
     * @param $filter
     * @param $timeStart
     * @param $timeEnd
     * @param $type
     * @param $page_num
     * @return mixed
     * @author xl
     * 获取月加班(已处理)LIST
     */
    public function getOvertimeDealList($filter, $timeStart, $timeEnd, $page_num);

    /**
     * @param $filter
     * @param $timeStart
     * @param $timeEnd
     * @param $type
     * @param $page_num
     * @return mixed
     * @author xl
     * 获取月加班(未处理)LIST
     */
    public function getOvertimeNoDelList($filter, $timeStart, $timeEnd, $page_num);

    /**
     * @param $time_start
     * @param $time_end
     * @param $filter
     * @param $type
     * @return mixed
     * @author xl
     * 获取节假日上课老师数
     */
    public function getFestivalCount($time_start, $time_end, $filter, $type);

    /**
     * @param $time_start
     * @param $time_end
     * @param $filter
     * @param $type
     * @param $page_num
     * @return mixed
     * @author xl
     * 获取节假日上课老师列表
     */
    public function getFestivalList($time_start, $time_end, $filter, $type, $page_num);
    
  

    /**
     * @param $filter
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取额外加班(已处理)count
     */
    public function getOtherOvertimeDealCount($filter, $timeStart, $timeEnd);

    /**
     * @param $filter
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取额外加班(未处理)count
     */
    public function getOtherOvertimeNoDealCount($filter, $timeStart, $timeEnd);

    /**
     * @param $filter
     * @param $timeStart
     * @param $timeEnd
     * @param $type
     * @param $page_num
     * @return mixed
     * @author xl
     * 获取额外加班(已处理)LIST
     */
    public function getOtherOvertimeDealList($filter, $timeStart, $timeEnd, $page_num);

    /**
     * @param $filter
     * @param $timeStart
     * @param $timeEnd
     * @param $type
     * @param $page_num
     * @return mixed
     * @author xl
     * 获取额外加班(未处理)LIST
     */
    public function getOtherOvertimeNoDelList($filter, $timeStart, $timeEnd, $page_num);

    /**
     * @param $time
     * @param $filter
     * @param $type
     * @return mixed
     * @author sjy
     * 获取全勤奖列表
     */
    public function getAttendanceTeacherList($timeStart, $timeEnd, $filter, $type, $page_num);
    
    /**
     * @param $time
     * @param $filter
     * @param $type
     * @return mixed
     * @author sjy
     * 获取全勤奖个数
     */
    public function getAttendanceTeacherCount($timeStart, $timeEnd, $filter, $type);
    
    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $filter
     * @param $type
     * @return mixed
     * @author sjy
     * 获取好评奖个数
     */
    public function getGoodEvaluationCount($timeStart, $timeEnd, $filter, $type);
    
    /**
     * @param $time
     * @param $filter
     * @param $page_num
     * @param $type
     * @return mixed
     * @author sjy
     * 处理好评奖励list
     */
    public function getGoodEvaluationList($timeStart, $timeEnd, $filter, $type, $page_num);

    /**
     * @param $type
     * @return mixed
     * @author xl
     * 获取工龄奖励COUNT
     */
    public function getTripCount($type);

    /**
     * @param $type
     * @param $page_num
     * @return mixed
     * @author xl
     * 获取工龄奖励LIST
     */
    public function getTripList($type, $page_num);
    
    /**
     * @param 
     * @return mixed
     * @author sjy
     * 获取获得复购奖励的个数
     */
    public function getRepurchaseCount($time, $filter, $type);
    
     /**
     * @param 
     * @return mixed
     * @author sjy
     * 获取获得复购奖励list
     */
    public function actionRepurchaseList($time,$filter,$page_num,$type);
      
     /**
     * @param 
     * @return mixed
     * @author sjy
     * 获取给当前学生上过课的所有老师
     */
    public function teacherList($uid,$time_pay);


}