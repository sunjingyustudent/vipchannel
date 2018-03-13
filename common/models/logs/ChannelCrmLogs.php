<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/2/26
 * Time: 下午7:19
 */
namespace common\models\logs;

use yii\db\ActiveRecord;

class ChannelCrmLogs extends ActiveRecord
{
    public static function getDb()
    {
        return \Yii::$app->db_log;
    }

    public static function tableName()
    {
        return 'channel_crm_logs';
    }

}