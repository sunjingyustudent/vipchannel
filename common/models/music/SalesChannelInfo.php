<?php
/**
 * Created by PhpStorm.
 * User: wangkai
 */
namespace common\models\music;

use Yii;
use yii\db\ActiveRecord;

class SalesChannelInfo extends ActiveRecord {

    public static function getDb()
    {
        return Yii::$app->db;
    }

    public static function tableName()
    {
        return 'sales_channel_info';
    }
}