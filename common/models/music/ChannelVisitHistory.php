<?php
/**
 * Created by PhpStorm.
 * User: wangkai
 */
namespace common\models\music;

use Yii;
use yii\db\ActiveRecord;

class ChannelVisitHistory extends ActiveRecord {

    public static function getDb()
    {
        return Yii::$app->db;
    }

    public static function tableName()
    {
        return 'channel_visit_history';
    }
}