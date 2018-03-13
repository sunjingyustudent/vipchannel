<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午3:17
 */

namespace common\logics\teacher;

interface IRule {

    /**
     * @param $type
     * @return mixed
     * @author xl
     * 获取奖惩规则下拉列表
     */
    public function getRewardName($type);

    /**
     * @param $reward_id
     * @return mixed
     * @author xl
     * 通过ID获取奖惩信息
     */
    public function getRewardInfoById($reward_id);

    /**
     * @param $reward_id
     * @return mixed
     * @author xl
     * 通过ID获取奖励或者惩罚
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
     * 显示基地首页
     */
    public function getPlaceInfo();
    
    /**
     * @param $request
     * @return mixed
     * created by xl
     * 添加基地操作
     */
    public function doAddPlace($request);
    
    /**
     * @param $place_id
     * @return mixed
     * created by xl
     * 通过ID获取基地信息
     */
    public function getPlaceById($place_id);
    
    /**
     * @param $request
     * @return mixed
     * created by xl
     * 编辑基地信息操作
     */
    public function doEditPlace($request);
    
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
     * @param $request
     * @return mixed
     * @author xl
     * 编辑奖惩规则操作
     */
    public function doEditReward($request);

    /**
     * @return mixed
     * @author xl
     * 获取工作类型列表
     */
    public function getWorkTypeInfo();

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 添加工作类型
     */
    public function doAddWorkType($request);

    /**
     * @param $work_id
     * @return mixed
     * @author xl
     * 根据ID获取工作类型信息
     */
    public function getWorkTypeById($work_id);

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 编辑工作类型
     */
    public function doEditWorkType($request);

    /**
     * @param $work_id
     * @return mixed
     * @author xl
     * 删除工作类型
     */
    public function deleteWorkType($work_id);

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 添加旷工记录
     */
    public function addAbsence($request);

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 添加培训缺席记录
     */
    public function addAbsenceTrain($request);

    /**
     * @return mixed
     * @author xl
     * 获取基地个数
     */
    public function getPlaceCount();

     /* @param $request
     * @return mixed
     * @author 小黑
     * 添加等级规则
     */
    public function addGradeRule($request);

    /**
     * @return list
     * @author 小黑
     * 添加等级规则
     */
    public function getGradeRuleList() ;

    /*
     * @param $grade_rule_id
     * @return  array
     * @author  小黑
     * 获取规则详情
     */
    public function getGradeRuleInfo($grade_rule_id);

    /*
     * @params $request
     * @return array
     * author 小黑
     * 修改等级规则
     */
    public function editGradeRule($request);

    /**
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 查看等级修改记录
     */
    public function showGradeRuleLog($record_id);
}