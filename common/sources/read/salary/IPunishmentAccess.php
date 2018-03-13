<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:05
 */

namespace common\sources\read\salary;

interface IPunishmentAccess {

    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $filter
     * @param $type
     * @return mixed
     * @author xl
     * 通过筛选条件获取请假条数
     */
    public function getRewardRestCount($timeStart, $timeEnd, $filter, $type);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $filter
     * @param $type
     * @param $page_num
     * @return mixed
     * @author xl
     * 通过筛选条件获取请假详细信息
     */
    public function getRewardRestInfo($timeStart, $timeEnd, $filter, $type, $page_num);

    /**
     * @param $teacher_id
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 查询老师一个月的惩罚
     */
    public function getTeacherPunishmentTotal($teacher_id, $timeStart, $timeEnd);

    /**
     * @param $teacher_id
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取老师一个月惩罚详情
     */
    public function getTeacherPunishmentList($teacher_id, $timeStart, $timeEnd);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取月惩罚总计
     */
    public function getPunishmentTotal($timeStart, $timeEnd);


    /**
     * @param $time
     * @param $filter
     * @param $type
     * @return mixed
     * @author sjy
     * 获取缺勤列表
     */
    public function getAbsenceList($timeStart,$timeEnd,$filter,$type,$page_num);
    
    /**
     * @param $time
     * @param $filter
     * @param $type
     * @return mixed
     * @author sjy
     * 获取缺勤COUNT
     */
    public function getAbsenceCount($timeStart, $timeEnd,$filter,$type);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $filter
     * @param $type
     * @return mixed
     * @author xl
     * 获取差评条数
     */
    public function getBadEvaluationCount($timeStart, $timeEnd, $filter, $type);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $filter
     * @param $type
     * @param $page_num
     * @return mixed
     * @author xl
     * 获取差评列表
     */
    public function getBadEvaluationList($timeStart, $timeEnd, $filter, $type, $page_num);

    /**
     * @param $time
     * @param $filter
     * @param $type
     * @return mixed
     * @author xl
     * 获取旷工条数
     */
    public function getAbsenteeismCount($timeStart, $timeEnd, $filter, $type);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $filter
     * @param $type
     * @param $page_num
     * @return mixed
     * @author xl
     * 获取旷工列表
     */
    public function getAbsenteeismList($timeStart, $timeEnd, $filter, $type, $page_num);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $filter
     * @param $type
     * @return mixed
     * @author xl
     * 获取培训缺席条数
     */
    public function getAbsenteeismTrainCount($timeStart, $timeEnd, $filter, $type);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $filter
     * @param $type
     * @param $page_num
     * @return mixed
     * @author xl
     * 获取培训缺席list
     */
    public function getAbsenteeismTrainList($timeStart, $timeEnd, $filter, $type, $page_num);
}