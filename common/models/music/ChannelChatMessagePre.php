<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/3/3
 * Time: 上午11:36
 */
namespace common\models\music;

use Yii;
use yii\db\ActiveRecord;

class ChannelChatMessagePre extends ActiveRecord {

    public static function getDb()
    {
        return Yii::$app->db;
    }

    public static function tableName()
    {
        return 'channel_chat_message_pre';
    }
}