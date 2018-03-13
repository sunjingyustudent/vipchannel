<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/2/4
 * Time: 下午12:44
 */
namespace console\models;

use Yii;
use yii\db\ActiveRecord;

class TempCourse extends ActiveRecord
{

    public static function tableName()
    {
        return 'temp_course';
    }
}