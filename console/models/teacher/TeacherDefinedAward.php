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

class TeacherDefinedAward extends ActiveRecord
{
    public static function tableName()
    {
        return 'teacher_defined_award';
    }

}