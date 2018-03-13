<?php
/**
 * Created by phpStorm.
 * User: xl
 * Date: 2017/2/4
 * Time: 11:06
 */
namespace console\models\teacher;

use Yii;
use yii\db\ActiveRecord;

class TeacherAbsence extends ActiveRecord
{
    public static function tableName()
    {
        return 'teacher_absence';
    }
}