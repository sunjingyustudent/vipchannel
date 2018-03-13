<?php
/**
 * 
 */
namespace common\models\music;

use Yii;
use yii\db\ActiveRecord;

class ChannelTransferInfo extends ActiveRecord {

    public static function getDb()
    {
        return Yii::$app->db;
    }

    public static function tableName()
    {
        return 'channel_transfer_info';
    }
}