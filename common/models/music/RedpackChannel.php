<?php
/**
 * Created by PhpStorm.
 */

namespace common\models\music;

use yii\db\ActiveRecord;

class RedpackChannel extends ActiveRecord
{
    public static function getDb()
    {
        return \Yii::$app->db;
    }

    public static function tableName()
    {
        return 'redpack_channel';
    }

}