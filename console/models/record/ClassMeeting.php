<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2017/3/31
 * Time: 16:14
 */
namespace console\models\record ;
use yii\db\ActiveRecord;

class ClassMeeting extends ActiveRecord
{

    public static function tableName()
    {
        return 'class_meeting';
    }

}