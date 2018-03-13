<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:06
 */

namespace common\sources\read\salary;


use common\models\music\SalaryChangeLog;
use common\models\music\TeacherSalary;
use Yii;
Class BasepayAccess implements IBasepayAccess {

    public function getTeacherDaySalary($teacher_id, $timeDay)
    {
        $sql = "SELECT day_salary FROM teacher_salary WHERE teacher_id = :teacher_id AND time_day = :timeDay";

        return Yii::$app->db->createCommand($sql)
                ->bindValues([
                    ':teacher_id' => $teacher_id,
                    ':timeDay' => $timeDay
                ])->queryScalar();
    }

    public function getTeacherMonthSalary($teacher_id, $timeStart, $timeEnd)
    {
        $sql = "SELECT ifnull(sum(day_salary),0) as salary, teacher_id FROM teacher_salary WHERE time_day >= :timeStart AND  time_day < :timeEnd AND teacher_id = :teacher_id";

        return Yii::$app->db->createCommand($sql)
                    ->bindValues([
                        ':timeStart' => $timeStart,
                        ':timeEnd' => $timeEnd,
                        ':teacher_id' => $teacher_id
                    ])->queryOne();
    }

    public function getTeacherMonthSalaryList($teacher_id, $timeStart, $timeEnd)
    {
        return TeacherSalary::find()
                    ->select('day_salary,time_day')
                    ->where(['teacher_id' => $teacher_id])
                    ->andWhere('time_day >= :timeStart',[':timeStart' => $timeStart])
                    ->andWhere('time_day < :timeEnd', [':timeEnd' => $timeEnd])
                    ->asArray()
                    ->all();

    }

    public function getSalaryTotal($timeStart, $timeEnd)
    {
        return TeacherSalary::find()
                    ->select('sum(day_salary)')
                    ->where('time_day >= :timeStart',[':timeStart' => $timeStart])
                    ->andWhere('time_day < :timeEnd',[':timeEnd' => $timeEnd])
                    ->scalar();
    }

    public function isPublish($timeStart, $timeEnd)
    {
        return TeacherSalary::find()
                    ->select('id')
                    ->where('time_day >= :timeStart',[':timeStart' => $timeStart])
                    ->andWhere('time_day < :timeEnd',[':timeEnd' => $timeEnd])
                    ->andWhere('is_publish = 1')
                    ->one();
    }

    public function getSalaryLogByTeacherId($teacher_id)
    {
        return SalaryChangeLog::find()
            ->alias('s')
            ->select('s.*, w.name as work_name')
            ->leftJoin('teacher_work_type as w','w.id = s.work_type')
            ->where('teacher_id = :teacher_id',[':teacher_id'=>$teacher_id])
            ->asArray()
            ->all();
    }

    public function getTeacherLastHourSalary($teacher_id)
    {
        $sql = "SELECT salary_after FROM salary_change_log WHERE salary_time ="
            . " (SELECT max(salary_time) FROM salary_change_log where teacher_id = :teacher_id) AND teacher_id = :teacher_id ORDER BY time_created DESC LIMIT 1";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':teacher_id' => $teacher_id,
            ])->queryScalar();
    }

    public function getTeacherBasePay($teacher_id)
    {
        $sql = "SELECT * FROM teacher_instrument WHERE teacher_id = :teacher_id";

        return Yii::$app->db->createCommand($sql)
                    ->bindValue(':teacher_id', $teacher_id)
                    ->queryAll();
    }

    public function getTeacherInstrumentLog($teacher_id)
    {
        $sql = "SELECT t.*, i.name AS instrument_name FROM teacher_instrument_log as t LEFT JOIN instrument as i ON t.instrument_id = i.id"
            . " WHERE teacher_id = :teacher_id";

        return Yii::$app->db->createCommand($sql)
                    ->bindValue(':teacher_id', $teacher_id)
                    ->queryAll();
    }

    public function getBasicSalaryByGrade($teacher_type, $school_id, $grade, $level, $time_day)
    {
        $sql = "SELECT salary_after, class_hour_first, class_hour_second, class_hour_third FROM teacher_grade_rule_log"
            . " WHERE `level` = :level AND grade_id = :grade AND teacher_type = :teacher_type AND school_id = :school_id"
            . " AND salary_time = (SELECT max(salary_time) FROM teacher_grade_rule_log where salary_time <= :time_day AND `level` = :level AND grade_id = :grade AND teacher_type = :teacher_type AND school_id = :school_id) ORDER BY create_time DESC LIMIT 1";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':teacher_type' => $teacher_type,
                ':school_id' => $school_id,
                ':grade' => $grade,
                ':level' => $level,
                ':time_day' => $time_day
            ])->queryOne();
    }

    public function getHourFee($teacher_id, $time_day)
    {
        $sql = "SELECT salary_after FROM teacher_basic_salary WHERE teacher_id = :teacher_id AND time_day = :time_day";

        return Yii::$app->db->createCommand($sql)
                        ->bindValues([
                            ':teacher_id' => $teacher_id,
                            ':time_day' => $time_day
                            ])->queryColumn();
    }
}