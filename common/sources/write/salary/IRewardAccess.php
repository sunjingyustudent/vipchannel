<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:05
 */

namespace common\sources\write\salary;

interface IRewardAccess {

    /**
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     * @author xl
     * 更新奖惩记录表为已发布
     */
    public function updateRewardIsPublish($timeStart, $timeEnd);

    /**
     * @param $teacher_id
     * @param $salary_reward
     * @param $salary_punish
     * @param $time_created
     * @return mixed
     * @author hll
     * 导入老师奖励惩罚excel
     */
    public function importSalary($teacher_id, $salary_reward, $salary_punish, $time_created);

    /**
     * @param $teacher_id
     * @param $timeStart
     * @param $is_attendance
     * @return mixed
     * @author xl
     * 更新全勤/缺勤为已处理
     */
    public function updateAttendance($teacher_id, $timeStart, $is_attendance);

    /**
     * @param $teacher_id
     * @return mixed
     * @author xl
     * 工龄奖励-赠送一次旅游
     */
    public function addTrip($teacher_id);
    
    /**
     * @param $orderid
     * @return mixed
     * @author sjy
     * 改变处理状态及结果
     */
    public function changeOrderStatus($orderid,$money);
}