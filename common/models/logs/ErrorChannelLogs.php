<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/2/26
 * Time: 下午7:08
 */
namespace common\models\logs;

use yii\db\ActiveRecord;

class ErrorChannelLogs extends ActiveRecord
{
    public static function getDb()
    {
        return \Yii::$app->db_log;
    }

    public static function tableName()
    {
        return 'error_channel_logs';
    }

}