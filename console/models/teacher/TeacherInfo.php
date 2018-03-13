<?php
/**
 * Created by PhpStorm.
 * User: xl
 * Date: 16/12/29
 * Time: 16:15
 */
namespace console\models\teacher;

use Yii;
use yii\db\ActiveRecord;

class TeacherInfo extends ActiveRecord
{
    public static function tableName()
    {
        return 'teacher_info';
    }

    public static function getTeacherTimeBit($teacher_id, $week)
    {
        $sql = "SELECT conv(time_bit,2,10) AS time_bit FROM teacher_info WHERE teacher_id = :teacher_id AND week = :week "
            ." ORDER BY time_execute DESC";

        return Yii::$app->db->createCommand($sql)
                ->bindValues([
                    ':teacher_id' => $teacher_id,
                    ':week' => $week
                ])->queryScalar();
    }

    public static function getTeacherTimeBitAll($teacher_id)
    {
        $sql = "SELECT conv(time_bit,2,10) AS time_bit, week FROM teacher_info"
            . " WHERE teacher_id = :teacher_id";

        return Yii::$app->db->createCommand($sql)
            ->bindValues([
                ':teacher_id' => $teacher_id
            ])->queryAll();
    }

}