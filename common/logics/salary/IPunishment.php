<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午3:17
 */

namespace common\logics\salary;

interface IPunishment
{
    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $filter
     * @param $type
     * @return mixed
     * @author xl
     * 惩罚-请假页面
     */
    public function rewardRestPage($timeStart, $timeEnd, $filter, $type);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $filter
     * @param $type
     * @param $page_num
     * @return mixed
     * @author xl
     * 惩罚-请假列表
     */
    public function rewardRestList($timeStart,$timeEnd,$filter,$type,$page_num);

    /**
     * @param $rest_id
     * @return mixed
     * @author xl
     * 惩罚-请假处理Modal
     */
    public function rewardRestTag($rest_id);

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 惩罚-请假处理结果
     */
    public function doAddRewardRest($request);

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 添加投诉处罚（需要处罚）
     */
    public function doAddRewardComplain($request);

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 投诉处罚（无需处罚）
     */
    public function noRewardComplain($request);
    
    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $filter
     * @param $type
     * @return mixed
     * @author sjy
     * 获取缺勤条数
     */
    public function absencecount($time,$filter,$type);

    /**
     * @param $teacher_id
     * @return mixed
     * @author sjy
     * 添加缺勤处理
     */
    public function addPunishment($request);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $filter
     * @param $type
     * @return mixed
     * @author sjy
     * 获取缺勤列表
     */
     public function absencelist($time,$filter,$type,$page_num);

    /**
     * @param $time
     * @param $filter
     * @param $type
     * @return mixed
     * @author xl
     * 获取差评count
     */
     public function badEvaluationCount($time, $filter, $type);

    /**
     * @param $time
     * @param $filter
     * @param $type
     * @param $page_num
     * @return mixed
     * @author xl
     * 获取差评列表
     */
     public function badEvaluationList($time, $filter, $type, $page_num);

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 添加差评惩罚记录
     */
     public function addRewardBadEvaluation($request);

    /**
     * @param $time
     * @param $filter
     * @param $type
     * @return mixed
     * @author xl
     * 获取旷工count
     */
     public function getAbsenteeismCount($time, $filter, $type);

    /**
     * @param $time
     * @param $filter
     * @param $type
     * @param $page_num
     * @return mixed
     * @author xl
     * 获取旷工列表
     */
     public function getAbsenteeismList($time, $filter, $type, $page_num);

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 添加旷工处罚记录
     */
     public function addRewardAbsenteeism($request);

    /**
     * @param $time
     * @param $filter
     * @param $type
     * @return mixed
     * @author xl
     * 获取培训缺席count
     */
     public function getAbsenteeismTrainCount($time, $filter, $type);

    /**
     * @param $time
     * @param $filter
     * @param $type
     * @param $page_num
     * @return mixed
     * @author xl
     * 获取培训缺席list
     */
     public function getAbsenteeismTrainList($time, $filter, $type, $page_num);

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 添加培训缺席记录
     */
     public function addRewardAbsenteeismTrain($request);
}