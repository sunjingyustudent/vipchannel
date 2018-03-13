<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:06
 */

namespace common\sources\write\salary;

use common\models\music\HistoryTrade;
use Yii;
Class RewardAccess implements IRewardAccess {

    public function updateRewardIsPublish($timeStart, $timeEnd)
    {
        $sql = "UPDATE reward_record SET is_publish = 1 WHERE time_created >= :timeStart AND time_created < :timeEnd";

        return Yii::$app->db->createCommand($sql)
                ->bindValues([
                    ':timeStart' => $timeStart,
                    ':timeEnd' => $timeEnd
                ])->execute();
    }

    public function importSalary($teacher_id, $salary_reward, $salary_punish, $time_created)
    {
        $sql = "INSERT INTO teacher_reward_punish(teacher_id,salary_reward,salary_punish,time_created) VALUES (:teacher_id,:reward,:punish,:time_created) ON DUPLICATE KEY UPDATE salary_reward = :reward, salary_punish = :punish";

        Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':teacher_id' => $teacher_id,
                ':reward' => $salary_reward,
                ':punish' => $salary_punish,
                ':time_created'=>$time_created
            ])->execute();
    }

    public function updateAttendance($teacher_id, $timeStart, $is_attendance)
    {
        $sql= "UPDATE teacher_attendance SET tag = 1 WHERE teacher_id = :teacher_id AND time = :timeStart AND is_attendance = :is_attendance";

        return Yii::$app->db->createCommand($sql)
                    ->bindValues([
                        ':teacher_id' => $teacher_id,
                        ':timeStart' => $timeStart,
                        ':is_attendance' => $is_attendance
                    ])->execute();
    }

    public function addTrip($teacher_id)
    {
        $sql = "INSERT INTO teacher_trip(teacher_id, time_created) VALUES (:teacher_id, :time_created)";

        Yii::$app->db->createCommand($sql)
                    ->bindValues([':teacher_id' => $teacher_id, ':time_created' => time()])
                    ->execute();

        return Yii::$app->db->getLastInsertID();
    }
    
    /**
     * @param $orderid
     * @return mixed
     * @author sjy
     * 改变处理状态及结果
     */
    public function changeOrderStatus($orderid,$money){
        $sql= "UPDATE user_repay SET tag = 1,money = :money WHERE id = :id ";

        return Yii::$app->db->createCommand($sql)
                    ->bindValues([
                        ':money' => $money,
                        ':id' => $orderid
                    ])->execute();
    }


}