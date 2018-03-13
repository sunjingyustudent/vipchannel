<?php
/**
 * Created by PhpStorm.
 * User: wangkai
 * Date: 16/9/20
 * Time: 14:20
 */
namespace console\models\channel;

use Yii;
use yii\db\ActiveRecord;

class ChannelPromotionEffect extends ActiveRecord
{
    public static function tableName()
    {
        return 'channel_promotion_effect';
    }
}