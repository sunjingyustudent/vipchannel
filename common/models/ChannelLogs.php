<?php
namespace common\models;

use yii\db\ActiveRecord;

class ChannelLogs extends ActiveRecord
{
    public static function getDb()
    {
        return \Yii::$app->db_log;
    }

    public static function tableName()
    {
        return 'channel_logs';
    }

}