<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 17/3/17
 * Time: 下午4:20
 */
namespace common\models\music;

use Yii;
use yii\db\ActiveRecord;

class WechatToken extends ActiveRecord {

    public static function getDb()
    {
        return Yii::$app->db;
    }

    public static function tableName()
    {
        return 'wechat_token';
    }
}