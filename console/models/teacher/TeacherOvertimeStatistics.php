<?php
/**
 * Created by PhpStorm.
 * User: xl
 * Date: 16/12/30
 * Time: 15:51
 */
namespace console\models\teacher;

use Yii;
use yii\db\ActiveRecord;

class TeacherOvertimeStatistics extends ActiveRecord
{
    public static function tableName()
    {
        return 'teacher_overtime_statistics';
    }

    public static function intoOvertime($data)
    {
        return Yii::$app->db->createCommand()->batchInsert('teacher_overtime_statistics',
            ['teacher_id','over_time','type','time_day'],
            $data)->execute();
    }
}