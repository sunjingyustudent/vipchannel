<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午3:17
 */

namespace common\logics\salary;

interface IReward {

    /**
     * @param $time
     * @param $filter
     * @param $type
     * @return mixed
     * @author xl
     * 奖励操作-月体验page
     */
    public function operationExperiencePage($time, $filter, $type);

    /**
     * @param $time
     * @param $filter
     * @param $type
     * @param $page_num
     * @return mixed
     * @author xl
     * 奖励操作-月体验list
     */
    public function operationExperienceList($time, $filter, $type, $page_num);

    /**
     * @param $time
     * @param $filter
     * @param $type
     * @return mixed
     * @author hll
     * 奖励操作-导出月体验list
     */
    public function operationExportExperienceList($time, $filter, $type);

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 奖励操作-添加月体验奖惩记录
     */
    public function doAddRewardEx($request);

    /**
     * @param $time
     * @param $name
     * @param $type
     * @return mixed
     * @author xl
     * 奖励-月爽约page
     */
    public function monthMissPage($time, $type, $filter);

    /**
     * @param $time
     * @param $name
     * @param $type
     * @param $num
     * @return mixed
     * @author xl
     * 奖励-月爽约list
     */
    public function monthMissList($time, $type, $filter, $page_num);

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 奖励操作-添加月爽约奖惩记录
     */
    public function doAddRewardCancel($request);

    /**
     * @param $month
     * @param $filter
     * @param $type
     * @return mixed
     * @author xl
     * 奖励-月加班（周课表）PAGE
     */
    public function overtimePage($month, $filter, $type);

    /**
     * @param $month
     * @param $filter
     * @param $type
     * @param $page_num
     * @return mixed
     * @author xl
     * 奖励-月加班（周课表）LIST
     */
    public function overtimeList($month, $filter, $type, $page_num);

    /**
     * @return mixed
     * create by wangke
     * 月加班 ，点击标记时 ，获得对应的规则放在下拉框中
     */
    public function rewardOvertimeTag();

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 添加周加班奖励记录
     */
    public function doAddRewardOvertime($request);

    /**
     * @param $month
     * @param $filter
     * @param $type
     * @return mixed
     * @author xl
     * 奖励-额外加班PAGE
     */
    public function otherOvertimePage($month, $filter, $type);

    /**
     * @param $month
     * @param $filter
     * @param $type
     * @param $page_num
     * @return mixed
     * @author xl
     * 奖励-额外加班LIST
     */
    public function otherOvertimeList($month, $filter, $type, $page_num);

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 添加额外加班奖励记录
     */
    public function doAddRewardOtherOvertime($request);

    /**
     * @param $time
     * @param $filter
     * @param $type
     * @return mixed
     * @author xl
     * 节假日奖励PAGE
     */
    public function festivalPage($time, $filter, $type);

    /**
     * @param $time
     * @param $filter
     * @param $type
     * @param $page_num
     * @return mixed
     * @author xl
     * 节假日奖励LIST
     */
    public function festivalList($time, $filter, $type, $page_num);

    
    /**
     * @param $time
     * @param $filter
     * @param $type
     * @return mixed
     * @author sjy
     * 获取全勤奖count
     */
    public function attendancePageCount($time, $filter, $type);
    
    /**
     * @param $time
     * @param $filter
     * @param $type
     * @return mixed
     * @author sjy
     * 获取全勤奖list
     */
    public function attendancePageList($time, $filter, $page_num, $type);


    /**
     * @param $request
     * @return mixed
     * @author xl
     * 添加节假日奖励记录
     */
    public function doAddRewardFestival($request);

    /**
     * @param $time
     * @param $filter
     * @param $type
     * @return mixed
     * @author sjy
     * 处理全勤奖
     */
    public function getAttendanceDeal($request);
    

    /**
     * @param $time
     * @param $filter
     * @param $type
     * @return mixed
     * @author sjy
     * 处理好评奖励count
     */
    public function getGoodEvaluationCount($time, $filter, $type);
    
    /**
     * @param $time
     * @param $filter
     * @param $page_num
     * @param $type
     * @return mixed
     * @author sjy
     * 处理好评奖励list
     */
    public function getGoodEvaluationList($time,$filter,$page_num,$type);

    /**
     * @param $teacher_id
     * @param $teacher_buy
     * @return mixed
     * @author xl
     * 获取体验成单应用规则结果
     */
    public function getExperienceMoney($teacher_id, $teacher_buy, $reward_id);

    /**
     * @param $request
     * @return mixed
     * @author xl
     * 添加好评奖
     */
    public function doAddRewardGoodEval($request);

    /**
     * @param $type
     * @return mixed
     * @author xl
     * 获取工龄COUNT
     */
    public function getTripCount($type);

    /**
     * @param $type
     * @param $page_num
     * @return mixed
     * @author xl
     * 获取工龄LIST
     */
    public function getTripList($type, $page_num);

    /**
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 赠送一次旅游
     */
    public function addTrip($teacher_id);
    
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
    
    /**
     * @param 
     * @return mixed
     * @author sjy
     *处理复购奖励
     */
    public function repurchaseDeal($request);



}