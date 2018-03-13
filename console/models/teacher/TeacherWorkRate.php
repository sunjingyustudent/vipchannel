<?php
/**
 * Created by phpStorm.
 * User: xl
 * Date: 2017/2/4
 * Time: 10:14
 */
namespace console\models\teacher;

use Yii;
use yii\db\ActiveRecord;

class TeacherWorkRate extends ActiveRecord
{
    public static function tableName()
    {
        return 'teacher_work_rate';
    }

    public static function intoWorkRate($data)
    {
        return Yii::$app->db->createCommand()->batchInsert('teacher_work_rate',
            ['teacher_id','work','actual_work','work_rate','time'],
            $data)->execute();
    }
}