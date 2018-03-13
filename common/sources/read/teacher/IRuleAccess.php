<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:05
 */

namespace common\sources\read\teacher;

interface IRuleAccess {

    /**
     * @param $type
     * @return mixed
     * @author xl
     * 筛选奖惩规则
     */
    public function getRewardName($type);

    /**
     * @param $reward_id
     * @return mixed
     * @author xl
     * 通过ID获取奖惩规则信息
     */
    public function getRewardById($reward_id);

    /**
     * @param $reward_id
     * @return mixed
     * @author xl
     * 通过奖惩ID获取扣款/奖励类型
     *
     */
    public function getPrefixByRewardId($reward_id);

    /**
     * @return mixed
     * @author xl
     * 获取工作类型列表
     */
    public function getWorkInfo();

   
    /**
     * @return mixed
     * created by xl
     * 获取基地首页信息
     */
    public function getPlaceInfo();
    
    /**
     * @param $place_id
     * @return mixed
     * created by xl
     * 通过ID获取基地信息
     */
    public function getPlaceById($place_id);
    
    /*
     * @return mixed
     * @author xl
     * 获取老师奖惩规则详细信息
     */
    public function getRewardInfo();
    
    /**
     * @return mixed
     * @author xl
     * 获取奖惩规则类型
     */
    public function getRuleInfo();

    /**
     * @return mixed
     * @author xl
     * 获取工作类型列表
     */
    public function getWorkTypeInfo();

    /**
     * @param $work_id
     * @return mixed
     * @author xl
     * 根据ID 获取工作类型
     */
    public function getWorkTypeById($work_id);

    /**
     * @return mixed
     * @author xl
     * 获取基地个数
     */
    public function getPlaceList();

     /* @params $request
     * @return count
     * @author 小黑
     * 获取等级规则的总和
     */
    public function countGradeRule($request);

    /**
     * @params $request
     * @return count
     * @author 小黑
     * 获取等级规则
     */
    public function getGradeRuleList();

    /*
     * @param $grade_rule_id
     * @return array
     * @authoe 小黑
     * 获取等级规则想
     */
    public function getGradeRuleInfo($grade_rule_id);

    /**
     * @param $record_id
     * @return mixed
     * @author xl
     * 查看等级修改记录
     */
    public function showGradeRuleLog($record_id);
}