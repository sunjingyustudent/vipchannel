<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:06
 */

namespace common\sources\write\teacher;
use Yii;

Class WorktimeAccess implements IWorktimeAccess {

    public function addTeacherFixedTime($teacherId, $week, $timeBit, $timeExecute)
    {
        $sql = "INSERT INTO teacher_info(teacher_id, week, time_bit, time_execute, time_created) VALUES(:teacher_id, :week,".$timeBit.", :time_execute, :time_created)"
            . " ON DUPLICATE KEY UPDATE time_bit = ".$timeBit.", time_updated = :time_update";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':teacher_id' => $teacherId,
                ':week' => $week,
                ':time_execute' => $timeExecute,
                ':time_created' => time(),
                ':time_update' => time()
            ])->execute();
    }

    public function addTeacherDayTime($teacher_id, $timeDay, $timeBit)
    {
        $sql = "INSERT INTO timetable(user_id, time_day, time_bit, time_created) VALUES(:teacher_id, :timeDay,".$timeBit.", :time_created)"
            . " ON DUPLICATE KEY UPDATE time_bit = ".$timeBit.", time_updated = :time_update";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':teacher_id' => $teacher_id,
                ':timeDay' => $timeDay,
                ':time_created' => time(),
                ':time_update' => time()
            ])->execute();
    }

    public function addTeacherFixedTimeLog($teacher_id, $week, $timeBit, $timeExecute)
    {
        $sql= "INSERT INTO music_log.teacher_info_log(teacher_id, week, time_bit, time_execute, time_created)"
            . " VALUES(:teacher_id, :week, :time_bit, :time_execute, :time_created)";

        return Yii::$app->db->createCommand($sql)
                        ->bindValues([
                            ':teacher_id' => $teacher_id,
                            ':week' => $week,
                            ':time_bit' => $timeBit,
                            ':time_execute' => $timeExecute,
                            ':time_created' => time()
                        ])->execute();
    }

    public function addNewTeacherFixedTime($teacherId, $timeExecute)
    {
        $sql = "INSERT INTO teacher_info(teacher_id, week, time_bit, time_execute, time_created)"
            . " VALUES"
            . "(:teacher_id, 1, 562949953421311, :time_execute, :time_created),"
            . "(:teacher_id, 2, 562949953421311, :time_execute, :time_created),"
            . "(:teacher_id, 3, 562949953421311, :time_execute, :time_created),"
            . "(:teacher_id, 4, 562949953421311, :time_execute, :time_created),"
            . "(:teacher_id, 5, 562949953421311, :time_execute, :time_created),"
            . "(:teacher_id, 6, 562949953421311, :time_execute, :time_created),"
            . "(:teacher_id, 7, 562949953421311, :time_execute, :time_created)";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':teacher_id' => $teacherId,
                ':time_execute' => $timeExecute,
                ':time_created' => time()
            ])->execute();
    }

    public function addNewTeacherFixedTimeLog($teacher_id,$timeExecute)
    {
        $sql= "INSERT INTO music_log.teacher_info_log(teacher_id, week, time_bit, time_execute, time_created)"
            . " VALUES"
            . "(:teacher_id, 1, 562949953421311, :time_execute, :time_created),"
            . "(:teacher_id, 2, 562949953421311, :time_execute, :time_created),"
            . "(:teacher_id, 3, 562949953421311, :time_execute, :time_created),"
            . "(:teacher_id, 4, 562949953421311, :time_execute, :time_created),"
            . "(:teacher_id, 5, 562949953421311, :time_execute, :time_created),"
            . "(:teacher_id, 6, 562949953421311, :time_execute, :time_created),"
            . "(:teacher_id, 7, 562949953421311, :time_execute, :time_created)";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':teacher_id' => $teacher_id,
                ':time_execute' => $timeExecute,
                ':time_created' => time()
            ])->execute();
    }

}