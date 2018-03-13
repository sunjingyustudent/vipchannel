<?php
/**
 * Created by phpStorm.
 * User: xl
 * Date: 2017/3/3
 * Time: 17:56
 */
namespace console\models\teacher;

use Yii;
use yii\db\ActiveRecord;

class TeacherInstrumentLog extends ActiveRecord
{
    public static function tableName()
    {
        return 'teacher_instrument_log';
    }

    public static function getInstrument($teacher_id)
    {
        $sql = "SELECT * FROM teacher_instrument WHERE teacher_id = :teacher_id";

        return Yii::$app->db->createCommand($sql)
                    ->bindValue(':teacher_id', $teacher_id)
                    ->queryAll();
    }

    public static function intoTeacherInstrumentLog($data)
    {
        return Yii::$app->db->createCommand()->batchInsert('teacher_instrument_log',
            ['teacher_id','instrument_id','grade','level', 'hour_first', 'hour_second', 'hour_third', 'salary', 'time_day', 'time_created'],
            $data)->execute();
    }

    public static function intoTeacherInstrument($data)
    {
        return Yii::$app->db->createCommand()->batchInsert('teacher_instrument',
            ['teacher_id','instrument_id','grade','level', 'hour_first', 'hour_second', 'hour_third', 'salary'],
            $data)->execute();
    }

}

