<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午3:17
 */

namespace common\logics\teacher;

interface IReport {

    /**
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取老师运营统计count
     */
    public function getTeacherStatisticsCount($timeStart, $timeEnd);
    /**
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取老师运营统计
     */
    public function getTeacherStatistics($timeStart, $timeEnd, $page_num);

    /**
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 获取老师月课时统计
     */
    public function getTeacherCourseStatistics($teacher_id, $year);

    /**
     * @param $teacher_id
     * @param $time
     * @return mixed
     * @author xl
     * 获取老师周课时统计
     */
    public function getCourseStatisticsWeek($teacher_id, $time);

    /**
     * @param $time
     * @param $orderType
     * @return mixed
     * @author xl
     * 获取老师请假列表
     */
    public function getTeacherLeaveList($timeStart, $timeEnd, $filter, $page_num);
    
    /**
     * @param $time
     * @return mixed
     * @author xl
     * 运营监控列表
     */
    public function monitorList($day, $hour);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取基地利用率列表
     */
    public function getUseRateList($timeStart, $timeEnd);

    /**
     * @param $id
     * @return mixed
     * @author xl
     * 查看时间段利用率
     */
    public function getRateDetail($id);

    /**
     * @return mixed
     * @author xl
     * 获取好评数据count
     */
    public function getGoodAnalysisCount($filter);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $filter
     * @return mixed
     * @author xl
     * 获取好评数据list
     */
    public function getGoodAnalysisList($timeStart, $timeEnd, $filter, $page_num);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $filter
     * @param $page_num
     * @return mixed
     * @author xl
     * 导出好评数据分析
     */
    public function exportGoodAnalysis($timeStart, $timeEnd, $filter, $page_num);
}