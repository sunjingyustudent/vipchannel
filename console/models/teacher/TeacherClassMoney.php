<?php
/**
 * Created by PhpStorm.
 * User: xl
 * Date: 16/12/29
 * Time: 18:37
 */
namespace console\models\teacher;

use Yii;
use yii\db\ActiveRecord;

class TeacherClassMoney extends ActiveRecord
{
    public static function tableName()
    {
        return 'teacher_class_money';
    }

    public static function intoClassMoney($data)
    {
        return Yii::$app->db->createCommand()->batchInsert('teacher_class_money',
            ['teacher_id','class_id','class_money','time_class'],
            $data)->execute();
    }
}