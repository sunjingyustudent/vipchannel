<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/15
 * Time: 上午10:32
 */
namespace common\models;

use Yii;
use yii\db\ActiveRecord;

class WechatLog extends ActiveRecord {

    public static function getDb()
    {
        return Yii::$app->db;
    }

    public static function tableName()
    {
        return 'wechat_log';
    }
}