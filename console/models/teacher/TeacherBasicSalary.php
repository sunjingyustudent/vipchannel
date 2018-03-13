<?php
/**
 * Created by phpStorm.
 * User: xl
 * Date: 2017/3/13
 * Time: 13:42
 */
namespace console\models\teacher;

use Yii;
use yii\db\ActiveRecord;

class TeacherBasicSalary extends ActiveRecord
{
    public static function tableName()
    {
        return 'teacher_basic_salary';
    }

    public static function intoBasicSalary($data)
    {
        return Yii::$app->db->createCommand()->batchInsert('teacher_basic_salary',
            ['teacher_id','instrument_id','grade','level','salary_after','class_hour_first','class_hour_second','class_hour_third','time_day'],
            $data)->execute();
    }
}