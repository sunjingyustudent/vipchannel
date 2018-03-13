<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午4:06
 */

namespace common\sources\read\salary;

use console\models\teacher\TeacherClassMoney;
use Yii;
Class WorkhourAccess implements IWorkhourAccess {

    public function getHourFee($long, $time_class, $tid)
    {
        $sql = self::getTimeType($long);


        return Yii::$app->db->createCommand($sql)
            ->bindValues([':time_class' => $time_class, ':tid' => $tid])
            ->queryScalar();
    }

    private static function getTimeType($long)
    {
        if($long == 25)
        {
            return "SELECT class_hour_first FROM salary_change_log WHERE hour_time = (SELECT max(hour_time) FROM salary_change_log where :time_class >= hour_time AND teacher_id = :tid) AND teacher_id = :tid ORDER BY time_created DESC LIMIT 1";
        }else if($long == 45)
        {
            return "SELECT class_hour_second FROM salary_change_log WHERE hour_time = (SELECT max(hour_time) FROM salary_change_log where :time_class >= hour_time AND teacher_id = :tid) AND teacher_id = :tid ORDER BY time_created DESC LIMIT 1";
        }else{
            return "SELECT class_hour_third FROM salary_change_log WHERE hour_time = (SELECT max(hour_time) FROM salary_change_log where :time_class >= hour_time AND teacher_id = :tid) AND teacher_id = :tid ORDER BY time_created DESC LIMIT 1";
        }
    }

    public function getClassCommission($teacher_id, $timeStart, $timeEnd)
    {
        $sql = "SELECT ifnull(sum(class_money),0) as class_commission, teacher_id FROM teacher_class_money"
            . " WHERE teacher_id = :teacher_id AND time_class >= :timeStart AND time_class < :timeEnd";

        return Yii::$app->db->createCommand($sql)
                    ->bindValues([
                        ':teacher_id' => $teacher_id,
                        ':timeStart' => $timeStart,
                        ':timeEnd' => $timeEnd
                    ])->queryOne();
    }

    public function getClassCommissionList($teacher_id, $timeStart, $timeEnd)
    {
        return TeacherClassMoney::find()
            ->alias('t')
            ->select('t.class_money, c.time_class, c.time_end, c.is_ex_class')
            ->leftJoin('class_room as c','c.id = t.class_id')
            ->where(['t.teacher_id' => $teacher_id])
            ->andWhere('t.time_class >= :timeStart',[':timeStart' => $timeStart])
            ->andWhere('t.time_class < :timeEnd', [':timeEnd' => $timeEnd])
            ->asArray()
            ->all();
    }

    public function getClassCommissionTotal($timeStart, $timeEnd)
    {
        return TeacherClassMoney::find()
                    ->select('sum(class_money)')
                    ->where('time_class >= :timeStart',[':timeStart' => $timeStart])
                    ->andWhere('time_class < :timeEnd',[':timeEnd' => $timeEnd])
                    ->scalar();
    }

    public function getHourFeeByClassId($class_id)
    {
        $sql = "SELECT class_money FROM teacher_class_money WHERE class_id = :class_id";

        return Yii::$app->db->createCommand($sql)
                ->bindValue(':class_id',$class_id)
                ->queryScalar();
    }

    public function getClassMoney($teacher_id, $instrument_id, $time_class, $class_long)
    {
        $time_day = strtotime(date('Y-m-d', $time_class));

        $sql = self::getClassSql($class_long);

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':time_day' => $time_day,
                ':teacher_id' => $teacher_id,
                ':instrument_id' => $instrument_id
            ])
            ->queryScalar();
    }

    private static function getClassSql($class_long)
    {
        if($class_long == 25)
        {
            return "SELECT class_hour_first FROM `teacher_basic_salary` WHERE teacher_id = :teacher_id AND instrument_id = :instrument_id AND :time_day = time_day";
        }else if($class_long == 45)
        {
            return "SELECT class_hour_second FROM `teacher_basic_salary` WHERE teacher_id = :teacher_id AND instrument_id = :instrument_id AND :time_day = time_day";
        }else{
            return "SELECT class_hour_third FROM `teacher_basic_salary` WHERE teacher_id = :teacher_id AND instrument_id = :instrument_id AND :time_day = time_day";
        }
    }
}