<?php
/**
 * Created by PhpStorm.
 * User: sjy
 * Date: 16/11/3
 * Time: 18:07
 */

namespace common\models\music;

use yii\db\ActiveRecord;

class RedactiveRecord extends ActiveRecord
{
    public static function getDb()
    {
        return \Yii::$app->db;
    }

    public static function tableName()
    {
        return 'redactive_record';
    }

}