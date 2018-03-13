<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:05
 */

namespace common\sources\write\teacher;

interface IRuleAccess {

    /**
     * @param $request
     * @return mixed
     * created by sjy
     * 添加基地信息
     */
    public function doAddPlace($request);
    
     /**
     * @param $request
     * @return mixed
     * created by sjy
     * 修改基地信息
     */
    public function doEditPlace($request);
    
    /**
     * @param $request
     * @return mixed
     * @author xl
     * 添加奖惩规则操作
     */
    public function doAddReward($request);
    
    /**
     * @param $request
     * @return mixed
     * @author xl
     * 编辑奖惩操作
     */
    public function doEditReward($request);

    /**
     * @return mixed
     * @author xl
     */
    public function addWorkType($name, $time_start, $time_end, $instruction);

    /**
     * @param $work_id
     * @param $name
     * @param $time_start
     * @param $time_end
     * @param $instruction
     * @return mixed
     * @author xl
     * 编辑工作类型
     */
    public function editWorkType($work_id, $name, $time_start, $time_end, $instruction);

    /**
     * @param $work_id
     * @return mixed
     * @author xl
     * 删除工作类型
     */
    public function deleteWorkType($work_id);

    /**
     * @param $teacher_id
     * @param $absence_time
     * @param $reason
     * @return mixed
     * @author xl
     * 添加缺勤记录
     */
    public function addAbsence($teacher_id, $absence_time, $reason, $type);
    /*
     * @params   $request
     * @return mixed
     * @author  小黑
     * 添加等级规则
     */
    public function  addGradeRule($request);
    /*
     * @params   $request
     * @return mixed
     * @author  小黑
     * 添加等级规则记录
     */
    public function  addGradeRuleLog($request);
    /*
    * @params   $request
    * @return mixed
    * @author  小黑
    * 修改等级规则记录
    */
    public function  editGradeRule($request);

}