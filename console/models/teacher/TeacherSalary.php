<?php
/**
 * Created by PhpStorm.
 * User: xl
 * Date: 16/12/29
 * Time: 17:35
 */
namespace console\models\teacher;

use Yii;
use yii\db\ActiveRecord;

class TeacherSalary extends ActiveRecord
{
    public static function tableName()
    {
        return 'teacher_salary';
    }

    public static function intoSalary($data)
    {
        return Yii::$app->db->createCommand()->batchInsert('teacher_salary',
            ['teacher_id','day_salary','time_day'],
            $data)->execute();
    }
}