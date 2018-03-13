<?php
/**
 * Created by PhpStorm.
 * User: xl
 * Date: 16/12/30
 * Time: 11:39
 */
namespace console\models\teacher;

use Yii;
use yii\db\ActiveRecord;

class TeacherClassCancelStatistics extends ActiveRecord
{
    public static function tableName()
    {
        return 'teacher_class_cancel_statistics';
    }

    public static function intoClassCancel($data)
    {
        return Yii::$app->db->createCommand()->batchInsert('teacher_class_cancel_statistics',
            ['teacher_id','cancel_mount','time_day'],
            $data)->execute();
    }
}