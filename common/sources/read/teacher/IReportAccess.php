<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:05
 */

namespace common\sources\read\teacher;

interface IReportAccess {

    /**
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取老师运营统计count
     */
    public function getHomeStatisticsCount($timeStart, $timeEnd);
    /**
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取老师运营统计
     */
    public function getHomeStatistics($timeStart, $timeEnd, $page_num);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $orderType
     * @return mixed
     * @author xl
     * 获取老师请假列表
     */
    public function getTeacherLeaveList($timeStart, $timeEnd, $filter, $orderType);

    /**
     * @param $time_day
     * @param $place_id
     * @return mixed
     * @author xl
     * 获取基地一天的整体利用率
     */
    public function getPlaceDayRateTotal($time_day, $place_id);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $place_id
     * @return mixed
     * @author xl
     * 获取基地利用率大于90%的时间段
     */
    public function getHourRateByPlaceId($timeStart, $timeEnd, $place_id);

    /**
     * @param $id
     * @return mixed
     * @author xl
     * 通过ID获取时间段利用率
     */
    public function getRateDetail($id);

}