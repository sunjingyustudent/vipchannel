<?php
/**
 * Created by PhpStorm.
 * User: xl
 * Date: 16/12/30
 * Time: 16:09
 */
namespace console\models\teacher;

use Yii;
use yii\db\ActiveRecord;

class StatisticsTeacherRest extends ActiveRecord
{
    public static function tableName()
    {
        return 'statistics_teacher_rest';
    }
}