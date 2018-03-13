<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:06
 */

namespace common\sources\write\teacher;

use  Yii;
use common\models\music\StatisticsTeacherRest;

Class RestAccess implements IRestAccess {

    public function updateRestTag($rest_id,$reward_record_id)
    {
        $sql = "UPDATE statistics_teacher_rest SET tag = :id WHERE id = :rest_id";

        return Yii::$app->db->createCommand($sql)
                ->bindValues([
                    ':id' => $reward_record_id,
                    ':rest_id' => $rest_id
                ])->execute();
    }

    public function addTeacherLeave($teacher_id, $leaveType, $timeDay, $time_start, $time_end)
    {
        if($leaveType == 1)
        {
            $sql = "INSERT INTO statistics_teacher_rest(teacher_id,time_day,time_date,tmp_leave,all_leave,pause,time_start,time_end,time_created) VALUES(:teacher_id,:time_day,:time_date,0,1,0,:time_start,:time_end,:time_created) ON DUPLICATE KEY UPDATE"
                . " tmp_leave = 0, all_leave = 1, pause = 0, time_start = :time_start, time_end = :time_end, time_updated = :time_update";

        }elseif($leaveType == 2)
        {
            $sql = "INSERT INTO statistics_teacher_rest(teacher_id,time_day,time_date,tmp_leave,all_leave,pause,time_start,time_end,time_created) VALUES(:teacher_id,:time_day,:time_date,1,0,0,:time_start,:time_end,:time_created) ON DUPLICATE KEY UPDATE"
                . " tmp_leave = 1, all_leave = 0, pause = 0, time_start = :time_start, time_end = :time_end, time_updated = :time_update";

        }else {
            $sql = "INSERT INTO statistics_teacher_rest(teacher_id,time_day,time_date,tmp_leave,all_leave,pause,time_start,time_end,time_created) VALUES(:teacher_id,:time_day,:time_date,0,0,1,:time_start,:time_end,:time_created) ON DUPLICATE KEY UPDATE"
                . " tmp_leave = 0, all_leave = 0, pause = 1, time_start = :time_start, time_end = :time_end, time_updated = :time_update";
        }

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':teacher_id' => $teacher_id, ':time_day' => $timeDay,':time_date'=>date('Y-m-d',$timeDay),':time_start'=>$time_start,':time_end'=>$time_end,':time_created'=>time(),'time_update'=>time()])
            ->execute();
    }

    public function deleteLeave($teacher_id, $timeDay)
    {
        $sql = "UPDATE statistics_teacher_rest SET tmp_leave = 0, all_leave = 0, pause = 0, time_updated = :time_updated WHERE teacher_id=:teacher_id AND time_day =:time_day";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':teacher_id' => $teacher_id, ':time_day' => $timeDay, ':time_updated' => time()])
            ->execute();
    }
}