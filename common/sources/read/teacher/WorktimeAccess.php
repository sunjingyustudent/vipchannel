<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:06
 */

namespace common\sources\read\teacher;

use common\models\music\TeacherInfo;
use Yii;

Class WorktimeAccess implements IWorktimeAccess {

    public function getTeacherFixedTime($teacher_id, $week)
    {
        $sql = "SELECT CONV(time_bit,2,10) as time_bit, time_execute FROM teacher_info WHERE teacher_id = :teacher_id AND week = :week";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':teacher_id'=>$teacher_id,':week'=>$week])
            ->queryOne();
    }

    public function getTeacherClassFixTime($teacher_id, $week)
    {
        $sql = "SELECT CONV(time_bit,2,10) AS time_bit FROM student_fix_time WHERE week = :week AND teacher_id = :teacher_id";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([':week'=>$week,':teacher_id'=>$teacher_id])
            ->queryColumn();
    }

    public function getFixedTimeStudentName($teacher_id, $weekDay, $num)
    {
        $sql = "SELECT u.nick FROM student_fix_time AS s"
            . " LEFT JOIN user AS u ON u.id = s.student_id"
            . " WHERE s.teacher_id = :teacher_id AND week = :week AND s.time_bit & $num = $num";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':teacher_id' => $teacher_id,
                ':week' => $weekDay
            ])->queryOne();
    }

    public function getTeacherFixedTimeAll($teacher_id)
    {
        $sql = "SELECT week, CONV(time_bit,2,10) AS time_bit FROM teacher_info WHERE teacher_id = :teacher_id ORDER BY time_execute DESC LIMIT 7";

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':teacher_id' ,$teacher_id)
            ->queryAll();
    }

    public function getTeacherFixedTimeExecuteTime($teacher_id, $time_day)
    {
        return TeacherInfo::find()
            ->select('max(time_execute)')
            ->where('teacher_id = :teacher_id',[':teacher_id'=>$teacher_id])
            ->andWhere('time_execute <= :time_day', [':time_day' => $time_day])
            ->scalar();
    }

    public function getTeacherDayTime($teacher_id, $timeDay)
    {
        $sql = "SELECT CONV(time_bit,2,10) as time_bit FROM timetable WHERE user_id = :uid AND time_day = :tday";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':uid' => $teacher_id,
                ':tday' => $timeDay
            ])->queryScalar();
    }

    public function getTeacherFixTimeAll($teacher_id, $time_day)
    {
        $sql = "SELECT `week`, CONV(time_bit,2,10) AS time_bit FROM teacher_info WHERE teacher_id = :teacher_id AND time_execute <= :time_day ORDER BY time_execute DESC LIMIT 7";

        return Yii::$app->db->createCommand($sql)
                    ->bindValues([
                        ':teacher_id' => $teacher_id,
                        ':time_day' => $time_day
                    ])->queryAll();
    }

    public function getTeacherFixTimeByWeek($teacher_id, $week, $time_day)
    {
        $sql = "SELECT CONV(time_bit,2,10) AS time_bit FROM teacher_info WHERE teacher_id = :teacher_id AND week = :week AND time_execute <= :time_day"
            . " ORDER BY time_execute DESC LIMIT 1";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':teacher_id' => $teacher_id,
                ':time_day' => $time_day,
                ':week' => $week
            ])->queryScalar();
    }

    public function getAvailableListByClass($week, $time_class)
    {
        $sql = "SELECT ti.teacher_id, CONV(ti.time_bit,2,10) AS time_bit, ti.time_execute FROM teacher_info AS ti"
            . " LEFT JOIN user_teacher AS t ON ti.teacher_id = t.id"
            . " WHERE ti.`week` = :week AND ti.time_execute <= :time_class AND t.is_disabled = 0 ORDER BY ti.teacher_id ASC, ti.time_execute DESC";

        return Yii::$app->db->createCommand($sql)
                    ->bindValues([
                        ':week' => $week,
                        ':time_class' => $time_class
                    ])->queryAll();
    }

    public function getTeacherFixTimeList($week)
    {
        $sql = "SELECT teacher_info.teacher_id, CONV(teacher_info.time_bit,2,10) AS time_bit, teacher_info.time_execute FROM teacher_info"
            . " LEFT JOIN user_teacher ON teacher_info.teacher_id = user_teacher.id"
            . " WHERE teacher_info.`week` = :week AND user_teacher.is_disabled = 0 ORDER BY teacher_info.teacher_id ASC, teacher_info.time_execute DESC";

        return Yii::$app->db->createCommand($sql)
                            ->bindValue(':week', $week)
                            ->queryAll();
    }

    public function teacherFixTimeRecord($teacher_id)
    {
        $sql = "SELECT * FROM teacher_info WHERE teacher_id = :teacher_id ORDER BY time_execute, week ";

        return Yii::$app->db->createCommand($sql)
                        ->bindValue(':teacher_id', $teacher_id)
                        ->queryAll();
    }

    public function getNextTimeExecute($teacher_id, $time_execute)
    {
        $sql = "SELECT MIN(time_execute) FROM teacher_info WHERE teacher_id = :teacher_id AND time_execute > :time_execute";

        return Yii::$app->db->createCommand($sql)
                        ->bindValues([
                            ':teacher_id' => $teacher_id,
                            ':time_execute' => $time_execute
                        ])->queryScalar();
    }
}