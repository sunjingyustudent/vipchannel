<?php
/**
 * 微课拉新统计
 */
namespace common\models\music;

use Yii;
use yii\db\ActiveRecord;

class WechatClassShareStat extends ActiveRecord
{

    public static function getDb()
    {
        return Yii::$app->db;
    }

    public static function tableName()
    {
        return 'wechat_class_share_stat';
    }
}
