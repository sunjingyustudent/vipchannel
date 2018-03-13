<?php
/**
 * Created by PhpStorm.
 * User: sjy
 * Date: 2017/1/3
 * Time: 下午4:05
 */

namespace common\sources\read\salary;

interface IOthersAccess {

    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $mobile
     * @return mixed
     * @sjy 
     * 获取自定义惩罚列表个数
     */
    public function definedawardcount($timeStart,$timeEnd,$mobile);
    
    /**
     * @param $timeStart
     * @param $timeEnd
     * @param $pagenum
     * @param $mobile
     * @return mixed
     * @sjy 
     * 获取自定义惩罚列表
     */
    public function  definedawardlist($timeStart,$timeEnd,$mobile,$pagenum);

    /**
     * @return mixed
     * @sjy 
     * 获取在职老师姓名，id
     */
    public function getteachername();

    /**
     * @param $teacher_id
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取老师一个月自定义奖励、扣除
     */
    public function getOthersMoney($teacher_id, $timeStart, $timeEnd);

    /**
     * @param $teacher_id
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取老师月自定义奖励详细
     */
    public function getOtherRewardList($teacher_id, $timeStart, $timeEnd);

    /**
     * @param $teacher_id
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取老师月自定义惩罚详细
     */
    public function getOtherPunishmentList($teacher_id, $timeStart, $timeEnd);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取老师月自定义奖励总计
     */
    public function getOtherRewardTotal($timeStart, $timeEnd);

    /**
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 获取老师月自定义惩罚总计
     */
    public function getOtherPunishmentTotal($timeStart, $timeEnd);


}