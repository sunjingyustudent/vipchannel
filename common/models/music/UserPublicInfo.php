<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/12/13
 * Time: 下午5:03
 */
namespace common\models\music;

use Yii;
use yii\db\ActiveRecord;

class UserPublicInfo extends ActiveRecord {

    public static function getDb()
    {
        return Yii::$app->db;
    }

    public static function tableName()
    {
        return 'user_public_info';
    }
}