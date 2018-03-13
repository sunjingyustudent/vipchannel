<?php
/**
 * Created by PhpStorm.
 * User: wangkai
 * Date: 2017/4/11
 * Time: 17:50
 */
namespace common\models\music;

use Yii;
use yii\db\ActiveRecord;

class ChannelPromotionEffect extends ActiveRecord {

    public static function getDb()
    {
        return Yii::$app->db;
    }

    public static function tableName()
    {
        return 'channel_promotion_effect';
    }
}