<?php
/**
 * Created by PhpStorm.
 * User: xl
 * Date: 16/11/3
 * Time: 18:07
 */

namespace common\models;

use yii\db\ActiveRecord;

class RedactiveRecord extends ActiveRecord
{
    public static function getDb()
    {
        return \Yii::$app->db_log;
    }

    public static function tableName()
    {
        return 'redactive_record';
    }

}