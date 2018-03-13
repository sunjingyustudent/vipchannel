<?php
/**
 * Created by PhpStorm.
 * User: xl
 * Date: 16/12/29
 * Time: 16:32
 */
namespace console\models\teacher;

use Yii;
use yii\db\ActiveRecord;

class SalaryChangeLog extends ActiveRecord
{
    public static function tableName()
    {
        return 'salary_change_log';
    }

    public static function getTeacherHourSalary($teacher_id)
    {
        $sql = "SELECT MAX(salary) FROM teacher_instrument_log WHERE teacher_id = :teacher_id";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':teacher_id' => $teacher_id
            ])->queryScalar();
    }

    public static function getHourFee($long, $time_class, $tid, $instrument_id)
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
//            return "SELECT class_hour_first, MAX(time_day) FROM teacher_instrument_log WHERE teacher_id = :teacher_id AND instrument_id = :instrument_id AND :time_class > time_day";
            return "SELECT class_hour_first FROM teacher_instrument_log WHERE hour_time = (SELECT max(hour_time) FROM salary_change_log where :time_class >= hour_time AND teacher_id = :tid) AND teacher_id = :tid ORDER BY time_created DESC LIMIT 1";
        }else if($long == 45)
        {
//            return "SELECT class_hour_second, MAX(time_day) FROM teacher_instrument_log WHERE teacher_id = :teacher_id AND instrument_id = :instrument_id AND :time_class > time_day";

            return "SELECT class_hour_second FROM teacher_instrument_log WHERE hour_time = (SELECT max(hour_time) FROM salary_change_log where :time_class >= hour_time AND teacher_id = :tid) AND teacher_id = :tid ORDER BY time_created DESC LIMIT 1";
        }else{

//            return "SELECT class_hour_third, MAX(time_day) FROM teacher_instrument_log WHERE teacher_id = :teacher_id AND instrument_id = :instrument_id AND :time_class > time_day";

            return "SELECT class_hour_third FROM teacher_instrument_log WHERE hour_time = (SELECT max(hour_time) FROM salary_change_log where :time_class >= hour_time AND teacher_id = :tid) AND teacher_id = :tid ORDER BY time_created DESC LIMIT 1";
        }
    }
}