<?php
/**
 * Created by phpStorm.
 * User: xl
 * Date: 2017/2/21
 * Time: 15:07
 */
namespace console\models\teacher;

use Yii;
use yii\db\ActiveRecord;

class TeacherUseRate extends ActiveRecord
{
    public static function tableName()
    {
        return 'teacher_use_rate';
    }

    public static function intoDayRate($data)
    {
        return Yii::$app->db->createCommand()->batchInsert('teacher_use_rate',
            ['teacher_id','time_day','rate','place_id'],
            $data)->execute();
    }
}